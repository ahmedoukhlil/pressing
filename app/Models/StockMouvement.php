<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMouvement extends Model
{
    protected $fillable = [
        'fk_id_consommable',
        'type_mouvement',
        'quantite',
        'date_mouvement',
        'motif',
        'notes',
        'fk_id_user',
    ];

    protected $casts = [
        'quantite' => 'decimal:2',
        'date_mouvement' => 'datetime',
    ];

    public function consommable(): BelongsTo
    {
        return $this->belongsTo(Consommable::class, 'fk_id_consommable');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'fk_id_user');
    }
}
