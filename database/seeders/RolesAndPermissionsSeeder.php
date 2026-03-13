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

        $caissier->syncPermissions([]);
        $gerant->syncPermissions($permissions);

        $admin = User::firstOrCreate(
            ['email' => 'admin@pressing.local'],
            ['name' => 'Admin Pressing', 'password' => bcrypt('password')]
        );

        $admin->assignRole('gerant');
    }
}
