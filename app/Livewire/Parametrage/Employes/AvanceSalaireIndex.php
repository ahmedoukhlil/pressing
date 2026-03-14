<?php

namespace App\Livewire\Parametrage\Employes;

use App\Models\AvanceSalaire;
use App\Models\Depense;
use App\Models\Employe;
use App\Models\ModePaiement;
use App\Models\TypeDepense;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;

class AvanceSalaireIndex extends Component
{
    private const TYPE_SALAIRES_LABEL = 'الرواتب';

    public int $employeId;
    public Employe $employe;
    public bool $vuePaiementSalaire = false;

    public bool $afficherForm = false;
    public string $dateAvance = '';
    public string $montant = '';
    public string $motif = '';
    public string $notes = '';
    public string $messageSucces = '';
    public string $messageErreur = '';
    public bool $afficherPaiementSalaire = false;
    public string $datePaiementSalaire = '';
    public string $modePaiementSalaire = 'especes';
    public string $notesPaiementSalaire = '';

    public function mount(int $employeId): void
    {
        $this->employe = Employe::findOrFail($employeId);
        $this->employeId = $employeId;
        $this->dateAvance = now()->toDateString();
        $this->datePaiementSalaire = now()->toDateString();
        $this->modePaiementSalaire = ModePaiement::actif()->value('code') ?? 'especes';
        $this->vuePaiementSalaire = request()->routeIs('parametrage.employes.paiement') || request()->boolean('paiement');

        if ($this->vuePaiementSalaire) {
            $this->ouvrirPaiementSalaire();
        }
    }

    public function ouvrirFormAvance(): void
    {
        $this->reset(['montant', 'motif', 'notes']);
        $this->dateAvance = now()->toDateString();
        $this->afficherPaiementSalaire = false;
        $this->afficherForm = true;
    }

    public function ouvrirPaiementSalaire(): void
    {
        $this->reset(['notesPaiementSalaire']);
        $this->datePaiementSalaire = now()->toDateString();
        $this->modePaiementSalaire = ModePaiement::actif()->value('code') ?? 'especes';
        $this->afficherForm = false;
        $this->resetErrorBag();
        $this->resetValidation();
        $this->afficherPaiementSalaire = true;
    }

    public function enregistrerAvance(): void
    {
        $this->validate([
            'dateAvance' => ['required', 'date'],
            'montant' => ['required', 'numeric', 'min:1', 'max:' . $this->employe->salaire_brut],
            'motif' => ['nullable', 'string', 'max:255'],
        ]);

        $totalEnCours = (float) $this->employe->total_avances_en_cours;
        if ($totalEnCours + (float) $this->montant > (float) $this->employe->salaire_brut) {
            $this->addError('montant', 'Le total des avances depasse le salaire brut.');
            return;
        }

        DB::transaction(function (): void {
            $typeSalaire = $this->resolveTypeSalaire();

            $avance = AvanceSalaire::create([
                'fk_id_employe' => $this->employeId,
                'date_avance' => $this->dateAvance,
                'montant' => $this->montant,
                'motif' => $this->motif ?: null,
                'statut' => 'en_cours',
                'fk_id_user' => auth()->id(),
                'notes' => $this->notes ?: null,
            ]);

            $depense = Depense::create([
                'date_depense' => $this->dateAvance,
                'fk_id_type_depense' => $typeSalaire->id,
                'designation' => 'Avance salaire - ' . $this->employe->full_name,
                'montant' => $this->montant,
                'mode_paiement' => 'especes',
                'fk_id_employe' => $this->employeId,
                'reference' => 'AVANCE-' . $avance->id,
                'statut' => 'validee',
                'notes' => $this->notes ?: null,
                'fk_id_user' => auth()->id(),
            ]);

            $avance->update(['fk_id_depense' => $depense->id]);
        });

        $this->employe->refresh();
        $this->afficherForm = false;
        $this->messageSucces = 'تم تسجيل السلفة واحتسابها كمصروف من نوع الرواتب.';
        $this->dispatch('notify', type: 'success', message: $this->messageSucces);
    }

    public function deduireAvance(int $avanceId): void
    {
        $avance = AvanceSalaire::findOrFail($avanceId);

        if ($avance->statut !== 'en_cours') {
            $this->messageErreur = 'Cette avance n\'est plus en cours.';
            $this->dispatch('notify', type: 'error', message: $this->messageErreur);
            return;
        }

        $salaireNet = max(0, (float) $this->employe->salaire_brut - (float) $avance->montant);
        $avance->update([
            'statut' => 'deduite',
            'date_deduction' => now()->toDateString(),
            'salaire_net_verse' => $salaireNet,
        ]);

        $this->employe->refresh();
        $this->messageSucces = 'Avance deduite. Pas de nouvelle depense creee.';
        $this->dispatch('notify', type: 'success', message: $this->messageSucces);
    }

    public function annulerAvance(int $avanceId): void
    {
        $avance = AvanceSalaire::findOrFail($avanceId);
        if ($avance->statut !== 'en_cours') {
            $this->messageErreur = 'Seules les avances en cours peuvent etre annulees.';
            $this->dispatch('notify', type: 'error', message: $this->messageErreur);
            return;
        }

        DB::transaction(function () use ($avance): void {
            $avance->update(['statut' => 'annulee']);
            if ($avance->fk_id_depense) {
                Depense::where('id', $avance->fk_id_depense)->update(['statut' => 'annulee']);
            }
        });

        $this->employe->refresh();
        $this->messageSucces = 'Avance et depense liee annulees.';
        $this->dispatch('notify', type: 'success', message: $this->messageSucces);
    }

    public function payerSalaire(): void
    {
        $codesModes = ModePaiement::actif()->pluck('code')->toArray();
        if ($codesModes === []) {
            $this->addError('modePaiementSalaire', 'Aucun mode de paiement actif n\'est configure.');
            return;
        }

        $this->validate([
            'datePaiementSalaire' => ['required', 'date'],
            'modePaiementSalaire' => ['required', Rule::in($codesModes)],
            'notesPaiementSalaire' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->employe->refresh();
        $salaireBrut = (float) $this->employe->salaire_brut;
        $totalAvances = (float) $this->employe->total_avances_en_cours;
        $salaireNet = max(0, $salaireBrut - $totalAvances);

        DB::transaction(function () use ($salaireBrut, $totalAvances, $salaireNet): void {
            $typeSalaire = $this->resolveTypeSalaire();

            if ($salaireNet > 0) {
                Depense::create([
                    'date_depense' => $this->datePaiementSalaire,
                    'fk_id_type_depense' => $typeSalaire->id,
                    'designation' => 'Paiement salaire - ' . $this->employe->full_name,
                    'montant' => $salaireNet,
                    'mode_paiement' => $this->modePaiementSalaire,
                    'fk_id_employe' => $this->employeId,
                    'reference' => 'PAIE-' . $this->employe->id . '-' . now()->format('YmdHis'),
                    'statut' => 'validee',
                    'notes' => trim(
                        "Situation de paie\n"
                        . 'Salaire brut: ' . number_format($salaireBrut, 2, '.', '') . " MRU\n"
                        . 'Total avances deduites: ' . number_format($totalAvances, 2, '.', '') . " MRU\n"
                        . 'Salaire net verse: ' . number_format($salaireNet, 2, '.', '') . " MRU\n"
                        . ($this->notesPaiementSalaire !== '' ? "Notes: {$this->notesPaiementSalaire}" : '')
                    ),
                    'fk_id_user' => auth()->id(),
                ]);
            }

            AvanceSalaire::query()
                ->where('fk_id_employe', $this->employeId)
                ->where('statut', 'en_cours')
                ->update([
                    'statut' => 'deduite',
                    'date_deduction' => $this->datePaiementSalaire,
                    'salaire_net_verse' => $salaireNet,
                ]);
        });

        $this->employe->refresh();
        $this->afficherPaiementSalaire = false;
        $this->notesPaiementSalaire = '';

        $message = $salaireNet > 0
            ? 'Salaire paye avec deductions des avances. Situation de paie enregistree.'
            : 'Aucun net a payer: toutes les avances ont ete deduites.';

        $this->messageSucces = $message;
        $this->dispatch('notify', type: 'success', message: $message);
    }

    public function render()
    {
        return view('livewire.parametrage.employes.avance-salaire-index', [
            'employe' => $this->employe,
            'avances' => AvanceSalaire::query()
                ->where('fk_id_employe', $this->employeId)
                ->latest('date_avance')
                ->get(),
            'modesPaiement' => ModePaiement::actif()->get(),
        ])->layout('layouts.app');
    }

    private function resolveTypeSalaire(): TypeDepense
    {
        $type = TypeDepense::query()
            ->where('libelle', self::TYPE_SALAIRES_LABEL)
            ->first();

        if ($type) {
            $type->update([
                'libelle' => self::TYPE_SALAIRES_LABEL,
                'icone' => '👥',
                'couleur' => '#10B981',
                'actif' => true,
                'ordre' => 1,
            ]);

            return $type->fresh();
        }

        return TypeDepense::create([
            'libelle' => self::TYPE_SALAIRES_LABEL,
            'icone' => '👥',
            'couleur' => '#10B981',
            'actif' => true,
            'ordre' => 1,
        ]);
    }
}
