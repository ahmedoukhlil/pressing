<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientPointWallet extends Model
{
    protected $fillable = [
        'fk_id_succursale',
        'fk_id_client',
        'solde_points',
        'total_points_gagnes',
        'total_points_utilises',
    ];

    protected $casts = [
        'solde_points' => 'integer',
        'total_points_gagnes' => 'integer',
        'total_points_utilises' => 'integer',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'fk_id_client');
    }

    public function succursale(): BelongsTo
    {
        return $this->belongsTo(Succursale::class, 'fk_id_succursale');
    }
}

