<?php

namespace App\Livewire\Depenses;

use App\Models\Depense;
use App\Models\Employe;
use App\Models\Fournisseur;
use App\Models\ModePaiement;
use App\Models\Pret;
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
    private const TYPE_SALAIRES_LABEL  = 'الرواتب';
    private const TYPE_TRANSPORT_LABEL = 'النقل';
    private const TYPE_PRET_LABEL      = 'تسديد قرض';

    // Onglet actif
    public string $onglet = 'depenses'; // depenses | prets

    // Formulaire prêt
    public bool $afficherFormPret = false;
    public ?int  $pretEditId      = null;
    public string $pretDatePret   = '';
    public string $pretPreteur    = '';
    public string $pretMontant    = '';
    public string $pretMode       = 'especes';
    public string $pretNotes      = '';

    // Remboursement
    public bool  $afficherFormRemboursement = false;
    public ?int  $pretARembourserId         = null;
    public string $rembDate                 = '';
    public string $rembMontant              = '';
    public string $rembMode                 = 'especes';
    public string $rembNotes                = '';

    public function mount(): void
    {
        $this->dateDepense  = now()->toDateString();
        $this->pretDatePret = now()->toDateString();
        $this->rembDate     = now()->toDateString();
        $this->ensureTypeSalairesActif();
        $this->ensureTypePretActif();
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

    private function ensureTypePretActif(): void
    {
        TypeDepense::updateOrCreate(
            ['libelle' => self::TYPE_PRET_LABEL],
            ['icone' => '🏦', 'couleur' => '#6366F1', 'actif' => true, 'ordre' => 99]
        );
    }

    /* ─── Prêts ──────────────────────────────────────────────────── */

    public function nouveauPret(): void
    {
        $this->reset(['pretEditId', 'pretPreteur', 'pretMontant', 'pretNotes']);
        $this->pretDatePret = now()->toDateString();
        $this->pretMode     = 'especes';
        $this->resetErrorBag();
        $this->afficherFormPret = true;
    }

    public function editerPret(int $id): void
    {
        $pret = Pret::query()->forCurrentSuccursale()->findOrFail($id);
        $this->pretEditId   = $pret->id;
        $this->pretDatePret = $pret->date_pret->toDateString();
        $this->pretPreteur  = $pret->preteur;
        $this->pretMontant  = (string) $pret->montant;
        $this->pretMode     = $pret->mode_paiement;
        $this->pretNotes    = $pret->notes ?? '';
        $this->resetErrorBag();
        $this->afficherFormPret = true;
    }

    public function sauvegarderPret(): void
    {
        $codesModes = ModePaiement::actif()->pluck('code')->toArray();

        $this->validate([
            'pretDatePret' => ['required', 'date'],
            'pretPreteur'  => ['required', 'string', 'max:255'],
            'pretMontant'  => ['required', 'numeric', 'min:0.01'],
            'pretMode'     => ['required', Rule::in($codesModes)],
        ], [
            'pretPreteur.required' => 'يرجى إدخال اسم المُقرض.',
            'pretMontant.required' => 'يرجى إدخال مبلغ القرض.',
            'pretMode.required'    => 'يرجى اختيار طريقة الاستلام.',
        ]);

        $data = [
            'fk_id_succursale' => SuccursaleContext::currentIdForWrite(),
            'date_pret'        => $this->pretDatePret,
            'preteur'          => $this->pretPreteur,
            'montant'          => $this->pretMontant,
            'mode_paiement'    => $this->pretMode,
            'notes'            => $this->pretNotes ?: null,
            'fk_id_user'       => auth()->id(),
        ];

        if ($this->pretEditId) {
            $pret = Pret::query()->forCurrentSuccursale()->findOrFail($this->pretEditId);
            $pret->update($data);
            $pret->recalculerMontantRembourse();
            $this->dispatch('notify', type: 'success', message: 'تم تحديث القرض.');
        } else {
            Pret::create($data);
            $this->dispatch('notify', type: 'success', message: 'تم تسجيل القرض.');
        }

        $this->fermerFormPret();
    }

    public function fermerFormPret(): void
    {
        $this->afficherFormPret = false;
        $this->reset(['pretEditId', 'pretPreteur', 'pretMontant', 'pretNotes']);
        $this->pretDatePret = now()->toDateString();
        $this->pretMode     = 'especes';
        $this->resetErrorBag();
    }

    public function ouvrirRemboursement(int $pretId): void
    {
        $this->pretARembourserId = $pretId;
        $this->rembDate          = now()->toDateString();
        $this->rembMontant       = '';
        $this->rembMode          = 'especes';
        $this->rembNotes         = '';
        $this->resetErrorBag();
        $this->afficherFormRemboursement = true;
    }

    public function fermerRemboursement(): void
    {
        $this->afficherFormRemboursement = false;
        $this->pretARembourserId         = null;
        $this->reset(['rembMontant', 'rembNotes']);
        $this->rembDate = now()->toDateString();
        $this->rembMode = 'especes';
        $this->resetErrorBag();
    }

    public function sauvegarderRemboursement(): void
    {
        $codesModes = ModePaiement::actif()->pluck('code')->toArray();
        $pret       = Pret::query()->forCurrentSuccursale()->findOrFail($this->pretARembourserId);

        $this->validate([
            'rembDate'    => ['required', 'date'],
            'rembMontant' => ['required', 'numeric', 'min:0.01', 'max:' . $pret->solde_restant],
            'rembMode'    => ['required', Rule::in($codesModes)],
        ], [
            'rembMontant.required' => 'يرجى إدخال مبلغ التسديد.',
            'rembMontant.max'      => 'المبلغ يتجاوز الرصيد المتبقي (' . number_format($pret->solde_restant, 2) . ' MRU).',
        ]);

        $typePret = TypeDepense::where('libelle', self::TYPE_PRET_LABEL)->first();

        Depense::create([
            'fk_id_succursale'  => SuccursaleContext::currentIdForWrite(),
            'date_depense'      => now()->toDateString() === $this->rembDate
                ? now()->toDateTimeString()
                : $this->rembDate . ' ' . now()->format('H:i:s'),
            'fk_id_type_depense' => $typePret?->id,
            'designation'       => 'تسديد قرض - ' . $pret->preteur,
            'montant'           => $this->rembMontant,
            'mode_paiement'     => $this->rembMode,
            'reference'         => 'PRET-' . $pret->id,
            'notes'             => $this->rembNotes ?: null,
            'statut'            => 'validee',
            'fk_id_user'        => auth()->id(),
        ]);

        $pret->recalculerMontantRembourse();

        $this->dispatch('notify', type: 'success', message: 'تم تسجيل التسديد.');
        $this->fermerRemboursement();
    }

    public function getPretsTotauxProperty(): array
    {
        $prets = Pret::query()->forCurrentSuccursale()->get();
        return [
            'total_emprunte'  => round((float) $prets->sum('montant'), 2),
            'total_rembourse' => round((float) $prets->sum('montant_rembourse'), 2),
            'solde_restant'   => round((float) $prets->sum(fn ($p) => $p->solde_restant), 2),
        ];
    }

    public function render()
    {
        $types = TypeDepense::actif()->get();

        $prets = Pret::query()
            ->forCurrentSuccursale()
            ->orderByDesc('date_pret')
            ->with(['user'])
            ->paginate(15, ['*'], 'pretsPage');

        return view('livewire.depenses.depense-index', [
            'depenses'     => $this->buildQuery()->with(['typeDepense', 'fournisseur', 'employe'])->paginate(20),
            'types'        => $types,
            'typesSaisie'  => $types->filter(fn (TypeDepense $type): bool =>
                $type->libelle !== self::TYPE_SALAIRES_LABEL &&
                $type->libelle !== self::TYPE_PRET_LABEL
            )->values(),
            'fournisseurs' => Fournisseur::actif()->get(),
            'employes'     => Employe::actif()->get(),
            'modes'        => ModePaiement::actif()->get(),
            'totalPeriode' => $this->total_periode,
            'prets'        => $prets,
            'pretsTotaux'  => $this->prets_totaux,
        ])->layout('layouts.app');
    }
}
