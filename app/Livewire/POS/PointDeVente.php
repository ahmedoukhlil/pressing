<?php

namespace App\Livewire\POS;

use App\Models\CaisseOperation;
use App\Models\Client;
use App\Models\Commande;
use App\Models\DetailCommande;
use App\Models\Service;
use App\Support\SuccursaleContext;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class PointDeVente extends Component
{
    public string $rechercheClient = '';
    public ?int $clientSelectionneId = null;
    public ?array $clientInfo = null;
    public array $clientsTrouves = [];
    public bool $afficherFormNouveauClient = false;

    public string $nouveauNom = '';
    public string $nouveauPrenom = '';
    public string $nouveauTelephone = '';

    public array $panier = [];
    public string $modeReglement = 'especes';
    public float $montantPaye = 0;
    public string $remisePourcentage = '0';
    public string $notes = '';

    public bool $afficherModalPaiement = false;
    public ?int $commandeCreeId = null;

    public function mount(): void
    {
        $this->resetPanier();
    }

    public function rechercherClient(): void
    {
        $tel = $this->normalizeTelephone($this->rechercheClient);
        $this->rechercheClient = $tel;

        if ($tel === '') {
            $this->clientsTrouves = [];
            $this->afficherFormNouveauClient = false;
            return;
        }

        $clients = Client::query()
            ->forCurrentSuccursale()
            ->where('telephone', 'like', "{$tel}%")
            ->orderBy('nom')
            ->limit(8)
            ->get(['id', 'nom', 'prenom', 'telephone']);

        $this->clientsTrouves = $clients->map(fn (Client $c): array => [
            'id' => $c->id,
            'nom' => trim("{$c->nom} {$c->prenom}"),
            'telephone' => $c->telephone,
        ])->toArray();

        $estNumeroComplet = strlen($tel) === 8;
        $this->afficherFormNouveauClient = $estNumeroComplet && empty($this->clientsTrouves);
        $this->nouveauTelephone = $this->afficherFormNouveauClient ? $tel : '';
        if ($this->afficherFormNouveauClient) {
            $this->resetErrorBag();
            $this->resetValidation();
        }
    }

    public function updatedRechercheClient(): void
    {
        $this->rechercherClient();
    }

    public function selectionnerClient(int $id): void
    {
        $client = Client::query()->forCurrentSuccursale()->findOrFail($id);
        $this->clientSelectionneId = $client->id;
        $this->clientInfo = [
            'id' => $client->id,
            'nom' => $client->full_name,
            'telephone' => $client->telephone,
        ];
        $this->clientsTrouves = [];
        $this->afficherFormNouveauClient = false;
        $this->rechercheClient = $client->telephone;
    }

    public function creerNouveauClient(): void
    {
        $this->nouveauNom = $this->normalizeName($this->nouveauNom);
        $this->nouveauPrenom = $this->normalizeName($this->nouveauPrenom);
        $this->nouveauTelephone = $this->normalizeTelephone($this->nouveauTelephone);

        $this->validate([
            'nouveauNom' => ['required', 'string', 'max:100'],
            'nouveauTelephone' => [
                'required',
                'regex:/^\d{8}$/',
                'unique:clients,telephone,NULL,id,fk_id_succursale,' . SuccursaleContext::currentIdForWrite(),
            ],
        ]);

        $client = Client::create([
            'fk_id_succursale' => SuccursaleContext::currentIdForWrite(),
            'nom' => $this->nouveauNom,
            'prenom' => $this->nouveauPrenom ?: null,
            'telephone' => $this->nouveauTelephone,
        ]);

        $this->selectionnerClient($client->id);
        $this->resetFormClient();
    }

    public function updatedNouveauTelephone(): void
    {
        $this->nouveauTelephone = $this->normalizeTelephone($this->nouveauTelephone);
    }

    public function fermerModalNouveauClient(): void
    {
        $this->resetFormClient();
        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function resetClient(): void
    {
        $this->clientSelectionneId = null;
        $this->clientInfo = null;
        $this->clientsTrouves = [];
        $this->rechercheClient = '';
        $this->resetFormClient();
    }

    public function ajouterAuPanier(int $serviceId): void
    {
        $service = Service::findOrFail($serviceId);
        $index = collect($this->panier)->search(fn (array $item) => $item['service_id'] === $serviceId);

        if ($index !== false) {
            $this->panier[$index]['quantite']++;
            $this->panier[$index]['sous_total'] = $this->panier[$index]['quantite'] * $this->panier[$index]['prix'];
            return;
        }

        $this->panier[] = [
            'service_id' => $service->id,
            'libelle' => $service->libelle_ar ?: '-',
            'icone' => $service->icone,
            'prix' => (float) $service->prix,
            'quantite' => 1,
            'sous_total' => (float) $service->prix,
            'notes' => '',
        ];
    }

    public function modifierQuantite(int $index, int $quantite): void
    {
        if ($quantite <= 0) {
            $this->retirerDuPanier($index);
            return;
        }

        if (!isset($this->panier[$index])) {
            return;
        }

        $quantite = max(1, (int) $quantite);
        $this->panier[$index]['quantite'] = $quantite;
        $this->panier[$index]['sous_total'] = $quantite * $this->panier[$index]['prix'];
    }

    public function retirerDuPanier(int $index): void
    {
        array_splice($this->panier, $index, 1);
    }

    public function modifierObservation(int $index, string $observation): void
    {
        if (!isset($this->panier[$index])) {
            return;
        }

        $this->panier[$index]['notes'] = trim($observation);
    }

    public function resetPanier(): void
    {
        $this->panier = [];
    }

    public function getMontantTotalProperty(): float
    {
        return (float) collect($this->panier)->sum('sous_total');
    }

    public function getRemiseMontantProperty(): float
    {
        $pourcentage = max(0, min(100, (float) $this->remisePourcentage));
        return round($this->montant_total * ($pourcentage / 100), 2);
    }

    public function getMontantTotalNetProperty(): float
    {
        return max(0, round($this->montant_total - $this->remise_montant, 2));
    }

    public function getResteAPayerProperty(): float
    {
        return max(0, round($this->montant_total_net - $this->montantPaye, 2));
    }

    public function ouvrirModalPaiement(): void
    {
        if (!$this->clientSelectionneId) {
            $this->addError('client', 'Veuillez selectionner un client.');
            return;
        }

        if (empty($this->panier)) {
            $this->addError('panier', 'Le panier est vide.');
            return;
        }

        // Au depot, le paiement est optionnel : 0 = non paye, >0 = avance/total.
        $this->montantPaye = 0;
        $this->modeReglement = 'non_paye';
        $this->remisePourcentage = (string) max(0, min(100, (float) $this->remisePourcentage));
        $this->afficherModalPaiement = true;
    }

    public function updatedMontantPaye($value): void
    {
        $montant = (float) $value;

        if ($montant <= 0) {
            $this->modeReglement = 'non_paye';
            return;
        }

        if ($this->modeReglement === 'non_paye') {
            $this->modeReglement = 'especes';
        }
    }

    public function validerCommande(): void
    {
        $this->validate([
            'clientSelectionneId' => ['required', 'exists:clients,id'],
            'modeReglement' => ['required', 'in:especes,carte,virement,non_paye'],
            'montantPaye' => ['required', 'numeric', 'min:0', 'max:' . $this->montant_total_net],
            'remisePourcentage' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        if (!Client::query()->forCurrentSuccursale()->whereKey($this->clientSelectionneId)->exists()) {
            $this->addError('clientSelectionneId', 'الزبون غير متاح في الفرع الحالي.');
            return;
        }

        if ($this->montantPaye <= 0) {
            $this->modeReglement = 'non_paye';
        }

        if ($this->montantPaye > 0 && $this->modeReglement === 'non_paye') {
            $this->addError('modeReglement', 'Le mode Non paye est reserve aux commandes sans versement.');
            return;
        }

        DB::transaction(function (): void {
            $succursaleId = SuccursaleContext::currentIdForWrite();
            $numData = Commande::generateNumeroCommande(null, $succursaleId);

            $commande = Commande::create([
                'fk_id_succursale' => $succursaleId,
                'numero_commande' => $numData['numero_commande'],
                'annee_commande' => $numData['annee_commande'],
                'n_ordre' => $numData['n_ordre'],
                'fk_id_client' => $this->clientSelectionneId,
                'date_depot' => now(),
                'date_livraison_prevue' => now()->addDays(2),
                'statut' => 'en_cours',
                'montant_total' => $this->montant_total_net,
                'montant_paye' => $this->montantPaye,
                'reste_a_payer' => $this->reste_a_payer,
                'remise_depot_pourcentage' => $this->remisePourcentage,
                'remise_depot_montant' => $this->remise_montant,
                'remise_reglement_montant' => 0,
                'total_remise' => $this->remise_montant,
                'mode_reglement' => $this->modeReglement,
                'est_paye' => $this->montantPaye >= $this->montant_total_net,
                'notes' => $this->notes ?: null,
                'fk_id_user' => auth()->id(),
            ]);

            foreach ($this->panier as $item) {
                DetailCommande::create([
                    'fk_id_commande' => $commande->id,
                    'fk_id_service' => $item['service_id'],
                    'prix_unitaire' => $item['prix'],
                    'quantite' => $item['quantite'],
                    'sous_total' => $item['sous_total'],
                    'notes' => $item['notes'] ?: null,
                ]);
            }

            if ($this->montantPaye > 0) {
                CaisseOperation::create([
                    'fk_id_succursale' => $succursaleId,
                    'date_operation' => now(),
                    'montant_operation' => $this->montantPaye,
                    'designation' => 'Paiement commande ' . $commande->numero_commande,
                    'fk_id_client' => $this->clientSelectionneId,
                    'entree_espece' => $this->modeReglement === 'especes' ? $this->montantPaye : 0,
                    'retrait_espece' => 0,
                    'fk_id_commande' => $commande->id,
                    'fk_id_user' => auth()->id(),
                    'mode_paiement' => $this->modeReglement,
                ]);
            }

            $this->commandeCreeId = $commande->id;
        });

        $this->afficherModalPaiement = false;

        $message = 'Commande creee avec succes.';
        if ($this->montantPaye <= 0) {
            $message = 'Depot enregistre sans paiement.';
        } elseif ($this->montantPaye < $this->montant_total) {
            $message = 'Depot enregistre avec avance client.';
        } elseif ($this->montantPaye >= $this->montant_total) {
            $message = 'Depot enregistre et facture completement payee.';
        }

        session()->flash('success', $message);
        $this->dispatch('notify', type: 'success', message: $message);
        $this->resetFormCommande();
        $this->dispatch('imprimerTicket', commandeId: $this->commandeCreeId);
    }

    private function resetFormClient(): void
    {
        $this->nouveauNom = '';
        $this->nouveauPrenom = '';
        $this->nouveauTelephone = '';
        $this->afficherFormNouveauClient = false;
    }

    private function resetFormCommande(): void
    {
        $this->reset([
            'rechercheClient',
            'clientSelectionneId',
            'clientInfo',
            'clientsTrouves',
            'panier',
            'remisePourcentage',
            'notes',
        ]);

        $this->resetFormClient();
        $this->modeReglement = 'especes';
        $this->montantPaye = 0;
        $this->afficherModalPaiement = false;
        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.pos.point-de-vente', [
            'services' => Service::actif()->get(),
        ])->layout('layouts.app');
    }

    private function normalizeTelephone(string $telephone): string
    {
        $digits = preg_replace('/\D+/', '', $telephone) ?? '';
        return substr($digits, 0, 8);
    }

    private function normalizeName(string $value): string
    {
        $clean = preg_replace('/\s+/', ' ', trim($value)) ?? '';
        return $clean;
    }
}
