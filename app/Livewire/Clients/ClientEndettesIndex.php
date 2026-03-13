<?php

namespace App\Livewire\Clients;

use App\Models\Client;
use Livewire\Component;
use Livewire\WithPagination;

class ClientEndettesIndex extends Component
{
    use WithPagination;

    public string $recherche = '';
    public ?int $clientASupprimerId = null;
    public string $clientASupprimerNom = '';
    public bool $afficherConfirmationSuppression = false;

    public function updatingRecherche(): void
    {
        $this->resetPage();
    }

    public function demanderSuppressionClient(int $id): void
    {
        if (!auth()->user()?->hasRole('gerant')) {
            abort(403);
        }

        $client = Client::query()
            ->forCurrentSuccursale()
            ->findOrFail($id);

        $this->clientASupprimerId = $client->id;
        $this->clientASupprimerNom = $client->full_name;
        $this->afficherConfirmationSuppression = true;
    }

    public function annulerSuppressionClient(): void
    {
        $this->afficherConfirmationSuppression = false;
        $this->clientASupprimerId = null;
        $this->clientASupprimerNom = '';
    }

    public function confirmerSuppressionClient(): void
    {
        if (!auth()->user()?->hasRole('gerant')) {
            abort(403);
        }

        if (!$this->clientASupprimerId) {
            $this->annulerSuppressionClient();
            return;
        }

        $client = Client::query()
            ->forCurrentSuccursale()
            ->withCount('commandes')
            ->find($this->clientASupprimerId);

        if (!$client) {
            $this->dispatch('notify', type: 'error', message: 'الزبون غير موجود.');
            $this->annulerSuppressionClient();
            return;
        }

        if ((int) $client->commandes_count > 0) {
            $this->dispatch('notify', type: 'error', message: 'لا يمكن حذف الزبون لأنه مرتبط بطلبات.');
            $this->annulerSuppressionClient();
            return;
        }

        $client->delete();
        $this->dispatch('notify', type: 'success', message: 'تم حذف الزبون بنجاح.');
        $this->annulerSuppressionClient();
    }

    public function render()
    {
        $clients = Client::query()
            ->forCurrentSuccursale()
            ->whereHas('commandes', fn ($q) => $q->where('reste_a_payer', '>', 0))
            ->withSum(
                ['commandes as total_dette' => fn ($q) => $q->where('reste_a_payer', '>', 0)],
                'reste_a_payer'
            )
            ->when($this->recherche, fn ($q) => $q
                ->where(fn ($sub) => $sub
                    ->where('nom', 'like', "%{$this->recherche}%")
                    ->orWhere('prenom', 'like', "%{$this->recherche}%")
                    ->orWhere('telephone', 'like', "%{$this->recherche}%")))
            ->orderByDesc('total_dette')
            ->paginate(20);

        return view('livewire.clients.client-endettes-index', [
            'clients' => $clients,
        ])->layout('layouts.app');
    }
}
