<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TypeDepense extends Model
{
    protected $table = 'types_depenses';

    protected $fillable = ['libelle', 'icone', 'couleur', 'actif', 'ordre'];

    protected $casts = ['actif' => 'boolean'];

    public function depenses(): HasMany
    {
        return $this->hasMany(Depense::class, 'fk_id_type_depense');
    }

    public function scopeActif(Builder $query): Builder
    {
        return $query->where('actif', true)->orderBy('ordre');
    }
}
