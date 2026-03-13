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

    public function render()
    {
        $clients = Client::query()
            ->forCurrentSuccursale()
            ->when($this->recherche, fn ($q) => $q
                ->where(fn ($sub) => $sub
                    ->where('nom', 'like', "%{$this->recherche}%")
                    ->orWhere('prenom', 'like', "%{$this->recherche}%")
                    ->orWhere('telephone', 'like', "%{$this->recherche}%")))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(20);

        return view('livewire.clients.client-index', [
            'clients' => $clients,
        ])->layout('layouts.app');
    }
}
