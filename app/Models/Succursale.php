<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Succursale extends Model
{
    protected $fillable = [
        'nom',
        'code',
        'actif',
    ];

    protected $casts = [
        'actif' => 'boolean',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'fk_id_succursale');
    }
}

