<?php

namespace App\Livewire\Services;

use App\Models\Service;
use Livewire\Component;
use Livewire\WithPagination;

class ServiceIndex extends Component
{
    use WithPagination;

    public string $recherche = '';
    public string $sortField = 'ordre';
    public string $sortDirection = 'asc';

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
        $services = Service::query()
            ->when($this->recherche, fn ($q) => $q
                ->where('libelle', 'like', "%{$this->recherche}%")
                ->orWhere('libelle_ar', 'like', "%{$this->recherche}%"))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(20);

        return view('livewire.services.service-index', [
            'services' => $services,
        ])->layout('layouts.app');
    }
}
