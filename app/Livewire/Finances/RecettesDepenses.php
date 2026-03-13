<?php

namespace App\Livewire\Finances;

use App\Models\CaisseOperation;
use App\Models\Depense;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class RecettesDepenses extends Component
{
    public string $groupePar = 'mois'; // jour | mois | annee
    public int $annee;
    public int $mois;

    public function mount(): void
    {
        $this->annee = now()->year;
        $this->mois  = now()->month;
    }

    public function updatedGroupePar(): void
    {
        // Reset to current period on tab change.
        $this->annee = now()->year;
        $this->mois  = now()->month;
    }

    public function revenirPeriodeCourante(): void
    {
        $this->annee = now()->year;
        $this->mois = now()->month;
    }

    /* ─── Computed helpers ─────────────────────────────────────── */

    public function getLignesProperty(): Collection
    {
        return match ($this->groupePar) {
            'jour'  => $this->lignesParJour(),
            'annee' => $this->lignesParAnnee(),
            default => $this->lignesParMois(),
        };
    }

    public function getTotalRecettesProperty(): float
    {
        return round($this->lignes->sum('recettes'), 2);
    }

    public function getTotalDepensesProperty(): float
    {
        return round($this->lignes->sum('depenses'), 2);
    }

    public function getBeneficeNetProperty(): float
    {
        return round($this->total_recettes - $this->total_depenses, 2);
    }

    public function getNombrePeriodesProperty(): int
    {
        return $this->lignes->count();
    }

    public function getOperationsProperty(): Collection
    {
        $recettes = CaisseOperation::query()
            ->forCurrentSuccursale()
            ->select(['id', 'date_operation', 'designation', 'mode_paiement', 'montant_operation', 'fk_id_commande'])
            ->when($this->groupePar === 'jour', fn ($q) => $q
                ->whereYear('date_operation', $this->annee)
                ->whereMonth('date_operation', $this->mois))
            ->when($this->groupePar === 'mois', fn ($q) => $q
                ->whereYear('date_operation', $this->annee))
            ->orderByDesc('date_operation')
            ->get()
            ->map(function (CaisseOperation $operation): array {
                return [
                    'date' => Carbon::parse($operation->date_operation),
                    'type' => 'recette',
                    'type_label' => 'إيراد',
                    'designation' => $operation->designation ?: ('تحصيل طلب #' . ($operation->fk_id_commande ?? '-')),
                    'mode_paiement' => $operation->mode_paiement ?: '-',
                    'recette' => (float) $operation->montant_operation,
                    'depense' => 0.0,
                ];
            });

        $depenses = Depense::query()
            ->forCurrentSuccursale()
            ->where('statut', 'validee')
            ->select(['id', 'date_depense', 'designation', 'mode_paiement', 'montant', 'reference'])
            ->when($this->groupePar === 'jour', fn ($q) => $q
                ->whereYear('date_depense', $this->annee)
                ->whereMonth('date_depense', $this->mois))
            ->when($this->groupePar === 'mois', fn ($q) => $q
                ->whereYear('date_depense', $this->annee))
            ->orderByDesc('date_depense')
            ->get()
            ->map(function (Depense $depense): array {
                $reference = $depense->reference ? (' - ' . $depense->reference) : '';

                return [
                    'date' => Carbon::parse($depense->date_depense),
                    'type' => 'depense',
                    'type_label' => 'مصروف',
                    'designation' => ($depense->designation ?: 'مصروف') . $reference,
                    'mode_paiement' => $depense->mode_paiement ?: '-',
                    'recette' => 0.0,
                    'depense' => (float) $depense->montant,
                ];
            });

        return $recettes
            ->merge($depenses)
            ->sortByDesc(fn (array $operation) => $operation['date'])
            ->take(200)
            ->values();
    }

    public function getPeriodeSelectionneeLabelProperty(): string
    {
        if ($this->groupePar === 'annee') {
            return 'كل السنوات';
        }

        if ($this->groupePar === 'mois') {
            return 'سنة ' . $this->annee;
        }

        return Carbon::createFromDate($this->annee, $this->mois, 1)->translatedFormat('F Y');
    }

    /* ─── Groupage par jour (mois courant sélectionné) ─────────── */

    private function lignesParJour(): Collection
    {
        $debut = Carbon::createFromDate($this->annee, $this->mois, 1)->startOfDay();
        $fin   = $debut->copy()->endOfMonth()->endOfDay();

        $recettes = CaisseOperation::query()
            ->forCurrentSuccursale()
            ->whereBetween('date_operation', [$debut, $fin])
            ->selectRaw('DATE(date_operation) as periode, SUM(montant_operation) as total')
            ->groupBy('periode')
            ->pluck('total', 'periode');

        $depenses = Depense::query()
            ->forCurrentSuccursale()
            ->where('statut', 'validee')
            ->whereBetween('date_depense', [$debut->toDateString(), $fin->toDateString()])
            ->selectRaw('date_depense as periode, SUM(montant) as total')
            ->groupBy('periode')
            ->pluck('total', 'periode');

        $jours = collect();
        $cursor = $debut->copy();
        while ($cursor->lte($fin)) {
            $key = $cursor->toDateString();
            $r   = (float) ($recettes[$key] ?? 0);
            $d   = (float) ($depenses[$key] ?? 0);
            if ($r > 0 || $d > 0) {
                $jours->push([
                    'label'    => $cursor->translatedFormat('d M Y'),
                    'recettes' => $r,
                    'depenses' => $d,
                    'net'      => round($r - $d, 2),
                ]);
            }
            $cursor->addDay();
        }

        return $jours;
    }

    /* ─── Groupage par mois (année sélectionnée) ───────────────── */

    private function lignesParMois(): Collection
    {
        $debut = Carbon::createFromDate($this->annee, 1, 1)->startOfDay();
        $fin   = Carbon::createFromDate($this->annee, 12, 31)->endOfDay();

        $recettes = CaisseOperation::query()
            ->forCurrentSuccursale()
            ->whereBetween('date_operation', [$debut, $fin])
            ->selectRaw("DATE_FORMAT(date_operation, '%Y-%m') as periode, SUM(montant_operation) as total")
            ->groupBy('periode')
            ->pluck('total', 'periode');

        $depenses = Depense::query()
            ->forCurrentSuccursale()
            ->where('statut', 'validee')
            ->whereBetween('date_depense', [$debut->toDateString(), $fin->toDateString()])
            ->selectRaw("DATE_FORMAT(date_depense, '%Y-%m') as periode, SUM(montant) as total")
            ->groupBy('periode')
            ->pluck('total', 'periode');

        $moisLabels = [
            '01' => 'يناير', '02' => 'فبراير', '03' => 'مارس',
            '04' => 'أبريل', '05' => 'مايو',   '06' => 'يونيو',
            '07' => 'يوليو', '08' => 'أغسطس',  '09' => 'سبتمبر',
            '10' => 'أكتوبر','11' => 'نوفمبر',  '12' => 'ديسمبر',
        ];

        $lignes = collect();
        for ($m = 1; $m <= 12; $m++) {
            $key = $this->annee . '-' . str_pad($m, 2, '0', STR_PAD_LEFT);
            $r   = (float) ($recettes[$key] ?? 0);
            $d   = (float) ($depenses[$key] ?? 0);
            if ($r > 0 || $d > 0) {
                $lignes->push([
                    'label'    => $moisLabels[str_pad($m, 2, '0', STR_PAD_LEFT)] . ' ' . $this->annee,
                    'recettes' => $r,
                    'depenses' => $d,
                    'net'      => round($r - $d, 2),
                ]);
            }
        }

        return $lignes;
    }

    /* ─── Groupage par année ────────────────────────────────────── */

    private function lignesParAnnee(): Collection
    {
        $recettes = CaisseOperation::query()
            ->forCurrentSuccursale()
            ->selectRaw('YEAR(date_operation) as periode, SUM(montant_operation) as total')
            ->groupBy('periode')
            ->orderBy('periode')
            ->pluck('total', 'periode');

        $depenses = Depense::query()
            ->forCurrentSuccursale()
            ->where('statut', 'validee')
            ->selectRaw('YEAR(date_depense) as periode, SUM(montant) as total')
            ->groupBy('periode')
            ->orderBy('periode')
            ->pluck('total', 'periode');

        $annees = $recettes->keys()->merge($depenses->keys())->unique()->sort();

        return $annees->map(function ($annee) use ($recettes, $depenses) {
            $r = (float) ($recettes[$annee] ?? 0);
            $d = (float) ($depenses[$annee] ?? 0);
            return [
                'label'    => (string) $annee,
                'recettes' => $r,
                'depenses' => $d,
                'net'      => round($r - $d, 2),
            ];
        })->values();
    }

    /* ─── Liste des années disponibles ─────────────────────────── */

    public function getAnneesDisponiblesProperty(): array
    {
        $minRecettes = CaisseOperation::query()->forCurrentSuccursale()->min(DB::raw('YEAR(date_operation)'));
        $minDepenses = Depense::query()->forCurrentSuccursale()->min(DB::raw('YEAR(date_depense)'));
        $candidates = array_filter([(int) $minRecettes, (int) $minDepenses], fn (int $year) => $year > 0);

        $min = $candidates !== [] ? min($candidates) : now()->year;
        $max = now()->year;

        return range($max, max($min, $max - 10));
    }

    public function render()
    {
        return view('livewire.finances.recettes-depenses')->layout('layouts.app');
    }
}
