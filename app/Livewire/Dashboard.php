<?php

namespace App\Livewire;

use App\Models\CaisseOperation;
use App\Models\Client;
use App\Models\Commande;
use App\Models\Depense;
use App\Models\ModePaiement;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class Dashboard extends Component
{
    use WithPagination;

    public string $dateRecettes = '';
    public string $filtreMode = '';

    public function mount(): void
    {
        $this->dateRecettes = now()->toDateString();
    }

    public function updatingDateRecettes(): void
    {
        $this->resetPage('recettesPage');
    }

    public function updatingFiltreMode(): void
    {
        $this->resetPage('recettesPage');
    }

    public function render()
    {
        $today = now()->toDateString();
        $now = now();
        $dateSelectionnee = $this->dateRecettes ?: $today;

        $commandesNonLivrees = Commande::query()
            ->forCurrentSuccursale()
            ->with('client')
            ->whereIn('statut', ['en_cours', 'pret']);

        $stats = [
            'clients' => Client::query()->forCurrentSuccursale()->count(),
            'commandes_total' => Commande::query()->forCurrentSuccursale()->count(),
            'commandes_du_jour' => Commande::query()->forCurrentSuccursale()->whereDate('date_depot', $today)->count(),
            'en_cours' => Commande::query()->forCurrentSuccursale()->where('statut', 'en_cours')->count(),
            'pret' => Commande::query()->forCurrentSuccursale()->where('statut', 'pret')->count(),
            'ca_jour' => (float) CaisseOperation::query()->forCurrentSuccursale()->whereDate('date_operation', $dateSelectionnee)->sum('montant_operation'),
            'montants_factures_non_percus' => (float) Commande::query()
                ->forCurrentSuccursale()
                ->where('statut', '!=', 'annule')
                ->where('reste_a_payer', '>', 0)
                ->whereDate('date_depot', $dateSelectionnee)
                ->sum('reste_a_payer'),
            'commandes_avec_reste' => Commande::query()
                ->forCurrentSuccursale()
                ->where('statut', '!=', 'annule')
                ->where('reste_a_payer', '>', 0)
                ->whereDate('date_depot', $dateSelectionnee)
                ->count(),
            'depenses_mois' => (float) Depense::query()
                ->forCurrentSuccursale()
                ->validee()
                ->whereMonth('date_depense', now()->month)
                ->whereYear('date_depense', now()->year)
                ->sum('montant'),
        ];

        // Recettes journalières détaillées
        $libelles = ModePaiement::query()->pluck('libelle', 'code');
        $modesPaiement = ModePaiement::query()->orderBy('libelle')->get();

        $recettesQuery = CaisseOperation::query()
            ->forCurrentSuccursale()
            ->whereDate('date_operation', $dateSelectionnee)
            ->when($this->filtreMode !== '', fn ($q) => $q->where('mode_paiement', $this->filtreMode))
            ->orderByDesc('date_operation');

        $totalRecettesJour = (float) (clone $recettesQuery)->sum('montant_operation');

        $recettesJour = $recettesQuery
            ->with(['client', 'commande'])
            ->paginate(15, ['*'], 'recettesPage')
            ->through(fn ($op) => [
                'heure'           => Carbon::parse($op->date_operation)->format('H:i'),
                'client_nom'      => $op->client?->full_name ?? '-',
                'client_tel'      => $op->client?->telephone ?? '',
                'numero_commande' => $op->commande?->numero_commande ?? '-',
                'montant'         => (float) $op->montant_operation,
                'mode_paiement'   => $op->mode_paiement ? ($libelles[$op->mode_paiement] ?? $op->mode_paiement) : '-',
            ]);

        $commandesProchesEcheance = (clone $commandesNonLivrees)
            ->where('date_depot', '>', $now->copy()->subDays(7))
            ->where('date_depot', '<=', $now->copy()->subDays(5))
            ->orderBy('date_depot')
            ->limit(8)
            ->get()
            ->map(function (Commande $commande) use ($now) {
                $commande->jours_depuis_depot = Carbon::parse($commande->date_depot)->diffInDays($now);
                return $commande;
            });

        $commandesHorsDelai = (clone $commandesNonLivrees)
            ->where('date_depot', '<=', $now->copy()->subDays(7))
            ->orderBy('date_depot')
            ->limit(8)
            ->get()
            ->map(function (Commande $commande) use ($now) {
                $commande->jours_depuis_depot = Carbon::parse($commande->date_depot)->diffInDays($now);
                return $commande;
            });

        return view('livewire.dashboard', compact(
            'stats', 'commandesProchesEcheance', 'commandesHorsDelai',
            'recettesJour', 'totalRecettesJour', 'dateSelectionnee', 'modesPaiement'
        ))->layout('layouts.app');
    }
}
