<?php

namespace App\Models;

use App\Support\SuccursaleContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CaisseOperation extends Model
{
    protected $fillable = [
        'fk_id_succursale',
        'date_operation',
        'montant_operation',
        'designation',
        'fk_id_client',
        'entree_espece',
        'retrait_espece',
        'fk_id_commande',
        'fk_id_user',
        'mode_paiement',
    ];

    protected $casts = [
        'date_operation' => 'datetime',
        'montant_operation' => 'decimal:2',
        'entree_espece' => 'decimal:2',
        'retrait_espece' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $operation): void {
            if (!$operation->fk_id_succursale) {
                $operation->fk_id_succursale = SuccursaleContext::currentIdForWrite();
            }
        });
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'fk_id_client');
    }

    public function commande(): BelongsTo
    {
        return $this->belongsTo(Commande::class, 'fk_id_commande');
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
}
