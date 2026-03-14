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
    public bool $pointsEnabled = true;
    public string $pointsMruPerPoint = '10';
    public string $pointsMruDiscountPerPoint = '10';

    public function mount(): void
    {
        $settings = Setting::query()
            ->whereIn('key', [
                'nom_pressing',
                'adresse_pressing',
                'telephone_pressing',
                'footer_ticket',
                'points_enabled',
                'points_mru_per_point',
                'points_mru_discount_per_point',
            ])
            ->pluck('value', 'key');

        $this->nomPressing = (string) ($settings['nom_pressing'] ?? '');
        $this->adresse = (string) ($settings['adresse_pressing'] ?? '');
        $this->telephone = (string) ($settings['telephone_pressing'] ?? '');
        $this->footerTicket = (string) ($settings['footer_ticket'] ?? '');
        $this->pointsEnabled = in_array(strtolower((string) ($settings['points_enabled'] ?? '1')), ['1', 'true', 'yes', 'on'], true);
        $this->pointsMruPerPoint = (string) ($settings['points_mru_per_point'] ?? '10');
        $this->pointsMruDiscountPerPoint = (string) ($settings['points_mru_discount_per_point'] ?? $this->pointsMruPerPoint);
    }

    public function sauvegarder(): void
    {
        $data = $this->validate([
            'nomPressing' => ['required', 'string', 'max:120'],
            'adresse' => ['nullable', 'string', 'max:255'],
            'telephone' => ['nullable', 'string', 'max:25'],
            'footerTicket' => ['nullable', 'string', 'max:255'],
            'pointsEnabled' => ['required', 'boolean'],
            'pointsMruPerPoint' => ['required', 'numeric', 'min:0.01', 'max:100000'],
            'pointsMruDiscountPerPoint' => ['required', 'numeric', 'min:0.01', 'max:100000'],
        ]);

        Setting::updateOrCreate(['key' => 'nom_pressing'], ['value' => $data['nomPressing']]);
        Setting::updateOrCreate(['key' => 'adresse_pressing'], ['value' => $data['adresse']]);
        Setting::updateOrCreate(['key' => 'telephone_pressing'], ['value' => $data['telephone']]);
        Setting::updateOrCreate(['key' => 'footer_ticket'], ['value' => $data['footerTicket']]);
        Setting::updateOrCreate(['key' => 'points_enabled'], ['value' => $data['pointsEnabled'] ? '1' : '0']);
        Setting::updateOrCreate(['key' => 'points_mru_per_point'], ['value' => (string) $data['pointsMruPerPoint']]);
        Setting::updateOrCreate(['key' => 'points_mru_discount_per_point'], ['value' => (string) $data['pointsMruDiscountPerPoint']]);

        $this->dispatch('notify', type: 'success', message: 'تم حفظ الإعدادات بنجاح.');
        session()->flash('success', 'تم حفظ الإعدادات بنجاح.');
    }

    public function render()
    {
        return view('livewire.parametrage.parametres-generaux')->layout('layouts.app');
    }
}
