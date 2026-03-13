<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class ModePaiement extends Model
{
    protected $table = 'modes_paiement';

    protected $fillable = [
        'libelle',
        'code',
        'icone',
        'actif',
        'est_systeme',
        'ordre',
    ];

    protected $casts = [
        'actif' => 'boolean',
        'est_systeme' => 'boolean',
    ];

    public function scopeActif(Builder $query): Builder
    {
        return $query->where('actif', true)->orderBy('ordre');
    }
}
