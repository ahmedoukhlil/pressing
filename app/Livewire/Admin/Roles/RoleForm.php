<?php

namespace App\Livewire\Admin\Roles;

use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleForm extends Component
{
    public ?int $roleId = null;
    public string $name = '';
    public array $selectedPermissions = [];

    public function mount(?int $id = null): void
    {
        if (!$id) {
            return;
        }

        $role = Role::findOrFail($id);
        $this->roleId = $role->id;
        $this->name = $role->name;
        $this->selectedPermissions = $role->permissions()->pluck('name')->toArray();
    }

    public function sauvegarder(): void
    {
        $data = $this->validate([
            'name' => ['required', 'string', 'max:120', 'unique:roles,name,' . ($this->roleId ?? 'NULL') . ',id'],
            'selectedPermissions' => ['array'],
            'selectedPermissions.*' => ['exists:permissions,name'],
        ]);

        if ($this->roleId) {
            $role = Role::findOrFail($this->roleId);
            $role->update(['name' => $data['name']]);
        } else {
            $role = Role::create(['name' => $data['name']]);
        }

        $role->syncPermissions($data['selectedPermissions'] ?? []);

        $this->dispatch('notify', type: 'success', message: 'Role enregistre.');
        $this->redirect(route('admin.roles.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.roles.role-form', [
            'permissions' => Permission::query()->orderBy('name')->get(),
        ])->layout('layouts.app');
    }
}
