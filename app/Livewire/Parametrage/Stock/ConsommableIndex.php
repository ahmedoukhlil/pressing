<?php

namespace App\Livewire\Parametrage\Stock;

use App\Models\Consommable;
use App\Models\StockMouvement;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class ConsommableIndex extends Component
{
    use WithPagination;

    public string $recherche = '';

    public string $libelle = '';
    public string $unite = 'piece';
    public string $seuilAlerte = '0';

    public ?int $consommableSelectionneId = null;
    public string $typeMouvement = 'entree';
    public string $quantite = '';
    public string $motif = '';
    public string $notes = '';

    public function updatingRecherche(): void
    {
        $this->resetPage();
    }

    public function ajouterConsommable(): void
    {
        $this->validate([
            'libelle' => ['required', 'string', 'max:120', 'unique:consommables,libelle'],
            'unite' => ['required', 'string', 'max:30'],
            'seuilAlerte' => ['required', 'numeric', 'min:0'],
        ]);

        Consommable::create([
            'libelle' => trim($this->libelle),
            'unite' => trim($this->unite),
            'stock_actuel' => 0,
            'seuil_alerte' => (float) $this->seuilAlerte,
            'actif' => true,
        ]);

        $this->reset(['libelle', 'unite', 'seuilAlerte']);
        $this->unite = 'piece';
        $this->seuilAlerte = '0';

        $this->dispatch('notify', type: 'success', message: 'تمت إضافة المادة الاستهلاكية بنجاح.');
    }

    public function ouvrirMouvement(int $consommableId, string $type): void
    {
        $this->consommableSelectionneId = $consommableId;
        $this->typeMouvement = in_array($type, ['entree', 'sortie'], true) ? $type : 'entree';
        $this->quantite = '';
        $this->motif = '';
        $this->notes = '';
        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function enregistrerMouvement(): void
    {
        $this->validate([
            'consommableSelectionneId' => ['required', 'exists:consommables,id'],
            'typeMouvement' => ['required', 'in:entree,sortie'],
            'quantite' => ['required', 'numeric', 'min:0.01'],
            'motif' => [$this->typeMouvement === 'sortie' ? 'required' : 'nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        DB::transaction(function (): void {
            $consommable = Consommable::query()
                ->lockForUpdate()
                ->findOrFail($this->consommableSelectionneId);

            $quantite = (float) $this->quantite;
            $stockActuel = (float) $consommable->stock_actuel;

            if ($this->typeMouvement === 'sortie' && $quantite > $stockActuel) {
                $this->addError('quantite', 'الكمية المطلوبة للخروج أكبر من المخزون الحالي.');
                return;
            }

            $nouveauStock = $this->typeMouvement === 'entree'
                ? $stockActuel + $quantite
                : $stockActuel - $quantite;

            $consommable->update([
                'stock_actuel' => $nouveauStock,
            ]);

            StockMouvement::create([
                'fk_id_consommable' => $consommable->id,
                'type_mouvement' => $this->typeMouvement,
                'quantite' => $quantite,
                'date_mouvement' => now(),
                'motif' => $this->motif ?: null,
                'notes' => $this->notes ?: null,
                'fk_id_user' => auth()->id(),
            ]);
        });

        if ($this->getErrorBag()->isNotEmpty()) {
            return;
        }

        $this->quantite = '';
        $this->motif = '';
        $this->notes = '';

        $this->dispatch('notify', type: 'success', message: 'تم تسجيل حركة المخزون بنجاح.');
    }

    public function render()
    {
        $consommables = Consommable::query()
            ->when($this->recherche, fn ($q) => $q
                ->where('libelle', 'like', "%{$this->recherche}%")
                ->orWhere('unite', 'like', "%{$this->recherche}%"))
            ->orderBy('libelle')
            ->paginate(20);

        $consommableSelectionne = $this->consommableSelectionneId
            ? Consommable::find($this->consommableSelectionneId)
            : null;

        $mouvements = StockMouvement::query()
            ->with('consommable')
            ->when($this->consommableSelectionneId, fn ($q) => $q->where('fk_id_consommable', $this->consommableSelectionneId))
            ->latest('date_mouvement')
            ->limit(20)
            ->get();

        return view('livewire.parametrage.stock.consommable-index', [
            'consommables' => $consommables,
            'consommableSelectionne' => $consommableSelectionne,
            'mouvements' => $mouvements,
        ])->layout('layouts.app');
    }
}
