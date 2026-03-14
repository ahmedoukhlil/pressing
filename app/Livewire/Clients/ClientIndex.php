<?php

namespace App\Livewire\Clients;

use App\Models\Client;
use Livewire\Component;
use Livewire\WithPagination;

class ClientIndex extends Component
{
    use WithPagination;

    public string $recherche = '';
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';
    public ?int $clientASupprimerId = null;
    public string $clientASupprimerNom = '';
    public bool $afficherConfirmationSuppression = false;

    public function updatingRecherche(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
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
            ->when($this->recherche, fn ($q) => $q
                ->where(fn ($sub) => $sub
                    ->where('code_client', 'like', "%{$this->recherche}%")
                    ->orWhere('nom', 'like', "%{$this->recherche}%")
                    ->orWhere('prenom', 'like', "%{$this->recherche}%")
                    ->orWhere('telephone', 'like', "%{$this->recherche}%")))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(20);

        return view('livewire.clients.client-index', [
            'clients' => $clients,
        ])->layout('layouts.app');
    }
}
