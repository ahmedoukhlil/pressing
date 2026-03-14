<?php

namespace App\Models;

use App\Support\SuccursaleContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Depense extends Model
{
    protected $fillable = [
        'fk_id_succursale',
        'date_depense',
        'fk_id_type_depense',
        'designation',
        'montant',
        'mode_paiement',
        'fk_id_fournisseur',
        'fk_id_employe',
        'reference',
        'statut',
        'notes',
        'fk_id_user',
    ];

    protected $casts = [
        'date_depense' => 'date',
        'montant' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $depense): void {
            if (!$depense->fk_id_succursale) {
                $depense->fk_id_succursale = SuccursaleContext::currentIdForWrite();
            }
        });
    }

    public function typeDepense(): BelongsTo
    {
        return $this->belongsTo(TypeDepense::class, 'fk_id_type_depense');
    }

    public function fournisseur(): BelongsTo
    {
        return $this->belongsTo(Fournisseur::class, 'fk_id_fournisseur');
    }

    public function employe(): BelongsTo
    {
        return $this->belongsTo(Employe::class, 'fk_id_employe');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'fk_id_user');
    }

    public function succursale(): BelongsTo
    {
        return $this->belongsTo(Succursale::class, 'fk_id_succursale');
    }

    public function scopeForCurrentSuccursale(Builder $query): Builder
    {
        return SuccursaleContext::apply($query);
    }

    public function scopeValidee(Builder $query): Builder
    {
        return $query->where('statut', 'validee');
    }
}
