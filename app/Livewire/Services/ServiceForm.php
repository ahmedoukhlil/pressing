<?php

namespace App\Livewire\Services;

use App\Models\Service;
use Livewire\Component;

class ServiceForm extends Component
{
    public ?int $serviceId = null;
    public string $libelle = '';
    public string $libelleAr = '';
    public string $icone = '';
    public string $prix = '';
    public int $ordre = 0;
    public bool $actif = true;

    public function mount(?int $id = null): void
    {
        if (!$id) {
            return;
        }

        $s = Service::findOrFail($id);
        $this->serviceId = $s->id;
        $this->libelle = $s->libelle;
        $this->libelleAr = $s->libelle_ar ?? '';
        $this->icone = $s->icone ?? '';
        $this->prix = (string) $s->prix;
        $this->ordre = $s->ordre;
        $this->actif = $s->actif;
    }

    public function sauvegarder(): void
    {
        $this->validate([
            'libelleAr' => ['required', 'string', 'max:100'],
            'libelle' => ['nullable', 'string', 'max:100'],
            'prix' => ['required', 'numeric', 'min:0'],
            'ordre' => ['nullable', 'integer', 'min:0'],
        ]);

        $data = [
            'libelle' => $this->libelle ?: $this->libelleAr,
            'libelle_ar' => $this->libelleAr,
            'icone' => $this->icone ?: null,
            'prix' => $this->prix,
            'ordre' => $this->ordre,
            'actif' => $this->actif,
        ];

        if ($this->serviceId) {
            Service::findOrFail($this->serviceId)->update($data);
        } else {
            Service::create($data);
        }

        $this->redirect(route('services.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.services.service-form')->layout('layouts.app');
    }
}
