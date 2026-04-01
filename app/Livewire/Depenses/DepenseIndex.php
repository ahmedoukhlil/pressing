<?php

namespace App\Livewire\Depenses;

use App\Models\Depense;
use App\Models\Employe;
use App\Models\Fournisseur;
use App\Models\ModePaiement;
use App\Models\TypeDepense;
use App\Support\SuccursaleContext;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class DepenseIndex extends Component
{
    use WithPagination;

    public string $filtrePeriode = 'mois';
    public bool $afficherFiltresAvances = false;
    public $filtreType = null;
    public string $filtreCategorie = 'toutes';
    public string $recherche = '';
    public string $sortField = 'date_depense';
    public string $sortDirection = 'desc';

    public bool $afficherForm = false;
    public ?int $editId = null;
    public string $dateDepense = '';
    public $fkIdTypeDepense = null;
    public string $designation = '';
    public string $montant = '';
    public string $modePaiement = 'especes';
    public $fkIdFournisseur = null;
    public $fkIdEmploye = null;
    public string $reference = '';
    public string $notes = '';
    public ?int $depenseAAnnulerId = null;
    private const TYPE_SALAIRES_LABEL = 'الرواتب';
    private const TYPE_TRANSPORT_LABEL = 'النقل';

    public function mount(): void
    {
        $this->dateDepense = now()->toDateString();
        $this->ensureTypeSalairesActif();
    }

    public function updatingFiltrePeriode(): void
    {
        $this->resetPage();
    }

    public function updatingFiltreType(): void
    {
        $this->resetPage();
    }

    public function updatingFiltreCategorie(): void
    {
        $this->resetPage();
    }

    public function updatingRecherche(): void
    {
        $this->resetPage();
    }

    public function updatedFkIdTypeDepense($value): void
    {
        $type = TypeDepense::query()->find($value);
        $estTransport = $type?->libelle === self::TYPE_TRANSPORT_LABEL;

        if ($estTransport) {
            $this->fkIdFournisseur = null;
        } else {
            $this->fkIdEmploye = null;
        }
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function nouvelleDepense(): void
    {
        $this->reset(['designation', 'montant', 'fkIdTypeDepense', 'fkIdFournisseur', 'fkIdEmploye', 'reference', 'notes', 'editId']);
        $this->dateDepense = now()->toDateString();
        $this->modePaiement = 'especes';
        $this->resetErrorBag();
        $this->resetValidation();
        $this->afficherForm = true;
    }

    public function editer(int $id): void
    {
        $d = Depense::query()->forCurrentSuccursale()->findOrFail($id);
        $this->editId = $d->id;
        $this->dateDepense = $d->date_depense?->toDateString() ?? now()->toDateString();
        $this->fkIdTypeDepense = $d->fk_id_type_depense;
        $this->designation = $d->designation;
        $this->montant = (string) $d->montant;
        $this->modePaiement = $d->mode_paiement;
        $this->fkIdFournisseur = $d->fk_id_fournisseur;
        $this->fkIdEmploye = $d->fk_id_employe;
        $this->reference = $d->reference ?? '';
        $this->notes = $d->notes ?? '';
        $this->resetErrorBag();
        $this->resetValidation();
        $this->afficherForm = true;
    }

    public function sauvegarder(): void
    {
        $codesModes = ModePaiement::actif()->pluck('code')->toArray();
        $typeIdsSaisie = TypeDepense::actif()
            ->where('libelle', '!=', self::TYPE_SALAIRES_LABEL)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->toArray();

        $this->validate([
            'dateDepense' => ['required', 'date'],
            'fkIdTypeDepense' => ['required', Rule::in($typeIdsSaisie)],
            'designation' => ['required', 'string', 'max:255'],
            'montant' => ['required', 'numeric', 'min:0.01'],
            'modePaiement' => ['required', Rule::in($codesModes)],
        ], [
            'fkIdTypeDepense.in' => 'نوع "الرواتب" مخصص للإدخالات التلقائية فقط.',
        ]);

        $typeSelectionne = TypeDepense::query()->find($this->fkIdTypeDepense);
        $estTransport = $typeSelectionne?->libelle === self::TYPE_TRANSPORT_LABEL;

        if ($estTransport) {
            $this->validate([
                'fkIdEmploye' => ['required', 'exists:employes,id'],
            ], [
                'fkIdEmploye.required' => 'يرجى اختيار الموظف لمصروف النقل.',
                'fkIdEmploye.exists' => 'الموظف المختار غير صالح.',
            ]);
            $this->fkIdFournisseur = null;
        } else {
            $this->validate([
                'fkIdFournisseur' => ['nullable', 'exists:fournisseurs,id'],
            ], [
                'fkIdFournisseur.exists' => 'المورد المختار غير صالح.',
            ]);
            $this->fkIdEmploye = null;
        }

        $data = [
            'fk_id_succursale' => SuccursaleContext::currentIdForWrite(),
            'date_depense' => now()->toDateString() === $this->dateDepense
                ? now()->toDateTimeString()
                : $this->dateDepense . ' ' . now()->format('H:i:s'),
            'fk_id_type_depense' => $this->fkIdTypeDepense,
            'designation' => $this->designation,
            'montant' => $this->montant,
            'mode_paiement' => $this->modePaiement,
            'fk_id_fournisseur' => $this->fkIdFournisseur,
            'fk_id_employe' => $this->fkIdEmploye,
            'reference' => $this->reference ?: null,
            'notes' => $this->notes ?: null,
            'statut' => 'validee',
            'fk_id_user' => auth()->id(),
        ];

        if ($this->editId) {
            Depense::query()->forCurrentSuccursale()->findOrFail($this->editId)->update($data);
            $this->dispatch('notify', type: 'success', message: 'تم تحديث المصروف.');
        } else {
            Depense::create($data);
            $this->dispatch('notify', type: 'success', message: 'تم حفظ المصروف.');
        }

        $this->fermerForm();
        $this->resetPage();
    }

    public function fermerForm(): void
    {
        $this->afficherForm = false;
        $this->reset(['designation', 'montant', 'fkIdTypeDepense', 'fkIdFournisseur', 'fkIdEmploye', 'reference', 'notes', 'editId']);
        $this->dateDepense = now()->toDateString();
        $this->modePaiement = 'especes';
        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function demanderAnnulation(int $id): void
    {
        $this->depenseAAnnulerId = $id;
    }

    public function annulerAnnulation(): void
    {
        $this->depenseAAnnulerId = null;
    }

    public function confirmerAnnulation(): void
    {
        if (!$this->depenseAAnnulerId) {
            return;
        }

        Depense::query()->forCurrentSuccursale()->findOrFail($this->depenseAAnnulerId)->update(['statut' => 'annulee']);
        $this->dispatch('notify', type: 'success', message: 'تم إلغاء المصروف.');
        $this->depenseAAnnulerId = null;
    }

    public function getTotalPeriodeProperty(): float
    {
        return (float) $this->buildQuery()->sum('montant');
    }

    private function buildQuery()
    {
        return Depense::query()
            ->forCurrentSuccursale()
            ->validee()
            ->when($this->filtrePeriode === 'jour', fn ($q) => $q->whereDate('date_depense', today()))
            ->when($this->filtrePeriode === 'semaine', fn ($q) => $q->whereBetween('date_depense', [now()->startOfWeek(), now()->endOfWeek()]))
            ->when($this->filtrePeriode === 'mois', fn ($q) => $q->whereMonth('date_depense', now()->month)->whereYear('date_depense', now()->year))
            ->when($this->filtreType, fn ($q) => $q->where('fk_id_type_depense', $this->filtreType))
            ->when($this->filtreCategorie === 'employes', function ($q) {
                $q->where(function ($query) {
                    $query->whereHas('typeDepense', fn ($typeQuery) => $typeQuery->where('libelle', self::TYPE_SALAIRES_LABEL))
                        ->orWhere('reference', 'like', 'AVANCE-%')
                        ->orWhere('reference', 'like', 'PAIE-%');
                });
            })
            ->when($this->filtreCategorie === 'ordinaires', function ($q) {
                $q->where(function ($query) {
                    $query->whereDoesntHave('typeDepense', fn ($typeQuery) => $typeQuery->where('libelle', self::TYPE_SALAIRES_LABEL))
                        ->where(function ($refQuery) {
                            $refQuery->whereNull('reference')
                                ->orWhere(function ($notEmpRefQuery) {
                                    $notEmpRefQuery->where('reference', 'not like', 'AVANCE-%')
                                        ->where('reference', 'not like', 'PAIE-%');
                                });
                        });
                });
            })
            ->when($this->recherche, fn ($q) => $q->where('designation', 'like', "%{$this->recherche}%"))
            ->orderBy($this->sortField, $this->sortDirection);
    }

    private function ensureTypeSalairesActif(): void
    {
        TypeDepense::updateOrCreate(
            ['libelle' => self::TYPE_SALAIRES_LABEL],
            ['icone' => '👥', 'couleur' => '#10B981', 'actif' => true, 'ordre' => 1]
        );
    }

    public function render()
    {
        $types = TypeDepense::actif()->get();

        return view('livewire.depenses.depense-index', [
            'depenses' => $this->buildQuery()->with(['typeDepense', 'fournisseur', 'employe'])->paginate(20),
            'types' => $types,
            'typesSaisie' => $types->filter(fn (TypeDepense $type): bool => $type->libelle !== self::TYPE_SALAIRES_LABEL)->values(),
            'fournisseurs' => Fournisseur::actif()->get(),
            'employes' => Employe::actif()->get(),
            'modes' => ModePaiement::actif()->get(),
            'totalPeriode' => $this->total_periode,
        ])->layout('layouts.app');
    }
}
