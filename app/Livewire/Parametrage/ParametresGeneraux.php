<?php

namespace App\Livewire\Parametrage;

use App\Models\Client;
use App\Models\Commande;
use App\Models\Setting;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;

class ParametresGeneraux extends Component
{
    use WithPagination;

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

    public function updatingMoisCA(): void { $this->resetPage(); }
    public function updatingAnneeCA(): void { $this->resetPage(); }

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

        // CA mois courant par client
        $caMois = Commande::query()
            ->forCurrentSuccursale()
            ->where('statut', '!=', 'annule')
            ->whereBetween('date_depot', [$debutMois, $finMois])
            ->selectRaw('fk_id_client, SUM(montant_paye) as ca')
            ->groupBy('fk_id_client')
            ->pluck('ca', 'fk_id_client');

        // CA mois précédent par client
        $caPrevMois = Commande::query()
            ->forCurrentSuccursale()
            ->where('statut', '!=', 'annule')
            ->whereBetween('date_depot', [$debutPrevMois, $finPrevMois])
            ->selectRaw('fk_id_client, SUM(montant_paye) as ca')
            ->groupBy('fk_id_client')
            ->pluck('ca', 'fk_id_client');

        // CA total (tous temps) par client pour tri
        $caTotal = Commande::query()
            ->forCurrentSuccursale()
            ->where('statut', '!=', 'annule')
            ->selectRaw('fk_id_client, SUM(montant_paye) as ca')
            ->groupBy('fk_id_client')
            ->orderByDesc('ca')
            ->pluck('ca', 'fk_id_client');

        $clientIds = $caTotal->keys();

        $clients = Client::query()
            ->whereIn('id', $clientIds)
            ->get()
            ->keyBy('id');

        // Construire la collection triée
        $classement = $clientIds->map(function ($clientId) use ($clients, $caTotal, $caMois, $caPrevMois) {
            $client     = $clients[$clientId] ?? null;
            $caCourant  = (float) ($caMois[$clientId] ?? 0);
            $caPrev     = (float) ($caPrevMois[$clientId] ?? 0);
            $evolution  = $caPrev > 0 ? round((($caCourant - $caPrev) / $caPrev) * 100, 1) : null;

            return [
                'id'          => $clientId,
                'nom'         => $client?->full_name ?? '-',
                'telephone'   => $client?->telephone ?? '',
                'code'        => $client?->code_client ?? '',
                'ca_total'    => (float) ($caTotal[$clientId] ?? 0),
                'ca_mois'     => $caCourant,
                'ca_prev'     => $caPrev,
                'evolution'   => $evolution,
            ];
        })->values();

        // Pagination manuelle
        $page     = $this->getPage();
        $perPage  = 20;
        $total    = $classement->count();
        $items    = $classement->forPage($page, $perPage);
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $items, $total, $perPage, $page,
            ['path' => request()->url()]
        );

        $moisLabels = [
            1=>'يناير',2=>'فبراير',3=>'مارس',4=>'أبريل',5=>'مايو',6=>'يونيو',
            7=>'يوليو',8=>'أغسطس',9=>'سبتمبر',10=>'أكتوبر',11=>'نوفمبر',12=>'ديسمبر',
        ];

        $prevMois = $debutMois->copy()->subMonth();

        return view('livewire.parametrage.parametres-generaux', [
            'classement'      => $paginator,
            'moisLabels'      => $moisLabels,
            'labelMoisCourant'=> $moisLabels[$this->moisCA] . ' ' . $this->anneeCA,
            'labelMoisPrev'   => $moisLabels[$prevMois->month] . ' ' . $prevMois->year,
            'totalCA'         => $caTotal->sum(),
        ])->layout('layouts.app');
    }
}
