<?php

namespace App\Livewire\Parametrage;

use App\Models\CaisseOperation;
use App\Models\Client;
use App\Models\Setting;
use Illuminate\Support\Carbon;
use Livewire\Component;

class ParametresGeneraux extends Component
{

    public string $nomPressing = '';
    public string $adresse = '';
    public string $telephone = '';
    public string $footerTicket = '';

    public int $anneeCA;
    public int $moisCA;

    public function mount(): void
    {
        $this->anneeCA = now()->year;
        $this->moisCA  = now()->month;

        $settings = Setting::query()
            ->whereIn('key', ['nom_pressing', 'adresse_pressing', 'telephone_pressing', 'footer_ticket'])
            ->pluck('value', 'key');

        $this->nomPressing  = (string) ($settings['nom_pressing'] ?? '');
        $this->adresse      = (string) ($settings['adresse_pressing'] ?? '');
        $this->telephone    = (string) ($settings['telephone_pressing'] ?? '');
        $this->footerTicket = (string) ($settings['footer_ticket'] ?? '');
    }

    public function sauvegarder(): void
    {
        $data = $this->validate([
            'nomPressing'  => ['required', 'string', 'max:120'],
            'adresse'      => ['nullable', 'string', 'max:255'],
            'telephone'    => ['nullable', 'string', 'max:25'],
            'footerTicket' => ['nullable', 'string', 'max:255'],
        ]);

        Setting::updateOrCreate(['key' => 'nom_pressing'],       ['value' => $data['nomPressing']]);
        Setting::updateOrCreate(['key' => 'adresse_pressing'],   ['value' => $data['adresse']]);
        Setting::updateOrCreate(['key' => 'telephone_pressing'], ['value' => $data['telephone']]);
        Setting::updateOrCreate(['key' => 'footer_ticket'],      ['value' => $data['footerTicket']]);

        $this->dispatch('notify', type: 'success', message: 'تم حفظ الإعدادات بنجاح.');
        session()->flash('success', 'تم حفظ الإعدادات بنجاح.');
    }

    public function render()
    {
        // Période courante et précédente
        $debutMois    = Carbon::createFromDate($this->anneeCA, $this->moisCA, 1)->startOfDay();
        $finMois      = $debutMois->copy()->endOfMonth()->endOfDay();
        $debutPrevMois = $debutMois->copy()->subMonth()->startOfDay();
        $finPrevMois   = $debutMois->copy()->subMonth()->endOfMonth()->endOfDay();

        // Top 10 clients par montants encaissés (mois courant)
        $rows = CaisseOperation::query()
            ->forCurrentSuccursale()
            ->whereNotNull('fk_id_client')
            ->whereBetween('date_operation', [$debutMois, $finMois])
            ->selectRaw('fk_id_client, SUM(montant_operation) as ca_mois')
            ->groupBy('fk_id_client')
            ->orderByDesc('ca_mois')
            ->limit(10)
            ->get();

        $clientIds = $rows->pluck('fk_id_client');

        // Mois précédent pour les mêmes clients
        $caPrevMois = CaisseOperation::query()
            ->forCurrentSuccursale()
            ->whereIn('fk_id_client', $clientIds)
            ->whereBetween('date_operation', [$debutPrevMois, $finPrevMois])
            ->selectRaw('fk_id_client, SUM(montant_operation) as ca')
            ->groupBy('fk_id_client')
            ->pluck('ca', 'fk_id_client');

        $clients = Client::query()->whereIn('id', $clientIds)->get()->keyBy('id');

        $classement = $rows->map(function ($row) use ($clients, $caPrevMois) {
            $client    = $clients[$row->fk_id_client] ?? null;
            $caCourant = (float) $row->ca_mois;
            $caPrev    = (float) ($caPrevMois[$row->fk_id_client] ?? 0);
            $evolution = $caPrev > 0 ? round((($caCourant - $caPrev) / $caPrev) * 100, 1) : null;

            return [
                'nom'       => $client?->full_name ?? '-',
                'telephone' => $client?->telephone ?? '',
                'ca_mois'   => $caCourant,
                'ca_prev'   => $caPrev,
                'evolution' => $evolution,
            ];
        });

        $moisLabels = [
            1=>'يناير',2=>'فبراير',3=>'مارس',4=>'أبريل',5=>'مايو',6=>'يونيو',
            7=>'يوليو',8=>'أغسطس',9=>'سبتمبر',10=>'أكتوبر',11=>'نوفمبر',12=>'ديسمبر',
        ];

        $prevMois = $debutMois->copy()->subMonth();

        return view('livewire.parametrage.parametres-generaux', [
            'classement'       => $classement,
            'moisLabels'       => $moisLabels,
            'labelMoisCourant' => $moisLabels[$this->moisCA] . ' ' . $this->anneeCA,
            'labelMoisPrev'    => $moisLabels[$prevMois->month] . ' ' . $prevMois->year,
        ])->layout('layouts.app');
    }
}
