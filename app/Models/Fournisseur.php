<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Fournisseur extends Model
{
    protected $fillable = ['nom', 'telephone', 'nif', 'actif'];

    protected $casts = ['actif' => 'boolean'];

    public function depenses(): HasMany
    {
        return $this->hasMany(Depense::class, 'fk_id_fournisseur');
    }

    public function scopeActif(Builder $query): Builder
    {
        return $query->where('actif', true)->orderBy('nom');
    }
}
