<?php

namespace App\Livewire\Parametrage\ModesPaiement;

use App\Models\ModePaiement;
use Illuminate\Validation\Rule;
use Livewire\Component;

class ModePaiementIndex extends Component
{
    public bool $afficherForm = false;
    public ?int $editId = null;

    public string $libelle = '';
    public string $code = '';
    public string $icone = '';
    public int $ordre = 0;
    public bool $actif = true;

    public function nouveauMode(): void
    {
        $this->reset(['libelle', 'code', 'icone', 'ordre', 'editId']);
        $this->actif = true;
        $this->afficherForm = true;
    }

    public function editer(int $id): void
    {
        $mode = ModePaiement::findOrFail($id);
        $this->editId = $mode->id;
        $this->libelle = $mode->libelle;
        $this->code = $mode->code;
        $this->icone = $mode->icone ?? '';
        $this->ordre = $mode->ordre;
        $this->actif = $mode->actif;
        $this->afficherForm = true;
    }

    public function sauvegarder(): void
    {
        $this->validate([
            'libelle' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:30', Rule::unique('modes_paiement', 'code')->ignore($this->editId)],
            'ordre' => ['nullable', 'integer', 'min:0'],
        ]);

        $data = [
            'libelle' => $this->libelle,
            'code' => strtolower($this->code),
            'icone' => $this->icone ?: null,
            'ordre' => $this->ordre,
            'actif' => $this->actif,
        ];

        if ($this->editId) {
            $mode = ModePaiement::findOrFail($this->editId);
            if ($mode->est_systeme) {
                $data['code'] = $mode->code;
            }
            $mode->update($data);
            $this->dispatch('notify', type: 'success', message: 'Mode de paiement mis a jour.');
        } else {
            ModePaiement::create($data);
            $this->dispatch('notify', type: 'success', message: 'Mode de paiement cree.');
        }

        $this->afficherForm = false;
        $this->reset(['libelle', 'code', 'icone', 'ordre', 'editId']);
    }

    public function toggleActif(int $id): void
    {
        $mode = ModePaiement::findOrFail($id);
        if ($mode->est_systeme) {
            return;
        }
        $mode->update(['actif' => !$mode->actif]);
        $this->dispatch('notify', type: 'success', message: 'Statut du mode mis a jour.');
    }

    public function supprimer(int $id): void
    {
        $mode = ModePaiement::findOrFail($id);
        if ($mode->est_systeme) {
            return;
        }
        $mode->delete();
        $this->dispatch('notify', type: 'success', message: 'Mode de paiement supprime.');
    }

    public function render()
    {
        return view('livewire.parametrage.modes-paiement.mode-paiement-index', [
            'modes' => ModePaiement::query()->orderBy('ordre')->get(),
        ])->layout('layouts.app');
    }
}
