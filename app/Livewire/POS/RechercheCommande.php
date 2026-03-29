<?php

namespace App\Livewire\POS;

use App\Models\CaisseOperation;
use App\Models\Commande;
use App\Models\DetailCommande;
use App\Models\ModePaiement;
use App\Support\LoyaltyPointsService;
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
    public bool $afficherFiltresAvances = false;
    public array $selectionCommandes = [];
    public bool $selectionPage = false;
    public string $statutGroupe = '';
    public ?int $commandeSelectionneeId = null;
    public ?Commande $commande = null;

    public float $montantAPayer = 0;
    public string $remisePourcentage = '0';
    public string $modeReglement = 'especes';
    public bool $afficherPaiement = false;
    public bool $afficherConfirmationStatut = false;
    public ?int $commandeAConfirmerId = null;
    public string $statutAConfirmer = '';
    public bool $afficherConfirmationSuppression = false;
    public ?int $commandeASupprimerId = null;
    public string $numeroCommandeASupprimer = '';
    public bool $afficherRappelsModal = false;

    public string $messageSucces = '';
    public string $messageErreur = '';

    /** @var array<int, string> quantité à remettre au client par ligne (detail id) */
    public array $remisePartielle = [];

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
        $this->afficherFiltresAvances = false;
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
        $this->initialiserRemisePartielle();
    }

    private function initialiserRemisePartielle(): void
    {
        $this->remisePartielle = [];
        if (!$this->commande) {
            return;
        }
        foreach ($this->commande->details as $d) {
            $restant = max(0, (int) $d->quantite - (int) $d->quantite_rendue);
            $this->remisePartielle[(int) $d->id] = $restant > 0 ? (string) $restant : '0';
        }
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
            $this->addError('montantAPayer', 'المبلغ يتجاوز المتبقي بعد الخصم.');
            return;
        }

        if ($this->montantAPayer <= 0 && $remiseMontant <= 0) {
            $this->addError('montantAPayer', 'يرجى إدخال مبلغ أو خصم.');
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
                $operation = CaisseOperation::create([
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

                LoyaltyPointsService::creditFromPayment(
                    (int) $this->commande->fk_id_succursale,
                    (int) $this->commande->fk_id_client,
                    (int) $this->commande->id,
                    (int) $operation->id,
                    (float) $this->montantAPayer,
                    auth()->id(),
                );
            }
        });

        $this->commande->refresh();
        $this->afficherPaiement = false;
        $this->messageSucces = 'تم تسجيل الدفع بنجاح.';
        $this->dispatch('notify', type: 'success', message: $this->messageSucces);
    }

    public function changerStatut(int $commandeId, string $nouveauStatut): void
    {
        $commande = Commande::query()->forCurrentSuccursale()->with('details')->findOrFail($commandeId);
        $transitionsAutorisees = $this->getTransitionsAutorisees();

        if (!in_array($nouveauStatut, $transitionsAutorisees[$commande->statut] ?? [], true)) {
            $this->messageErreur = 'الانتقال بين الحالات غير صالح.';
            $this->dispatch('notify', type: 'error', message: $this->messageErreur);
            return;
        }

        if ($nouveauStatut === 'livre') {
            $incomplet = $commande->details->contains(
                fn (DetailCommande $d) => (int) $d->quantite_rendue < (int) $d->quantite
            );
            if ($incomplet) {
                $this->messageErreur = 'لا يمكن إغلاق الطلب: سجّل تسليم كل القطع أولاً (أو أكمل الكميات المتبقية).';
                $this->dispatch('notify', type: 'error', message: $this->messageErreur);
                return;
            }
        }

        DB::transaction(function () use ($commande, $nouveauStatut): void {
            if ($nouveauStatut === 'pret') {
                $commande->details()->where('statut_ligne', 'en_cours')->update(['statut_ligne' => 'pret']);
            }

            $commande->update([
                'statut' => $nouveauStatut,
                'date_livraison_reelle' => $nouveauStatut === 'livre' ? now() : $commande->date_livraison_reelle,
            ]);
        });

        $commande->refresh();
        $commande->synchroniserStatutAvecLignes();

        $this->messageSucces = 'تم تحديث حالة الطلب.';
        $this->dispatch('notify', type: 'success', message: $this->messageSucces);
        if ($this->commandeSelectionneeId === $commande->id) {
            $this->selectionnerCommande($commande->id);
        }
    }

    public function marquerLignePret(int $detailId): void
    {
        $detail = DetailCommande::query()
            ->whereKey($detailId)
            ->with('commande')
            ->firstOrFail();

        $commande = $detail->commande;
        if (!Commande::query()->forCurrentSuccursale()->whereKey($commande->id)->exists()) {
            abort(403);
        }

        if ((int) $detail->quantite_rendue >= (int) $detail->quantite) {
            return;
        }

        $detail->update(['statut_ligne' => 'pret']);
        $commande->refresh();
        $commande->load('details');
        $commande->synchroniserStatutAvecLignes();

        $this->messageSucces = 'تم تحديث حالة القطعة.';
        $this->dispatch('notify', type: 'success', message: $this->messageSucces);

        if ($this->commandeSelectionneeId === $commande->id) {
            $this->selectionnerCommande($commande->id);
        }
    }

    public function enregistrerRemiseLigne(int $detailId): void
    {
        $detail = DetailCommande::query()
            ->whereKey($detailId)
            ->with('commande')
            ->firstOrFail();

        $commande = $detail->commande;
        if (!Commande::query()->forCurrentSuccursale()->whereKey($commande->id)->exists()) {
            abort(403);
        }

        if ($detail->statut_ligne === 'en_cours') {
            $this->messageErreur = 'القطعة ليست جاهزة بعد. عيّنها كـ «جاهزة» أولاً.';
            $this->dispatch('notify', type: 'error', message: $this->messageErreur);
            return;
        }

        $qty = (int) ($this->remisePartielle[$detailId] ?? 0);
        $restant = max(0, (int) $detail->quantite - (int) $detail->quantite_rendue);

        if ($qty < 1 || $qty > $restant) {
            $this->messageErreur = 'كمية غير صالحة (المتبقي: ' . $restant . ').';
            $this->dispatch('notify', type: 'error', message: $this->messageErreur);
            return;
        }

        $nouveauRendu = (int) $detail->quantite_rendue + $qty;
        $detail->quantite_rendue = min((int) $detail->quantite, $nouveauRendu);

        if ((int) $detail->quantite_rendue >= (int) $detail->quantite) {
            $detail->statut_ligne = 'livre';
        }

        $detail->save();

        $commande->refresh();
        $commande->load('details');
        $commande->synchroniserStatutAvecLignes();

        $this->messageSucces = 'تم تسجيل التسليم.';
        $this->dispatch('notify', type: 'success', message: $this->messageSucces);

        if ($this->commandeSelectionneeId === $commande->id) {
            $this->selectionnerCommande($commande->id);
        }
    }

    public function confirmerChangementStatut(int $commandeId, string $nouveauStatut): void
    {
        $this->commandeAConfirmerId = $commandeId;
        $this->statutAConfirmer = $nouveauStatut;
        $this->afficherConfirmationStatut = true;
    }

    public function validerChangementStatut(): void
    {
        if (!$this->commandeAConfirmerId || $this->statutAConfirmer === '') {
            $this->afficherConfirmationStatut = false;
            return;
        }

        $this->changerStatut($this->commandeAConfirmerId, $this->statutAConfirmer);
        $this->annulerConfirmationStatut();
    }

    public function annulerConfirmationStatut(): void
    {
        $this->afficherConfirmationStatut = false;
        $this->commandeAConfirmerId = null;
        $this->statutAConfirmer = '';
    }

    public function demanderSuppressionCommande(int $commandeId): void
    {
        if (!auth()->user()?->hasAnyRole(['gerant', 'المسير'])) {
            abort(403);
        }

        $commande = Commande::query()
            ->forCurrentSuccursale()
            ->findOrFail($commandeId);

        $this->commandeASupprimerId = $commande->id;
        $this->numeroCommandeASupprimer = $commande->numero_commande;
        $this->afficherConfirmationSuppression = true;
    }

    public function annulerSuppressionCommande(): void
    {
        $this->afficherConfirmationSuppression = false;
        $this->commandeASupprimerId = null;
        $this->numeroCommandeASupprimer = '';
    }

    public function ouvrirRappelsModal(): void
    {
        $this->afficherRappelsModal = true;
    }

    public function fermerRappelsModal(): void
    {
        $this->afficherRappelsModal = false;
    }

    public function ouvrirCommandeDepuisRappel(int $commandeId): void
    {
        $this->selectionnerCommande($commandeId);
        $this->fermerRappelsModal();
    }

    public function confirmerSuppressionCommande(): void
    {
        if (!auth()->user()?->hasAnyRole(['gerant', 'المسير'])) {
            abort(403);
        }

        if (!$this->commandeASupprimerId) {
            $this->annulerSuppressionCommande();
            return;
        }

        $commande = Commande::query()
            ->forCurrentSuccursale()
            ->find($this->commandeASupprimerId);

        if (!$commande) {
            $this->dispatch('notify', type: 'error', message: 'الطلب غير موجود.');
            $this->annulerSuppressionCommande();
            return;
        }

        DB::transaction(function () use ($commande): void {
            $commande->delete();
        });

        $this->selectionCommandes = collect($this->selectionCommandes)
            ->map(fn ($id) => (int) $id)
            ->reject(fn ($id) => $id === $commande->id)
            ->values()
            ->toArray();

        if ($this->commandeSelectionneeId === $commande->id) {
            $this->commandeSelectionneeId = null;
            $this->commande = null;
            $this->afficherPaiement = false;
        }

        $this->dispatch('notify', type: 'success', message: 'تم حذف الطلب بنجاح.');
        $this->annulerSuppressionCommande();
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

        $baseQuery = Commande::query()
            ->forCurrentSuccursale()
            ->whereIn('id', $ids);

        $totalSelection = (clone $baseQuery)->count();
        if ($totalSelection === 0) {
            $this->messageErreur = 'الطلبات المحددة غير متاحة في الفرع الحالي.';
            $this->dispatch('notify', type: 'error', message: $this->messageErreur);
            return;
        }

        // Mise a jour en lot: lignes « en_cours » → « pret », puis commande → « pret ».
        $idsCandidats = (clone $baseQuery)
            ->whereIn('statut', ['en_cours', 'en_attente'])
            ->pluck('id');

        $updated = 0;

        if ($idsCandidats->isNotEmpty()) {
            $updated = (int) DB::transaction(function () use ($idsCandidats): int {
                DetailCommande::query()
                    ->whereIn('fk_id_commande', $idsCandidats)
                    ->where('statut_ligne', 'en_cours')
                    ->update(['statut_ligne' => 'pret']);

                return Commande::query()
                    ->whereIn('id', $idsCandidats)
                    ->update(['statut' => 'pret']);
            });

            foreach ($idsCandidats as $cid) {
                Commande::query()->forCurrentSuccursale()->find($cid)?->synchroniserStatutAvecLignes();
            }
        }

        $ignored = max(0, $totalSelection - $updated);

        if ($updated === 0) {
            $this->messageErreur = 'لم يتم تحديث أي طلب. تأكد أن الطلبات في حالة قيد المعالجة.';
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
        $commandesARappeler = Commande::query()
            ->forCurrentSuccursale()
            ->with('client')
            ->whereIn('statut', ['en_cours', 'pret'])
            ->whereDate('date_depot', '<=', now()->subDays(7)->toDateString())
            ->orderBy('date_depot')
            ->limit(20)
            ->get();

        return view('livewire.pos.recherche-commande', [
            'resultats' => $resultats,
            'commande' => $this->commande,
            'modesPaiement' => $modesPaiement,
            'commandesARappeler' => $commandesARappeler,
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
