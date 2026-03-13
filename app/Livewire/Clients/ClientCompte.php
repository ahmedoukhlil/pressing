<?php

namespace App\Livewire\Clients;

use App\Models\Client;
use App\Models\Commande;
use Livewire\Component;
use Livewire\WithPagination;

class ClientCompte extends Component
{
    use WithPagination;

    public Client $client;

    public function mount(int $id): void
    {
        $this->client = Client::query()->forCurrentSuccursale()->findOrFail($id);
    }

    public function render()
    {
        $totaux = Commande::query()
            ->forCurrentSuccursale()
            ->where('fk_id_client', $this->client->id)
            ->selectRaw('
                COALESCE(SUM(montant_total), 0) as total_facture,
                COALESCE(SUM(montant_paye), 0) as total_paye,
                COALESCE(SUM(reste_a_payer), 0) as total_reste
            ')
            ->first();

        $commandes = Commande::query()
            ->forCurrentSuccursale()
            ->where('fk_id_client', $this->client->id)
            ->orderByDesc('date_depot')
            ->with(['details.service'])
            ->paginate(10);

        $totalFacture = (float) ($totaux->total_facture ?? 0);
        $totalPaye = (float) ($totaux->total_paye ?? 0);
        $clientDoit = (float) ($totaux->total_reste ?? 0);
        $pressingDoit = max(0, round($totalPaye - $totalFacture, 2));

        return view('livewire.clients.client-compte', [
            'commandes' => $commandes,
            'totalFacture' => $totalFacture,
            'totalPaye' => $totalPaye,
            'clientDoit' => $clientDoit,
            'pressingDoit' => $pressingDoit,
        ])->layout('layouts.app');
    }
}
