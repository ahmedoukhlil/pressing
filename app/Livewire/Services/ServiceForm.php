<?php

namespace App\Livewire\Services;

use App\Models\Service;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class ServiceForm extends Component
{
    use WithFileUploads;

    public ?int $serviceId = null;
    public string $libelle = '';
    public string $libelleAr = '';
    public string $icone = '';
    public string $prix = '';
    public int $ordre = 0;
    public bool $actif = true;
    public $nouvelleImage = null;
    public ?string $imageActuelle = null;

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
        $this->imageActuelle = $s->image;
    }

    public function supprimerImage(): void
    {
        if ($this->imageActuelle && $this->serviceId) {
            Storage::disk('public')->delete($this->imageActuelle);
            Service::findOrFail($this->serviceId)->update(['image' => null]);
            $this->imageActuelle = null;
        }
        $this->nouvelleImage = null;
    }

    public function sauvegarder(): void
    {
        $this->validate([
            'libelleAr' => ['required', 'string', 'max:100'],
            'libelle' => ['nullable', 'string', 'max:100'],
            'prix' => ['required', 'numeric', 'min:0'],
            'ordre' => ['nullable', 'integer', 'min:0'],
            'nouvelleImage' => ['nullable', 'image', 'max:2048'],
        ]);

        $data = [
            'libelle' => $this->libelle ?: $this->libelleAr,
            'libelle_ar' => $this->libelleAr,
            'icone' => $this->icone ?: null,
            'prix' => $this->prix,
            'ordre' => $this->ordre,
            'actif' => $this->actif,
        ];

        if ($this->nouvelleImage) {
            if ($this->imageActuelle) {
                Storage::disk('public')->delete($this->imageActuelle);
            }
            $data['image'] = $this->nouvelleImage->store('services', 'public');
        }

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
