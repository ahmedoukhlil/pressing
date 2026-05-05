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
use App\Livewire\Parametrage\ParametresGeneraux;
use App\Livewire\Parametrage\Stock\ConsommableIndex;
use App\Livewire\Parametrage\TypesDepenses\TypeDepenseIndex;
use App\Livewire\Services\ServiceForm;
use App\Livewire\Services\ServiceIndex;
use Illuminate\Support\Facades\Route;
use App\Models\Succursale;

Route::redirect('/', '/dashboard');

Route::middleware('auth')->group(function () {
    Route::post('/succursale-active', function () {
        $user = auth()->user();
        abort_unless($user?->can('succursales.switch') || \App\Support\SuccursaleContext::canSwitch(), 403);

        $validated = request()->validate([
            'succursale_id' => ['nullable', 'integer', 'exists:succursales,id'],
        ]);

        $succursaleId = $validated['succursale_id'] ?? null;

        if ($succursaleId) {
            $succursale = Succursale::query()->whereKey($succursaleId)->where('actif', true)->first();
            if (!$succursale) {
                return back();
            }
            // Non-gérants can only switch to their assigned succursales
            if (!\App\Support\SuccursaleContext::isGerant() && !$user->hasAccessToSuccursale($succursaleId)) {
                abort(403);
            }
        }

        session(['active_succursale_id' => $succursaleId]);
        return back();
    })->name('succursales.active');

    Route::get('/dashboard', Dashboard::class)->middleware('permission:view.dashboard')->name('dashboard');

    Route::get('/pos', PointDeVente::class)->middleware('permission:view.pos')->name('pos');
    Route::get('/recherche', RechercheCommande::class)->middleware('permission:view.recherche')->name('recherche');

    Route::get('/clients', ClientIndex::class)->middleware('permission:view.clients.index')->name('clients.index');
    Route::get('/clients/endettes', ClientEndettesIndex::class)->middleware('permission:view.clients.index')->name('clients.endettes');
    Route::get('/clients/nouveau', ClientForm::class)->middleware('permission:view.clients.index')->name('clients.create');
    Route::get('/clients/{id}/compte', ClientCompte::class)->middleware('permission:view.clients.index')->name('clients.compte');
    Route::get('/clients/{id}', ClientForm::class)->middleware('permission:view.clients.index')->name('clients.edit');

    Route::get('/services', ServiceIndex::class)->middleware('permission:view.services.index')->name('services.index');
    Route::get('/services/nouveau', ServiceForm::class)->middleware('permission:view.services.index')->name('services.create');
    Route::get('/services/{id}', ServiceForm::class)->middleware('permission:view.services.index')->name('services.edit');

    Route::get('/depenses', DepenseIndex::class)->middleware('permission:view.depenses.index')->name('depenses.index');
    Route::get('/finances/recettes-depenses', RecettesDepenses::class)->middleware('permission:view.finances.recettes-depenses')->name('finances.recettes-depenses');
    Route::get('/commandes/{commande}/ticket', [CommandeController::class, 'ticket'])->middleware('permission:view.commandes.ticket')->name('commandes.ticket');
    Route::get('/exports/commandes.pdf', [ExportController::class, 'commandesPdf'])->middleware('permission:export.commandes.pdf')->name('exports.commandes.pdf');
    Route::get('/exports/depenses.pdf', [ExportController::class, 'depensesPdf'])->middleware('permission:export.depenses.pdf')->name('exports.depenses.pdf');
    Route::get('/exports/finances/details.pdf', [ExportController::class, 'financesDetailsPdf'])->middleware('permission:export.finances.details.pdf')->name('exports.finances.details.pdf');
    Route::get('/exports/finances/details.excel', [ExportController::class, 'financesDetailsExcel'])->middleware('permission:export.finances.details.excel')->name('exports.finances.details.excel');

    Route::get('/parametrage/modes-paiement', ModePaiementIndex::class)->middleware('permission:view.parametrage.modes-paiement.index')->name('parametrage.modes-paiement.index');
    Route::redirect('/parametrage/parametres-generaux', '/parametrage/fidelite');
    Route::get('/parametrage/fidelite', ParametresGeneraux::class)->middleware('permission:view.parametrage.parametres-generaux')->name('parametrage.fidelite');
    Route::get('/parametrage/stock-consommables', ConsommableIndex::class)->middleware('permission:view.parametrage.stock-consommables.index')->name('parametrage.stock-consommables.index');
    Route::get('/exports/stock.pdf', [ExportController::class, 'stockPdf'])->middleware('permission:export.stock.pdf')->name('exports.stock.pdf');
    Route::get('/parametrage/fournisseurs', FournisseurIndex::class)->middleware('permission:view.parametrage.fournisseurs.index')->name('parametrage.fournisseurs.index');
    Route::get('/parametrage/types-depenses', TypeDepenseIndex::class)->middleware('permission:view.parametrage.types-depenses.index')->name('parametrage.types-depenses.index');
    Route::get('/parametrage/employes', EmployeIndex::class)->middleware('permission:view.parametrage.employes.index')->name('parametrage.employes.index');
    Route::get('/parametrage/employes/nouveau', EmployeForm::class)->middleware('permission:view.parametrage.employes.index')->name('parametrage.employes.create');
    Route::get('/parametrage/employes/{id}/modifier', EmployeForm::class)->middleware('permission:view.parametrage.employes.index')->name('parametrage.employes.edit');
    Route::get('/parametrage/employes/{employeId}/avances', AvanceSalaireIndex::class)->middleware('permission:view.parametrage.employes.index')->name('parametrage.employes.avances');
    Route::get('/parametrage/employes/{employeId}/paiement-salaire', AvanceSalaireIndex::class)->middleware('permission:view.parametrage.employes.index')->name('parametrage.employes.paiement');

    Route::get('/admin/utilisateurs', UserIndex::class)->middleware('permission:view.admin.users.index')->name('admin.users.index');
    Route::get('/admin/utilisateurs/nouveau', UserForm::class)->middleware('permission:view.admin.users.index')->name('admin.users.create');
    Route::get('/admin/utilisateurs/{id}', UserForm::class)->middleware('permission:view.admin.users.index')->name('admin.users.edit');
    Route::get('/admin/succursales', SuccursaleIndex::class)->middleware('permission:view.admin.succursales.index')->name('admin.succursales.index');
    Route::get('/admin/roles', RoleIndex::class)->middleware('permission:view.admin.users.index')->name('admin.roles.index');
    Route::get('/admin/roles/nouveau', RoleForm::class)->middleware('permission:view.admin.users.index')->name('admin.roles.create');
    Route::get('/admin/roles/{id}', RoleForm::class)->middleware('permission:view.admin.users.index')->name('admin.roles.edit');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
