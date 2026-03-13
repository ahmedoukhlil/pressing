<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailCommande extends Model
{
    protected $fillable = [
        'fk_id_commande',
        'fk_id_service',
        'prix_unitaire',
        'quantite',
        'sous_total',
        'notes',
    ];

    protected $casts = [
        'prix_unitaire' => 'decimal:2',
        'quantite' => 'integer',
        'sous_total' => 'decimal:2',
    ];

    public function commande(): BelongsTo
    {
        return $this->belongsTo(Commande::class, 'fk_id_commande');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'fk_id_service');
    }
}
