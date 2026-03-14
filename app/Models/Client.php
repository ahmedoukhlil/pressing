<?php

namespace App\Models;

use App\Support\SuccursaleContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Client extends Model
{
    protected $fillable = [
        'fk_id_succursale',
        'code_client',
        'nom',
        'prenom',
        'telephone',
        'email',
        'adresse',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $client): void {
            if (!$client->fk_id_succursale) {
                $client->fk_id_succursale = SuccursaleContext::currentIdForWrite();
            }
        });

        static::created(function (self $client): void {
            if (!$client->code_client) {
                $client->forceFill([
                    'code_client' => self::formatCodeClient((int) $client->id),
                ])->saveQuietly();
            }
        });
    }

    public function commandes(): HasMany
    {
        return $this->hasMany(Commande::class, 'fk_id_client');
    }

    public function succursale(): BelongsTo
    {
        return $this->belongsTo(Succursale::class, 'fk_id_succursale');
    }

    public function pointWallet(): HasOne
    {
        return $this->hasOne(ClientPointWallet::class, 'fk_id_client');
    }

    public function pointTransactions(): HasMany
    {
        return $this->hasMany(ClientPointTransaction::class, 'fk_id_client');
    }

    public function scopeForCurrentSuccursale(Builder $query): Builder
    {
        return SuccursaleContext::apply($query);
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->nom} {$this->prenom}");
    }

    public static function formatCodeClient(int $id): string
    {
        return (string) $id;
    }
}
