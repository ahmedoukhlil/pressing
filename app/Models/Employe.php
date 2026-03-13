<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employe extends Model
{
    protected $fillable = [
        'nom',
        'prenom',
        'telephone',
        'fk_id_poste',
        'date_embauche',
        'salaire_brut',
        'actif',
        'piece_identite_recto',
        'piece_identite_verso',
        'notes',
    ];

    protected $casts = [
        'date_embauche' => 'date',
        'salaire_brut' => 'decimal:2',
        'actif' => 'boolean',
    ];

    public function poste(): BelongsTo
    {
        return $this->belongsTo(Poste::class, 'fk_id_poste');
    }

    public function avances(): HasMany
    {
        return $this->hasMany(AvanceSalaire::class, 'fk_id_employe');
    }

    public function scopeActif(Builder $query): Builder
    {
        return $query->where('actif', true)->orderBy('nom');
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->nom} {$this->prenom}");
    }

    public function getTotalAvancesEnCoursAttribute(): string
    {
        return (string) $this->avances()->where('statut', 'en_cours')->sum('montant');
    }

    public function getSalaireNetAttribute(): string
    {
        return (string) max(0, (float) $this->salaire_brut - (float) $this->total_avances_en_cours);
    }
}
