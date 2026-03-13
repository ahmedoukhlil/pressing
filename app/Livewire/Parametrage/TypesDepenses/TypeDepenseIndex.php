<?php

namespace App\Livewire\Parametrage\TypesDepenses;

use App\Models\TypeDepense;
use Livewire\Component;

class TypeDepenseIndex extends Component
{
    public bool $afficherForm = false;
    public ?int $editId = null;
    public string $libelle = '';
    public string $icone = '';
    public string $couleur = '#6B7280';
    public int $ordre = 0;
    public bool $actif = true;

    public function nouveauType(): void
    {
        $this->reset(['libelle', 'icone', 'ordre', 'editId']);
        $this->couleur = '#6B7280';
        $this->actif = true;
        $this->afficherForm = true;
    }

    public function editer(int $id): void
    {
        $t = TypeDepense::findOrFail($id);
        $this->editId = $t->id;
        $this->libelle = $t->libelle;
        $this->icone = $t->icone ?? '';
        $this->couleur = $t->couleur;
        $this->ordre = $t->ordre;
        $this->actif = $t->actif;
        $this->afficherForm = true;
    }

    public function sauvegarder(): void
    {
        $this->validate([
            'libelle' => ['required', 'string', 'max:150'],
            'couleur' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'ordre' => ['nullable', 'integer', 'min:0'],
        ]);

        $data = [
            'libelle' => $this->libelle,
            'icone' => $this->icone ?: null,
            'couleur' => $this->couleur,
            'ordre' => $this->ordre,
            'actif' => $this->actif,
        ];

        if ($this->editId) {
            TypeDepense::findOrFail($this->editId)->update($data);
            $this->dispatch('notify', type: 'success', message: 'Type de depense mis a jour.');
        } else {
            TypeDepense::create($data);
            $this->dispatch('notify', type: 'success', message: 'Type de depense cree.');
        }

        $this->afficherForm = false;
        $this->reset(['libelle', 'icone', 'editId']);
    }

    public function supprimer(int $id): void
    {
        $t = TypeDepense::findOrFail($id);
        if ($t->depenses()->exists()) {
            $this->dispatch('notify', type: 'error', message: 'Suppression impossible: depenses liees.');
            return;
        }
        $t->delete();
        $this->dispatch('notify', type: 'success', message: 'Type de depense supprime.');
    }

    public function render()
    {
        return view('livewire.parametrage.types-depenses.type-depense-index', [
            'types' => TypeDepense::query()->orderBy('ordre')->get(),
        ])->layout('layouts.app');
    }
}
