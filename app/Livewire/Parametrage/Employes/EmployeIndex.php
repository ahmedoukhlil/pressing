<?php

namespace App\Livewire\Parametrage\Employes;

use App\Models\Depense;
use App\Models\Employe;
use App\Support\SuccursaleContext;
use Livewire\Component;
use Livewire\WithPagination;

class EmployeIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public ?int $employeDepensesId = null;
    public bool $afficherDepensesModal = false;
    private const TYPE_SALAIRES_LABEL = 'الرواتب';
    private const TYPE_TRANSPORT_LABEL = 'النقل';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function ouvrirDepensesEmploye(int $employeId): void
    {
        $this->employeDepensesId = $employeId;
        $this->afficherDepensesModal = true;
    }

    public function fermerDepensesEmploye(): void
    {
        $this->employeDepensesId = null;
        $this->afficherDepensesModal = false;
    }

    public function render()
    {
        $succursaleId = SuccursaleContext::currentIdForRead();

        $depensesEmployesBase = Depense::query()
            ->forCurrentSuccursale()
            ->validee()
            ->whereHas('typeDepense', fn ($typeQuery) => $typeQuery->whereIn('libelle', [self::TYPE_SALAIRES_LABEL, self::TYPE_TRANSPORT_LABEL]));

        $query = Employe::with('poste')
            ->withSum(['depenses as total_transport' => function ($q) use ($succursaleId): void {
                $q->where('statut', 'validee')
                    ->whereHas('typeDepense', fn ($typeQuery) => $typeQuery->where('libelle', self::TYPE_TRANSPORT_LABEL));

                if ($succursaleId) {
                    $q->where('fk_id_succursale', $succursaleId);
                }
            }], 'montant')
            ->withSum(['depenses as total_salaires' => function ($q) use ($succursaleId): void {
                $q->where('statut', 'validee')
                    ->whereHas('typeDepense', fn ($typeQuery) => $typeQuery->where('libelle', self::TYPE_SALAIRES_LABEL));

                if ($succursaleId) {
                    $q->where('fk_id_succursale', $succursaleId);
                }
            }], 'montant')
            ->when($this->search !== '', fn ($q) => $q
                ->where('nom', 'like', '%' . $this->search . '%')
                ->orWhere('prenom', 'like', '%' . $this->search . '%')
                ->orWhere('telephone', 'like', '%' . $this->search . '%'))
            ->orderBy('nom');

        $employes = $query->paginate(20);

        $employeDetails = null;
        $depensesEmploye = collect();
        $depensesEmployeSalaires = 0.0;
        $depensesEmployeTransport = 0.0;
        if ($this->afficherDepensesModal && $this->employeDepensesId) {
            $employeDetails = Employe::query()->find($this->employeDepensesId);
            if ($employeDetails) {
                $depensesEmploye = Depense::query()
                    ->forCurrentSuccursale()
                    ->with('typeDepense')
                    ->where('fk_id_employe', $this->employeDepensesId)
                    ->where('statut', 'validee')
                    ->whereHas('typeDepense', fn ($typeQuery) => $typeQuery->whereIn('libelle', [self::TYPE_SALAIRES_LABEL, self::TYPE_TRANSPORT_LABEL]))
                    ->latest('date_depense')
                    ->limit(25)
                    ->get();

                $depensesEmployeSalaires = (float) $depensesEmploye->filter(
                    fn ($depense) => $depense->typeDepense?->libelle === self::TYPE_SALAIRES_LABEL
                )->sum('montant');

                $depensesEmployeTransport = (float) $depensesEmploye->filter(
                    fn ($depense) => $depense->typeDepense?->libelle === self::TYPE_TRANSPORT_LABEL
                )->sum('montant');
            }
        }

        return view('livewire.parametrage.employes.employe-index', [
            'employes' => $employes,
            'statsEmployes' => [
                'count' => Employe::query()->count(),
                'salaires' => (float) (clone $depensesEmployesBase)->whereHas('typeDepense', fn ($q) => $q->where('libelle', self::TYPE_SALAIRES_LABEL))->sum('montant'),
                'transport' => (float) (clone $depensesEmployesBase)->whereHas('typeDepense', fn ($q) => $q->where('libelle', self::TYPE_TRANSPORT_LABEL))->sum('montant'),
            ],
            'employeDetails' => $employeDetails,
            'depensesEmploye' => $depensesEmploye,
            'depensesEmployeSalaires' => $depensesEmployeSalaires,
            'depensesEmployeTransport' => $depensesEmployeTransport,
        ])->layout('layouts.app');
    }
}
