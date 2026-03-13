<?php

namespace App\Livewire\Parametrage\Fournisseurs;

use App\Models\Fournisseur;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class FournisseurIndex extends Component
{
    use WithPagination;

    public string $recherche = '';
    public string $filtreActif = 'tous';

    public bool $afficherForm = false;
    public ?int $editId = null;
    public string $nom = '';
    public string $telephone = '';
    public string $nif = '';
    public bool $actif = true;

    public function updatingRecherche(): void
    {
        $this->resetPage();
    }

    public function updatingFiltreActif(): void
    {
        $this->resetPage();
    }

    public function nouveau(): void
    {
        $this->reset(['nom', 'telephone', 'nif', 'editId']);
        $this->actif = true;
        $this->afficherForm = true;
    }

    public function editer(int $id): void
    {
        $f = Fournisseur::findOrFail($id);
        $this->editId = $f->id;
        $this->nom = $f->nom;
        $this->telephone = $f->telephone;
        $this->nif = $f->nif ?? '';
        $this->actif = $f->actif;
        $this->afficherForm = true;
    }

    public function sauvegarder(): void
    {
        $this->validate([
            'nom' => ['required', 'string', 'max:200'],
            'telephone' => ['required', 'string', 'max:20', Rule::unique('fournisseurs', 'telephone')->ignore($this->editId)],
            'nif' => ['nullable', 'string', 'max:50'],
        ]);

        $data = [
            'nom' => $this->nom,
            'telephone' => $this->telephone,
            'nif' => $this->nif ?: null,
            'actif' => $this->actif,
        ];

        if ($this->editId) {
            Fournisseur::findOrFail($this->editId)->update($data);
            $this->dispatch('notify', type: 'success', message: 'Fournisseur mis a jour.');
        } else {
            Fournisseur::create($data);
            $this->dispatch('notify', type: 'success', message: 'Fournisseur cree.');
        }

        $this->afficherForm = false;
        $this->reset(['nom', 'telephone', 'nif', 'editId']);
    }

    public function toggleActif(int $id): void
    {
        $f = Fournisseur::findOrFail($id);
        $f->update(['actif' => !$f->actif]);
        $this->dispatch('notify', type: 'success', message: 'Statut fournisseur mis a jour.');
    }

    public function supprimer(int $id): void
    {
        $f = Fournisseur::findOrFail($id);
        if ($f->depenses()->exists()) {
            $this->dispatch('notify', type: 'error', message: 'Suppression impossible: depenses liees.');
            return;
        }
        $f->delete();
        $this->dispatch('notify', type: 'success', message: 'Fournisseur supprime.');
    }

    public function render()
    {
        $query = Fournisseur::query()
            ->when($this->recherche, fn ($q) => $q
                ->where('nom', 'like', "%{$this->recherche}%")
                ->orWhere('telephone', 'like', "%{$this->recherche}%")
                ->orWhere('nif', 'like', "%{$this->recherche}%"))
            ->when($this->filtreActif === 'actif', fn ($q) => $q->where('actif', true))
            ->when($this->filtreActif === 'inactif', fn ($q) => $q->where('actif', false))
            ->orderBy('nom');

        return view('livewire.parametrage.fournisseurs.fournisseur-index', [
            'fournisseurs' => $query->paginate(20),
        ])->layout('layouts.app');
    }
}
