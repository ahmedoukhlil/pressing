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
        'quantite_rendue',
        'sous_total',
        'notes',
        'statut_ligne',
    ];

    protected $casts = [
        'prix_unitaire' => 'decimal:2',
        'quantite' => 'integer',
        'quantite_rendue' => 'integer',
        'sous_total' => 'decimal:2',
    ];

    public function getQuantiteRestanteAttribute(): int
    {
        return max(0, (int) $this->quantite - (int) $this->quantite_rendue);
    }

    public function getStatutLigneLabelAttribute(): string
    {
        if ((int) $this->quantite_rendue >= (int) $this->quantite) {
            return 'مسلّم بالكامل';
        }

        return match ($this->statut_ligne) {
            'pret' => (int) $this->quantite_rendue > 0 ? 'جاهز — تسليم جزئي' : 'جاهز للتسليم',
            'livre' => 'مسلّم بالكامل',
            default => 'قيد المعالجة',
        };
    }

    public function commande(): BelongsTo
    {
        return $this->belongsTo(Commande::class, 'fk_id_commande');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'fk_id_service');
    }
}
