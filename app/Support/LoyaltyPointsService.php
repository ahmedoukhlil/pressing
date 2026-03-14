<?php

namespace App\Support;

use App\Models\ClientPointTransaction;
use App\Models\ClientPointWallet;
use App\Models\Setting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class LoyaltyPointsService
{
    public const KEY_ENABLED = 'points_enabled';
    public const KEY_MRU_PER_POINT = 'points_mru_per_point';
    public const KEY_MRU_DISCOUNT_PER_POINT = 'points_mru_discount_per_point';

    public static function settings(): array
    {
        $settings = Setting::query()
            ->whereIn('key', [self::KEY_ENABLED, self::KEY_MRU_PER_POINT, self::KEY_MRU_DISCOUNT_PER_POINT])
            ->pluck('value', 'key');

        $enabledRaw = strtolower((string) ($settings[self::KEY_ENABLED] ?? '1'));
        $enabled = in_array($enabledRaw, ['1', 'true', 'yes', 'on'], true);
        $mruPerPoint = max(0.01, (float) ($settings[self::KEY_MRU_PER_POINT] ?? 10));
        $mruDiscountPerPoint = max(0.01, (float) ($settings[self::KEY_MRU_DISCOUNT_PER_POINT] ?? $mruPerPoint));

        return [
            'enabled' => $enabled,
            'mru_per_point' => $mruPerPoint,
            'mru_discount_per_point' => $mruDiscountPerPoint,
        ];
    }

    public static function pointsFromAmount(float $amount, ?array $settings = null): int
    {
        $settings = $settings ?? self::settings();
        if (!$settings['enabled']) {
            return 0;
        }

        $amount = max(0, $amount);
        $ratio = max(0.01, (float) $settings['mru_per_point']);

        return (int) floor($amount / $ratio);
    }

    public static function amountFromPoints(int $points, ?array $settings = null): float
    {
        $settings = $settings ?? self::settings();
        if (!$settings['enabled']) {
            return 0;
        }

        $points = max(0, $points);
        $ratio = max(0.01, (float) $settings['mru_discount_per_point']);

        return round($points * $ratio, 2);
    }

    public static function maxPointsForAmount(float $amount, ?array $settings = null): int
    {
        $settings = $settings ?? self::settings();
        if (!$settings['enabled']) {
            return 0;
        }

        $amount = max(0, $amount);
        $ratio = max(0.01, (float) $settings['mru_discount_per_point']);

        return (int) floor($amount / $ratio);
    }

    public static function normalizePointsInput(mixed $value): int
    {
        if (is_string($value)) {
            $value = trim($value);
        }

        return max(0, (int) $value);
    }

    public static function getOrCreateWallet(int $succursaleId, int $clientId): ClientPointWallet
    {
        if (!self::pointsTablesExist()) {
            return new ClientPointWallet([
                'fk_id_succursale' => $succursaleId,
                'fk_id_client' => $clientId,
                'solde_points' => 0,
                'total_points_gagnes' => 0,
                'total_points_utilises' => 0,
            ]);
        }

        return ClientPointWallet::query()->firstOrCreate(
            [
                'fk_id_succursale' => $succursaleId,
                'fk_id_client' => $clientId,
            ],
            [
                'solde_points' => 0,
                'total_points_gagnes' => 0,
                'total_points_utilises' => 0,
            ],
        );
    }

    public static function debitForCommande(
        int $succursaleId,
        int $clientId,
        int $commandeId,
        int $points,
        float $valeurMru,
        ?int $userId = null
    ): void {
        $points = max(0, $points);
        if ($points <= 0 || !self::pointsTablesExist()) {
            return;
        }

        $reference = "commande:{$commandeId}:utilisation";
        $exists = ClientPointTransaction::query()
            ->where('reference_unique', $reference)
            ->exists();

        if ($exists) {
            return;
        }

        $wallet = ClientPointWallet::query()
            ->where('fk_id_succursale', $succursaleId)
            ->where('fk_id_client', $clientId)
            ->lockForUpdate()
            ->first();

        if (!$wallet) {
            throw ValidationException::withMessages([
                'pointsAUtiliser' => 'رصيد النقاط غير متوفر.',
            ]);
        }

        if ($wallet->solde_points < $points) {
            throw ValidationException::withMessages([
                'pointsAUtiliser' => 'النقاط المطلوبة تتجاوز الرصيد المتاح.',
            ]);
        }

        $wallet->decrement('solde_points', $points);
        $wallet->increment('total_points_utilises', $points);

        ClientPointTransaction::create([
            'fk_id_succursale' => $succursaleId,
            'fk_id_client' => $clientId,
            'fk_id_commande' => $commandeId,
            'fk_id_user' => $userId,
            'type' => 'utilisation',
            'points' => $points,
            'valeur_mru' => round($valeurMru, 2),
            'reference_unique' => $reference,
            'note' => 'Utilisation de points sur commande',
        ]);
    }

    public static function creditFromPayment(
        int $succursaleId,
        int $clientId,
        int $commandeId,
        int $caisseOperationId,
        float $montantEncaisse,
        ?int $userId = null
    ): int {
        $points = self::pointsFromAmount($montantEncaisse);
        if ($points <= 0 || !self::pointsTablesExist()) {
            return 0;
        }

        $alreadyExists = ClientPointTransaction::query()
            ->where('fk_id_caisse_operation', $caisseOperationId)
            ->where('type', 'gain')
            ->exists();

        if ($alreadyExists) {
            return 0;
        }

        $wallet = ClientPointWallet::query()
            ->where('fk_id_succursale', $succursaleId)
            ->where('fk_id_client', $clientId)
            ->lockForUpdate()
            ->first();

        if (!$wallet) {
            $wallet = self::getOrCreateWallet($succursaleId, $clientId);
        }

        $wallet->increment('solde_points', $points);
        $wallet->increment('total_points_gagnes', $points);

        ClientPointTransaction::create([
            'fk_id_succursale' => $succursaleId,
            'fk_id_client' => $clientId,
            'fk_id_commande' => $commandeId,
            'fk_id_caisse_operation' => $caisseOperationId,
            'fk_id_user' => $userId,
            'type' => 'gain',
            'points' => $points,
            'valeur_mru' => round($montantEncaisse, 2),
            'reference_unique' => "caisse:{$caisseOperationId}:gain",
            'note' => 'Points gagnes sur paiement',
        ]);

        return $points;
    }

    private static function pointsTablesExist(): bool
    {
        return Schema::hasTable('client_point_wallets') && Schema::hasTable('client_point_transactions');
    }
}

