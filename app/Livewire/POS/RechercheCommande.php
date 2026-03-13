<?php

namespace App\Livewire\POS;

use App\Models\CaisseOperation;
use App\Models\Commande;
use App\Models\ModePaiement;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class RechercheCommande extends Component
{
    use WithPagination;

    public string $recherche = '';
    public string $filtreStatut = '';
    public string $dateDebut = '';
    public string $dateFin = '';
    public array $selectionCommandes = [];
    public bool $selectionPage = false;
    public string $statutGroupe = '';
    public ?int $commandeSelectionneeId = null;
    public ?Commande $commande = null;

    public float $montantAPayer = 0;
    public string $remisePourcentage = '0';
    public string $modeReglement = 'especes';
    public bool $afficherPaiement = false;

    public string $messageSucces = '';
    public string $messageErreur = '';

    public function mount(): void
    {
        if (Commande::query()->forCurrentSuccursale()->where('statut', 'en_cours')->exists()) {
            $this->filtreStatut = 'en_cours';
        }
    }

    public function rechercherCommande(): void
    {
        $this->resetPage();
    }

    public function updatedRecherche(): void
    {
        $this->resetSelectionGroupe();
        $this->resetPage();
    }

    public function updatedFiltreStatut(): void
    {
        $this->resetSelectionGroupe();
        $this->resetPage();
    }

    public function updatedDateDebut(): void
    {
        $this->resetSelectionGroupe();
        $this->resetPage();
    }

    public function updatedDateFin(): void
    {
        $this->resetSelectionGroupe();
        $this->resetPage();
    }

    public function reinitialiserFiltres(): void
    {
        $this->recherche = '';
        $this->filtreStatut = '';
        $this->dateDebut = '';
        $this->dateFin = '';
        $this->resetSelectionGroupe();
        $this->resetPage();
    }

    public function updatedSelectionPage(bool $value): void
    {
        if (!$value) {
            $this->selectionCommandes = [];
            return;
        }

        $page = $this->getPage();
        $idsPage = $this->buildQuery()
            ->forPage($page, 15)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->toArray();

        $this->selectionCommandes = $idsPage;
    }

    public function selectionnerCommande(int $id): void
    {
        $this->commande = Commande::query()->forCurrentSuccursale()->with(['client', 'details.service'])->findOrFail($id);
        $this->commandeSelectionneeId = $id;
        $this->montantAPayer = (float) $this->commande->reste_a_payer;
        $this->messageErreur = '';
        $this->messageSucces = '';
    }

    public function ouvrirPaiement(): void
    {
        if (!$this->commande || (float) $this->commande->reste_a_payer <= 0) {
            return;
        }

        $codesModes = $this->getCodesModesPaiement();

        $this->montantAPayer = (float) $this->commande->reste_a_payer;
        $this->remisePourcentage = '0';
        $this->modeReglement = in_array($this->modeReglement, $codesModes, true)
            ? $this->modeReglement
            : $codesModes[0];
        $this->afficherPaiement = true;
    }

    public function getRemiseMontantProperty(): float
    {
        if (!$this->commande) {
            return 0;
        }

        $resteInitial = (float) $this->commande->reste_a_payer;
        $pourcentage = max(0, min(100, (float) $this->remisePourcentage));

        return round($resteInitial * ($pourcentage / 100), 2);
    }

    public function getResteApresRemiseProperty(): float
    {
        if (!$this->commande) {
            return 0;
        }

        return max(0, round((float) $this->commande->reste_a_payer - $this->remise_montant, 2));
    }

    public function encaisserReste(): void
    {
        if (!$this->commande) {
            return;
        }

        $codesModes = $this->getCodesModesPaiement();

        $this->validate([
            'montantAPayer' => ['required', 'numeric', 'min:0'],
            'remisePourcentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'modeReglement' => ['required', Rule::in($codesModes)],
        ]);

        $resteInitial = (float) $this->commande->reste_a_payer;
        $remiseMontant = round($resteInitial * ((float) $this->remisePourcentage / 100), 2);
        $resteApresRemise = max(0, round($resteInitial - $remiseMontant, 2));

        if ($this->montantAPayer > $resteApresRemise) {
            $this->addError('montantAPayer', 'Le montant depasse le reste apres remise.');
            return;
        }

        if ($this->montantAPayer <= 0 && $remiseMontant <= 0) {
            $this->addError('montantAPayer', 'Veuillez saisir un paiement ou une remise.');
            return;
        }

        $nouveauMontantPaye = round((float) $this->commande->montant_paye + $this->montantAPayer, 2);
        $nouvelleRemiseReglement = round((float) $this->commande->remise_reglement_montant + $remiseMontant, 2);
        $totalRemise = round((float) $this->commande->remise_depot_montant + $nouvelleRemiseReglement, 2);
        $resteAPayer = max(0, round($this->commande->montant_total - $nouveauMontantPaye - $remiseMontant, 2));

        DB::transaction(function () use ($nouveauMontantPaye, $resteAPayer, $nouvelleRemiseReglement, $totalRemise): void {
            $this->commande->update([
                'montant_paye' => $nouveauMontantPaye,
                'reste_a_payer' => $resteAPayer,
                'remise_reglement_montant' => $nouvelleRemiseReglement,
                'total_remise' => $totalRemise,
                'est_paye' => $resteAPayer <= 0,
                'mode_reglement' => $this->modeReglement,
            ]);

            if ($this->montantAPayer > 0) {
                CaisseOperation::create([
                    'fk_id_succursale' => $this->commande->fk_id_succursale,
                    'date_operation' => now(),
                    'montant_operation' => $this->montantAPayer,
                    'designation' => 'Paiement reste commande ' . $this->commande->numero_commande,
                    'fk_id_client' => $this->commande->fk_id_client,
                    'entree_espece' => $this->modeReglement === 'especes' ? $this->montantAPayer : 0,
                    'retrait_espece' => 0,
                    'fk_id_commande' => $this->commande->id,
                    'fk_id_user' => auth()->id(),
                    'mode_paiement' => $this->modeReglement,
                ]);
            }
        });

        $this->commande->refresh();
        $this->afficherPaiement = false;
        $this->messageSucces = 'Paiement enregistre avec succes.';
        $this->dispatch('notify', type: 'success', message: $this->messageSucces);
    }

    public function changerStatut(int $commandeId, string $nouveauStatut): void
    {
        $commande = Commande::query()->forCurrentSuccursale()->findOrFail($commandeId);
        $transitionsAutorisees = $this->getTransitionsAutorisees();

        if (!in_array($nouveauStatut, $transitionsAutorisees[$commande->statut] ?? [], true)) {
            $this->messageErreur = 'Transition de statut invalide.';
            $this->dispatch('notify', type: 'error', message: $this->messageErreur);
            return;
        }

        $commande->update([
            'statut' => $nouveauStatut,
            'date_livraison_reelle' => $nouveauStatut === 'livre' ? now() : $commande->date_livraison_reelle,
        ]);

        $this->messageSucces = 'Statut mis a jour.';
        $this->dispatch('notify', type: 'success', message: $this->messageSucces);
        if ($this->commandeSelectionneeId === $commande->id) {
            $this->selectionnerCommande($commande->id);
        }
    }

    public function confirmerChangementStatut(int $commandeId, string $nouveauStatut): void
    {
        $this->dispatch('confirm-statuts', commandeId: $commandeId, statut: $nouveauStatut);
    }

    public function appliquerChangementStatutGroupe(): void
    {
        $ids = collect($this->selectionCommandes)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            $this->messageErreur = 'حدد طلبًا واحدًا على الأقل.';
            $this->dispatch('notify', type: 'error', message: $this->messageErreur);
            return;
        }

        $this->validate([
            'statutGroupe' => ['required', 'in:pret'],
        ]);

        $transitionsAutorisees = $this->getTransitionsAutorisees();
        $commandes = Commande::query()->forCurrentSuccursale()->whereIn('id', $ids)->get();

        $updated = 0;
        $ignored = 0;

        DB::transaction(function () use ($commandes, $transitionsAutorisees, &$updated, &$ignored): void {
            foreach ($commandes as $commande) {
                // Regle metier: en changement groupe, on autorise uniquement en_cours -> pret.
                if ($commande->statut !== 'en_cours' || $this->statutGroupe !== 'pret') {
                    $ignored++;
                    continue;
                }

                if (!in_array($this->statutGroupe, $transitionsAutorisees[$commande->statut] ?? [], true)) {
                    $ignored++;
                    continue;
                }

                $commande->update([
                    'statut' => $this->statutGroupe,
                    'date_livraison_reelle' => $this->statutGroupe === 'livre' ? now() : $commande->date_livraison_reelle,
                ]);
                $updated++;
            }
        });

        if ($updated === 0) {
            $this->messageErreur = 'لم يتم تحديث أي طلب (انتقالات غير صالحة).';
            $this->dispatch('notify', type: 'error', message: $this->messageErreur);
        } else {
            $this->messageSucces = "تم تحديث {$updated} طلب.";
            if ($ignored > 0) {
                $this->messageSucces .= " تم تجاهل {$ignored} طلب بسبب انتقال غير صالح.";
            }
            $this->dispatch('notify', type: 'success', message: $this->messageSucces);
        }

        if ($this->commandeSelectionneeId && $ids->contains($this->commandeSelectionneeId)) {
            $this->selectionnerCommande($this->commandeSelectionneeId);
        }

        $this->resetSelectionGroupe();
    }

    public function render()
    {
        $resultats = $this->buildQuery()->paginate(15);
        $modesPaiement = ModePaiement::actif()->get();

        return view('livewire.pos.recherche-commande', [
            'resultats' => $resultats,
            'commande' => $this->commande,
            'modesPaiement' => $modesPaiement,
        ])
            ->layout('layouts.app');
    }

    private function getCodesModesPaiement(): array
    {
        $codes = ModePaiement::actif()->pluck('code')->filter()->values()->toArray();

        // Fallback defensif pour ne pas bloquer la modale si la table est vide.
        return $codes !== [] ? $codes : ['especes', 'carte', 'virement'];
    }

    private function buildQuery()
    {
        $terme = trim($this->recherche);

        return Commande::query()
            ->forCurrentSuccursale()
            ->with('client')
            ->when($this->filtreStatut !== '', fn ($query) => $query->where('statut', $this->filtreStatut))
            ->when($this->dateDebut !== '', fn ($query) => $query->whereDate('date_depot', '>=', $this->dateDebut))
            ->when($this->dateFin !== '', fn ($query) => $query->whereDate('date_depot', '<=', $this->dateFin))
            ->when($terme !== '', function ($query) use ($terme): void {
                $query->where(function ($sub) use ($terme): void {
                    $sub->where('numero_commande', 'like', "%{$terme}%")
                        ->orWhereHas('client', fn ($q) => $q
                            ->where('telephone', 'like', "%{$terme}%")
                            ->orWhere('nom', 'like', "%{$terme}%")
                            ->orWhere('prenom', 'like', "%{$terme}%"));
                });
            })
            ->latest();
    }

    private function resetSelectionGroupe(): void
    {
        $this->selectionPage = false;
        $this->selectionCommandes = [];
        $this->statutGroupe = '';
    }

    private function getTransitionsAutorisees(): array
    {
        return [
            'en_cours' => ['pret'],
            'pret' => ['livre'],
            'livre' => [],
        ];
    }
}
