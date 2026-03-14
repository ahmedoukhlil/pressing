<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientPointTransaction extends Model
{
    protected $fillable = [
        'fk_id_succursale',
        'fk_id_client',
        'fk_id_commande',
        'fk_id_caisse_operation',
        'fk_id_user',
        'type',
        'points',
        'valeur_mru',
        'reference_unique',
        'note',
    ];

    protected $casts = [
        'points' => 'integer',
        'valeur_mru' => 'decimal:2',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'fk_id_client');
    }

    public function succursale(): BelongsTo
    {
        return $this->belongsTo(Succursale::class, 'fk_id_succursale');
    }

    public function commande(): BelongsTo
    {
        return $this->belongsTo(Commande::class, 'fk_id_commande');
    }

    public function caisseOperation(): BelongsTo
    {
        return $this->belongsTo(CaisseOperation::class, 'fk_id_caisse_operation');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'fk_id_user');
    }
}

