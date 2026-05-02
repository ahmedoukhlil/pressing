<?php

namespace App\Models;

use App\Support\SuccursaleContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pret extends Model
{
    protected $table = 'prets';

    protected $fillable = [
        'fk_id_succursale',
        'date_pret',
        'preteur',
        'montant',
        'mode_paiement',
        'montant_rembourse',
        'statut',
        'notes',
        'fk_id_user',
    ];

    protected $casts = [
        'date_pret'          => 'date',
        'montant'            => 'decimal:2',
        'montant_rembourse'  => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $pret): void {
            if (!$pret->fk_id_succursale) {
                $pret->fk_id_succursale = SuccursaleContext::currentIdForWrite();
            }
        });
    }

    public function remboursements(): HasMany
    {
        return $this->hasMany(Depense::class, 'reference', 'id')
            ->where('reference', 'like', 'PRET-%')
            ->validee();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'fk_id_user');
    }

    public function succursale(): BelongsTo
    {
        return $this->belongsTo(Succursale::class, 'fk_id_succursale');
    }

    public function getSoldeRestantAttribute(): float
    {
        return round((float) $this->montant - (float) $this->montant_rembourse, 2);
    }

    public function getPourcentageRembourseAttribute(): float
    {
        if ((float) $this->montant <= 0) {
            return 0;
        }
        return round(((float) $this->montant_rembourse / (float) $this->montant) * 100, 1);
    }

    public function scopeForCurrentSuccursale(Builder $query): Builder
    {
        return SuccursaleContext::apply($query);
    }

    public function recalculerMontantRembourse(): void
    {
        $total = Depense::query()
            ->forCurrentSuccursale()
            ->where('reference', 'PRET-' . $this->id)
            ->validee()
            ->sum('montant');

        $this->montant_rembourse = $total;
        $this->statut = (float) $total >= (float) $this->montant ? 'solde' : 'en_cours';
        $this->save();
    }
}
