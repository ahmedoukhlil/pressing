<?php

namespace App\Livewire\Parametrage\Employes;

use App\Models\Employe;
use Livewire\Component;
use Livewire\WithPagination;

class EmployeIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Employe::with('poste')
            ->when($this->search !== '', fn ($q) => $q
                ->where('nom', 'like', '%' . $this->search . '%')
                ->orWhere('prenom', 'like', '%' . $this->search . '%')
                ->orWhere('telephone', 'like', '%' . $this->search . '%'))
            ->orderBy('nom');

        return view('livewire.parametrage.employes.employe-index', [
            'employes' => $query->paginate(20),
        ])->layout('layouts.app');
    }
}
