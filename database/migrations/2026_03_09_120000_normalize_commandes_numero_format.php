<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('commandes')
            ->select(['id', 'numero_commande'])
            ->where('numero_commande', 'like', 'CMD-%')
            ->orderBy('id')
            ->chunkById(200, function ($commandes): void {
                foreach ($commandes as $commande) {
                    $ancienNumero = (string) $commande->numero_commande;
                    $nouveauNumero = preg_replace('/^CMD-/', '', $ancienNumero);

                    if (!$nouveauNumero || $nouveauNumero === $ancienNumero) {
                        continue;
                    }

                    $existe = DB::table('commandes')
                        ->where('numero_commande', $nouveauNumero)
                        ->where('id', '!=', $commande->id)
                        ->exists();

                    if ($existe) {
                        continue;
                    }

                    DB::table('commandes')
                        ->where('id', $commande->id)
                        ->update(['numero_commande' => $nouveauNumero]);
                }
            });
    }

    public function down(): void
    {
        DB::table('commandes')
            ->select(['id', 'numero_commande'])
            ->whereRaw("numero_commande REGEXP '^[0-9]{4}-[0-9]{4}$'")
            ->orderBy('id')
            ->chunkById(200, function ($commandes): void {
                foreach ($commandes as $commande) {
                    $ancienNumero = (string) $commande->numero_commande;
                    $nouveauNumero = 'CMD-' . $ancienNumero;

                    $existe = DB::table('commandes')
                        ->where('numero_commande', $nouveauNumero)
                        ->where('id', '!=', $commande->id)
                        ->exists();

                    if ($existe) {
                        continue;
                    }

                    DB::table('commandes')
                        ->where('id', $commande->id)
                        ->update(['numero_commande' => $nouveauNumero]);
                }
            });
    }
};
