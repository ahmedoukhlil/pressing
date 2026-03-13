<?php

namespace App\Livewire\Parametrage;

use App\Models\Setting;
use Livewire\Component;

class ParametresGeneraux extends Component
{
    public string $nomPressing = '';
    public string $adresse = '';
    public string $telephone = '';
    public string $footerTicket = '';

    public function mount(): void
    {
        $settings = Setting::query()
            ->whereIn('key', ['nom_pressing', 'adresse_pressing', 'telephone_pressing', 'footer_ticket'])
            ->pluck('value', 'key');

        $this->nomPressing = (string) ($settings['nom_pressing'] ?? '');
        $this->adresse = (string) ($settings['adresse_pressing'] ?? '');
        $this->telephone = (string) ($settings['telephone_pressing'] ?? '');
        $this->footerTicket = (string) ($settings['footer_ticket'] ?? '');
    }

    public function sauvegarder(): void
    {
        $data = $this->validate([
            'nomPressing' => ['required', 'string', 'max:120'],
            'adresse' => ['nullable', 'string', 'max:255'],
            'telephone' => ['nullable', 'string', 'max:25'],
            'footerTicket' => ['nullable', 'string', 'max:255'],
        ]);

        Setting::updateOrCreate(['key' => 'nom_pressing'], ['value' => $data['nomPressing']]);
        Setting::updateOrCreate(['key' => 'adresse_pressing'], ['value' => $data['adresse']]);
        Setting::updateOrCreate(['key' => 'telephone_pressing'], ['value' => $data['telephone']]);
        Setting::updateOrCreate(['key' => 'footer_ticket'], ['value' => $data['footerTicket']]);

        $this->dispatch('notify', type: 'success', message: 'Parametres enregistres.');
        session()->flash('success', 'Parametres enregistres.');
    }

    public function render()
    {
        return view('livewire.parametrage.parametres-generaux')->layout('layouts.app');
    }
}
