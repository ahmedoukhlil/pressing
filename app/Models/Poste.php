<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Poste extends Model
{
    protected $fillable = ['libelle', 'actif'];

    protected $casts = ['actif' => 'boolean'];

    public function employes(): HasMany
    {
        return $this->hasMany(Employe::class, 'fk_id_poste');
    }

    public function scopeActif(Builder $query): Builder
    {
        return $query->where('actif', true)->orderBy('libelle');
    }
}
