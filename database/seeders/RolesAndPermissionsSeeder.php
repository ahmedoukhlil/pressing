<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Vues principales
            'view.dashboard',
            'view.pos',
            'view.recherche',
            'view.clients.index',
            'view.clients.endettes',
            'view.clients.form',
            'view.clients.compte',
            'view.services.index',
            'view.services.form',
            'view.depenses.index',
            'view.finances.recettes-depenses',

            // Vues parametrage
            'view.parametrage.modes-paiement.index',
            'view.parametrage.parametres-generaux',
            'view.parametrage.stock-consommables.index',
            'view.parametrage.fournisseurs.index',
            'view.parametrage.types-depenses.index',
            'view.parametrage.employes.index',
            'view.parametrage.employes.form',
            'view.parametrage.employes.avances',

            // Vues admin
            'view.admin.users.index',
            'view.admin.users.form',
            'view.admin.succursales.index',
            'view.admin.roles.index',
            'view.admin.roles.form',

            // Actions et exports
            'view.commandes.ticket',
            'export.commandes.pdf',
            'export.depenses.pdf',
            'export.finances.details.pdf',
            'export.finances.details.excel',
            'export.stock.pdf',
            'succursales.switch',

            // Compatibilite permissions existantes
            'services.manage',
            'commandes.cancel',
            'caisse.adjust',
            'users.manage',
            'roles.manage',
            'parametrage.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $caissier = Role::findOrCreate('caissier', 'web');
        $gerant = Role::findOrCreate('gerant', 'web');

        $caissier->syncPermissions([
            'view.dashboard',
            'view.pos',
            'view.recherche',
            'view.clients.index',
            'view.clients.compte',
            'view.depenses.index',
            'view.finances.recettes-depenses',
            'view.commandes.ticket',
            'export.commandes.pdf',
            'export.depenses.pdf',
            'export.finances.details.pdf',
            'export.finances.details.excel',
        ]);
        $gerant->syncPermissions($permissions);

        $admin = User::firstOrCreate(
            ['email' => 'admin@pressing.local'],
            ['name' => 'مدير المغسلة', 'password' => bcrypt('password')]
        );

        $admin->assignRole('gerant');
    }
}
