<?php

namespace App\Models;

use App\Support\SuccursaleContext;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Commande extends Model
{
    protected $fillable = [
        'fk_id_succursale',
        'numero_commande',
        'annee_commande',
        'n_ordre',
        'fk_id_client',
        'date_depot',
        'date_livraison_prevue',
        'date_livraison_reelle',
        'statut',
        'montant_total',
        'montant_paye',
        'reste_a_payer',
        'remise_depot_pourcentage',
        'remise_depot_montant',
        'remise_reglement_montant',
        'total_remise',
        'mode_reglement',
        'est_paye',
        'notes',
        'fk_id_user',
    ];

    protected $casts = [
        'date_depot' => 'datetime',
        'date_livraison_prevue' => 'datetime',
        'date_livraison_reelle' => 'datetime',
        'montant_total' => 'decimal:2',
        'montant_paye' => 'decimal:2',
        'reste_a_payer' => 'decimal:2',
        'remise_depot_pourcentage' => 'decimal:2',
        'remise_depot_montant' => 'decimal:2',
        'remise_reglement_montant' => 'decimal:2',
        'total_remise' => 'decimal:2',
        'est_paye' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $commande): void {
            if (!$commande->fk_id_succursale) {
                $commande->fk_id_succursale = SuccursaleContext::currentIdForWrite();
            }
        });
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'fk_id_client');
    }

    public function details(): HasMany
    {
        return $this->hasMany(DetailCommande::class, 'fk_id_commande');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'fk_id_user');
    }

    public function caisseOperations(): HasMany
    {
        return $this->hasMany(CaisseOperation::class, 'fk_id_commande');
    }

    public function succursale(): BelongsTo
    {
        return $this->belongsTo(Succursale::class, 'fk_id_succursale');
    }

    public function pointTransactions(): HasMany
    {
        return $this->hasMany(ClientPointTransaction::class, 'fk_id_commande');
    }

    public function scopeForCurrentSuccursale(Builder $query): Builder
    {
        return SuccursaleContext::apply($query);
    }

    public static function generateNumeroCommande(?int $annee = null, ?int $succursaleId = null): array
    {
        $annee = $annee ?? Carbon::now()->year;
        $succursaleId = $succursaleId ?? SuccursaleContext::currentIdForWrite();

        return DB::transaction(function () use ($annee, $succursaleId): array {
            $last = self::query()
                ->where('fk_id_succursale', $succursaleId)
                ->where('annee_commande', $annee)
                ->lockForUpdate()
                ->orderByDesc('n_ordre')
                ->first();

            $nOrdre = $last ? ($last->n_ordre + 1) : 1;

            return [
                'numero_commande' => $annee . '-' . str_pad((string) $nOrdre, 4, '0', STR_PAD_LEFT),
                'annee_commande' => $annee,
                'n_ordre' => $nOrdre,
            ];
        });
    }

    public function getStatutLabelAttribute(): string
    {
        return match ($this->statut) {
            'en_attente' => 'قيد المعالجة',
            'en_cours' => 'قيد المعالجة',
            'pret' => 'جاهز',
            'livre' => 'مسلّم',
            default => $this->statut,
        };
    }

    public function getStatutColorAttribute(): string
    {
        return match ($this->statut) {
            'en_attente' => 'blue',
            'en_cours' => 'blue',
            'pret' => 'green',
            'livre' => 'gray',
            default => 'gray',
        };
    }

    public function estCompletementPayee(): bool
    {
        return (float) $this->montant_paye >= (float) $this->montant_total;
    }

    /**
     * Aligne commande.statut sur l’état des lignes (prêt partiel / toutes livrées).
     */
    public function synchroniserStatutAvecLignes(): void
    {
        $this->loadMissing('details');

        if ($this->details->isEmpty()) {
            return;
        }

        $toutLivre = $this->details->every(
            fn (DetailCommande $d) => (int) $d->quantite_rendue >= (int) $d->quantite
        );

        if ($toutLivre) {
            if ($this->statut !== 'livre') {
                $this->update([
                    'statut' => 'livre',
                    'date_livraison_reelle' => $this->date_livraison_reelle ?? now(),
                ]);
            }

            return;
        }

        $encoreEnTraitement = $this->details->contains(fn (DetailCommande $d) => $d->statut_ligne === 'en_cours');

        if ($encoreEnTraitement) {
            if ($this->statut !== 'en_cours') {
                $updates = ['statut' => 'en_cours'];
                if ($this->statut === 'livre') {
                    $updates['date_livraison_reelle'] = null;
                }
                $this->update($updates);
            }

            return;
        }

        if ($this->statut !== 'pret') {
            $updates = ['statut' => 'pret'];
            if ($this->statut === 'livre') {
                $updates['date_livraison_reelle'] = null;
            }
            $this->update($updates);
        }
    }

    public function getRemisePartielleEnCoursAttribute(): bool
    {
        $this->loadMissing('details');

        return $this->details->contains(
            fn (DetailCommande $d) => (int) $d->quantite_rendue > 0
                && (int) $d->quantite_rendue < (int) $d->quantite
        );
    }
}
