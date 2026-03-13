<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AvanceSalaire extends Model
{
    protected $table = 'avances_salaire';

    protected $fillable = [
        'fk_id_employe',
        'date_avance',
        'montant',
        'motif',
        'statut',
        'date_deduction',
        'salaire_net_verse',
        'fk_id_depense',
        'fk_id_user',
        'notes',
    ];

    protected $casts = [
        'date_avance' => 'date',
        'date_deduction' => 'date',
        'montant' => 'decimal:2',
        'salaire_net_verse' => 'decimal:2',
    ];

    public function employe(): BelongsTo
    {
        return $this->belongsTo(Employe::class, 'fk_id_employe');
    }

    public function depense(): BelongsTo
    {
        return $this->belongsTo(Depense::class, 'fk_id_depense');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'fk_id_user');
    }
}
