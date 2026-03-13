<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Consommable extends Model
{
    protected $fillable = [
        'libelle',
        'unite',
        'stock_actuel',
        'seuil_alerte',
        'actif',
    ];

    protected $casts = [
        'stock_actuel' => 'decimal:2',
        'seuil_alerte' => 'decimal:2',
        'actif' => 'boolean',
    ];

    public function mouvements(): HasMany
    {
        return $this->hasMany(StockMouvement::class, 'fk_id_consommable');
    }

    public function scopeActif(Builder $query): Builder
    {
        return $query->where('actif', true)->orderBy('libelle');
    }
}
