<?php

namespace App\Livewire\Admin\Succursales;

use App\Models\Succursale;
use Livewire\Component;

class SuccursaleIndex extends Component
{
    public string $nom = '';
    public string $code = '';
    public bool $actif = true;
    public ?int $editId = null;

    public function editer(int $id): void
    {
        $succursale = Succursale::findOrFail($id);
        $this->editId = $succursale->id;
        $this->nom = $succursale->nom;
        $this->code = $succursale->code;
        $this->actif = (bool) $succursale->actif;
    }

    public function annulerEdition(): void
    {
        $this->reset(['editId', 'nom', 'code', 'actif']);
        $this->actif = true;
    }

    public function sauvegarder(): void
    {
        $data = $this->validate([
            'nom' => ['required', 'string', 'max:120'],
            'code' => ['required', 'string', 'max:30', 'unique:succursales,code,' . ($this->editId ?? 'NULL') . ',id'],
            'actif' => ['boolean'],
        ]);

        if ($this->editId) {
            Succursale::findOrFail($this->editId)->update($data);
            $this->dispatch('notify', type: 'success', message: 'تم تحديث الفرع.');
        } else {
            Succursale::create($data);
            $this->dispatch('notify', type: 'success', message: 'تم إنشاء الفرع.');
        }

        $this->annulerEdition();
    }

    public function basculerActif(int $id): void
    {
        $succursale = Succursale::findOrFail($id);
        $succursale->update(['actif' => !$succursale->actif]);
        $this->dispatch('notify', type: 'success', message: 'تم تحديث حالة الفرع.');
    }

    public function render()
    {
        return view('livewire.admin.succursales.succursale-index', [
            'succursales' => Succursale::query()->latest()->get(),
        ])->layout('layouts.app');
    }
}

