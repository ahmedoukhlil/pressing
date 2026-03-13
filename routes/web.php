<?php

use App\Http\Controllers\CommandeController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\ProfileController;
use App\Livewire\Admin\Roles\RoleForm;
use App\Livewire\Admin\Roles\RoleIndex;
use App\Livewire\Admin\Succursales\SuccursaleIndex;
use App\Livewire\Admin\Users\UserForm;
use App\Livewire\Admin\Users\UserIndex;
use App\Livewire\Clients\ClientForm;
use App\Livewire\Clients\ClientCompte;
use App\Livewire\Clients\ClientEndettesIndex;
use App\Livewire\Clients\ClientIndex;
use App\Livewire\Dashboard;
use App\Livewire\Finances\RecettesDepenses;
use App\Livewire\Depenses\DepenseIndex;
use App\Livewire\POS\PointDeVente;
use App\Livewire\POS\RechercheCommande;
use App\Livewire\Parametrage\Employes\AvanceSalaireIndex;
use App\Livewire\Parametrage\Employes\EmployeForm;
use App\Livewire\Parametrage\Employes\EmployeIndex;
use App\Livewire\Parametrage\Fournisseurs\FournisseurIndex;
use App\Livewire\Parametrage\ModesPaiement\ModePaiementIndex;
use App\Livewire\Parametrage\Stock\ConsommableIndex;
use App\Livewire\Parametrage\TypesDepenses\TypeDepenseIndex;
use App\Livewire\Services\ServiceForm;
use App\Livewire\Services\ServiceIndex;
use Illuminate\Support\Facades\Route;
use App\Models\Succursale;

Route::redirect('/', '/dashboard');

Route::middleware('auth')->group(function () {
    Route::post('/succursale-active', function () {
        abort_unless(auth()->user()?->hasRole('gerant'), 403);

        $validated = request()->validate([
            'succursale_id' => ['nullable', 'integer', 'exists:succursales,id'],
        ]);

        $succursaleId = $validated['succursale_id'] ?? null;
        if ($succursaleId && !Succursale::query()->whereKey($succursaleId)->where('actif', true)->exists()) {
            return back();
        }

        session(['active_succursale_id' => $succursaleId]);
        return back();
    })->name('succursales.active');

    Route::get('/dashboard', Dashboard::class)->name('dashboard');

    Route::get('/pos', PointDeVente::class)->name('pos');
    Route::get('/recherche', RechercheCommande::class)->name('recherche');

    Route::get('/clients', ClientIndex::class)->name('clients.index');
    Route::get('/clients/endettes', ClientEndettesIndex::class)->name('clients.endettes');
    Route::get('/clients/nouveau', ClientForm::class)->name('clients.create');
    Route::get('/clients/{id}/compte', ClientCompte::class)->name('clients.compte');
    Route::get('/clients/{id}', ClientForm::class)->name('clients.edit');

    Route::get('/services', ServiceIndex::class)->name('services.index');
    Route::get('/services/nouveau', ServiceForm::class)->name('services.create');
    Route::get('/services/{id}', ServiceForm::class)->name('services.edit');

    Route::get('/depenses', DepenseIndex::class)->name('depenses.index');
    Route::get('/finances/recettes-depenses', RecettesDepenses::class)->name('finances.recettes-depenses');
    Route::get('/commandes/{commande}/ticket', [CommandeController::class, 'ticket'])->name('commandes.ticket');
    Route::get('/exports/commandes.pdf', [ExportController::class, 'commandesPdf'])->name('exports.commandes.pdf');
    Route::get('/exports/depenses.pdf', [ExportController::class, 'depensesPdf'])->name('exports.depenses.pdf');

    Route::middleware('role:gerant')->group(function () {
        Route::get('/parametrage/modes-paiement', ModePaiementIndex::class)->name('parametrage.modes-paiement.index');
        Route::get('/parametrage/stock-consommables', ConsommableIndex::class)->name('parametrage.stock-consommables.index');
        Route::get('/exports/stock.pdf', [ExportController::class, 'stockPdf'])->name('exports.stock.pdf');
        Route::get('/parametrage/fournisseurs', FournisseurIndex::class)->name('parametrage.fournisseurs.index');
        Route::get('/parametrage/types-depenses', TypeDepenseIndex::class)->name('parametrage.types-depenses.index');
        Route::get('/parametrage/employes', EmployeIndex::class)->name('parametrage.employes.index');
        Route::get('/parametrage/employes/nouveau', EmployeForm::class)->name('parametrage.employes.create');
        Route::get('/parametrage/employes/{id}/modifier', EmployeForm::class)->name('parametrage.employes.edit');
        Route::get('/parametrage/employes/{employeId}/avances', AvanceSalaireIndex::class)->name('parametrage.employes.avances');
        Route::get('/parametrage/employes/{employeId}/paiement-salaire', AvanceSalaireIndex::class)->name('parametrage.employes.paiement');

        Route::get('/admin/utilisateurs', UserIndex::class)->name('admin.users.index');
        Route::get('/admin/utilisateurs/nouveau', UserForm::class)->name('admin.users.create');
        Route::get('/admin/utilisateurs/{id}', UserForm::class)->name('admin.users.edit');
        Route::get('/admin/succursales', SuccursaleIndex::class)->name('admin.succursales.index');
        Route::get('/admin/roles', RoleIndex::class)->name('admin.roles.index');
        Route::get('/admin/roles/nouveau', RoleForm::class)->name('admin.roles.create');
        Route::get('/admin/roles/{id}', RoleForm::class)->name('admin.roles.edit');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
