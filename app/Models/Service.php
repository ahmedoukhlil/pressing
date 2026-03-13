<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    protected $fillable = [
        'libelle',
        'libelle_ar',
        'icone',
        'image',
        'prix',
        'actif',
        'ordre',
    ];

    protected $casts = [
        'prix' => 'decimal:2',
        'actif' => 'boolean',
    ];

    public function detailsCommandes(): HasMany
    {
        return $this->hasMany(DetailCommande::class, 'fk_id_service');
    }

    public function scopeActif(Builder $query): Builder
    {
        return $query->where('actif', true)->orderBy('ordre');
    }
}
