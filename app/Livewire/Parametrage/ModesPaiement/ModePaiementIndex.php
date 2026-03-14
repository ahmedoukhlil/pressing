<?php

namespace App\Livewire\Parametrage\ModesPaiement;

use App\Models\ModePaiement;
use Illuminate\Support\Str;
use Livewire\Component;

class ModePaiementIndex extends Component
{
    public bool $afficherForm = false;
    public ?int $editId = null;

    public string $libelle = '';
    public string $icone = '';
    public int $ordre = 0;
    public bool $actif = true;
    public ?int $modeActionId = null;
    public string $modeActionType = '';

    public function nouveauMode(): void
    {
        $this->reset(['libelle', 'icone', 'ordre', 'editId']);
        $this->actif = true;
        $this->afficherForm = true;
    }

    public function editer(int $id): void
    {
        $mode = ModePaiement::findOrFail($id);
        $this->editId = $mode->id;
        $this->libelle = $mode->libelle;
        $this->icone = $mode->icone ?? '';
        $this->ordre = $mode->ordre;
        $this->actif = $mode->actif;
        $this->afficherForm = true;
    }

    public function sauvegarder(): void
    {
        $this->validate([
            'libelle' => ['required', 'string', 'max:100'],
            'ordre' => ['nullable', 'integer', 'min:0'],
        ]);

        $data = [
            'libelle' => $this->libelle,
            'icone' => $this->icone ?: null,
            'ordre' => $this->ordre,
            'actif' => $this->actif,
        ];

        if ($this->editId) {
            $mode = ModePaiement::findOrFail($this->editId);
            // Ne jamais modifier le code interne d'une methode existante
            // pour preserver les references historiques.
            $data['code'] = $mode->code;
            $mode->update($data);
            $this->dispatch('notify', type: 'success', message: 'تم تحديث طريقة الدفع.');
        } else {
            $data['code'] = $this->buildUniqueCodeFromLibelle($this->libelle);
            ModePaiement::create($data);
            $this->dispatch('notify', type: 'success', message: 'تم إنشاء طريقة الدفع.');
        }

        $this->afficherForm = false;
        $this->reset(['libelle', 'icone', 'ordre', 'editId']);
    }

    public function demanderToggleActif(int $id): void
    {
        $this->modeActionId = $id;
        $this->modeActionType = 'toggle';
    }

    public function demanderSuppression(int $id): void
    {
        $this->modeActionId = $id;
        $this->modeActionType = 'delete';
    }

    public function annulerActionMode(): void
    {
        $this->modeActionId = null;
        $this->modeActionType = '';
    }

    public function confirmerActionMode(): void
    {
        if (!$this->modeActionId || $this->modeActionType === '') {
            return;
        }

        if ($this->modeActionType === 'toggle') {
            $this->toggleActif($this->modeActionId);
        }

        if ($this->modeActionType === 'delete') {
            $this->supprimer($this->modeActionId);
        }

        $this->annulerActionMode();
    }

    public function toggleActif(int $id): void
    {
        $mode = ModePaiement::findOrFail($id);
        if ($mode->est_systeme) {
            return;
        }
        $mode->update(['actif' => !$mode->actif]);
        $this->dispatch('notify', type: 'success', message: 'تم تحديث حالة طريقة الدفع.');
    }

    public function supprimer(int $id): void
    {
        $mode = ModePaiement::findOrFail($id);
        if ($mode->est_systeme) {
            return;
        }
        $mode->delete();
        $this->dispatch('notify', type: 'success', message: 'تم حذف طريقة الدفع.');
    }

    public function render()
    {
        return view('livewire.parametrage.modes-paiement.mode-paiement-index', [
            'modes' => ModePaiement::query()->orderBy('ordre')->get(),
        ])->layout('layouts.app');
    }

    private function buildUniqueCodeFromLibelle(string $libelle): string
    {
        $base = Str::slug($libelle, '_');
        if ($base === '') {
            $base = 'mode_paiement';
        }

        $candidate = strtolower($base);
        $suffix = 2;
        while (ModePaiement::query()->where('code', $candidate)->exists()) {
            $candidate = strtolower($base . '_' . $suffix);
            $suffix++;
        }

        return $candidate;
    }
}
