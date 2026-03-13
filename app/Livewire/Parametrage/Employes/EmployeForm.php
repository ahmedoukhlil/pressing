<?php

namespace App\Livewire\Parametrage\Employes;

use App\Models\Employe;
use App\Models\Poste;
use Livewire\Component;

class EmployeForm extends Component
{
    public ?int $employeId = null;
    public string $nom = '';
    public string $prenom = '';
    public string $telephone = '';
    public ?int $fkIdPoste = null;
    public string $dateEmbauche = '';
    public string $salaireBrut = '';
    public bool $actif = true;
    public string $notes = '';

    public function mount(?int $id = null): void
    {
        if (!$id) {
            return;
        }

        $e = Employe::findOrFail($id);
        $this->employeId = $e->id;
        $this->nom = $e->nom;
        $this->prenom = $e->prenom ?? '';
        $this->telephone = $e->telephone ?? '';
        $this->fkIdPoste = $e->fk_id_poste;
        $this->dateEmbauche = $e->date_embauche?->toDateString() ?? '';
        $this->salaireBrut = (string) $e->salaire_brut;
        $this->actif = $e->actif;
        $this->notes = $e->notes ?? '';
    }

    public function sauvegarder(): void
    {
        $this->validate([
            'nom' => ['required', 'string', 'max:100'],
            'telephone' => ['nullable', 'string', 'max:20', 'unique:employes,telephone,' . ($this->employeId ?? 'NULL') . ',id'],
            'salaireBrut' => ['required', 'numeric', 'min:0'],
            'dateEmbauche' => ['nullable', 'date'],
            'fkIdPoste' => ['nullable', 'exists:postes,id'],
        ]);

        $data = [
            'nom' => $this->nom,
            'prenom' => $this->prenom ?: null,
            'telephone' => $this->telephone ?: null,
            'fk_id_poste' => $this->fkIdPoste,
            'date_embauche' => $this->dateEmbauche ?: null,
            'salaire_brut' => $this->salaireBrut,
            'actif' => $this->actif,
            'notes' => $this->notes ?: null,
        ];

        if ($this->employeId) {
            Employe::findOrFail($this->employeId)->update($data);
        } else {
            Employe::create($data);
        }

        $this->redirect(route('parametrage.employes.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.parametrage.employes.employe-form', [
            'postes' => Poste::actif()->get(),
        ])->layout('layouts.app');
    }
}
