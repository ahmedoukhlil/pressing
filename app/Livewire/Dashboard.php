<?php

namespace App\Livewire;

use App\Models\CaisseOperation;
use App\Models\Client;
use App\Models\Commande;
use App\Models\Depense;
use App\Models\ModePaiement;
use Carbon\Carbon;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $today = now()->toDateString();
        $now = now();

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
            'ca_jour' => (float) Commande::query()->forCurrentSuccursale()->whereDate('date_depot', $today)->sum('montant_paye'),
            'montants_factures_non_percus' => (float) Commande::query()
                ->forCurrentSuccursale()
                ->where('statut', '!=', 'annule')
                ->where('reste_a_payer', '>', 0)
                ->sum('reste_a_payer'),
            'commandes_avec_reste' => Commande::query()
                ->forCurrentSuccursale()
                ->where('statut', '!=', 'annule')
                ->where('reste_a_payer', '>', 0)
                ->count(),
            'depenses_mois' => (float) Depense::query()
                ->forCurrentSuccursale()
                ->validee()
                ->whereMonth('date_depense', now()->month)
                ->whereYear('date_depense', now()->year)
                ->sum('montant'),
        ];

        // Recettes du mois ventilées par mode de paiement
        $libelles = ModePaiement::query()->pluck('libelle', 'code');
        $recettesParMode = CaisseOperation::query()
            ->forCurrentSuccursale()
            ->whereMonth('date_operation', now()->month)
            ->whereYear('date_operation', now()->year)
            ->selectRaw('mode_paiement, SUM(montant_operation) as total')
            ->groupBy('mode_paiement')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [
                'code'    => $row->mode_paiement,
                'libelle' => $row->mode_paiement ? ($libelles[$row->mode_paiement] ?? $row->mode_paiement) : '-',
                'total'   => (float) $row->total,
            ]);

        $totalRecettesMois = $recettesParMode->sum('total');

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

        return view('livewire.dashboard', compact('stats', 'commandesProchesEcheance', 'commandesHorsDelai', 'recettesParMode', 'totalRecettesMois'))->layout('layouts.app');
    }
}
