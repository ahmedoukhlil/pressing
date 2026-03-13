<?php

namespace App\Livewire\Admin\Roles;

use Livewire\Component;
use Spatie\Permission\Models\Role;

class RoleIndex extends Component
{
    public function render()
    {
        return view('livewire.admin.roles.role-index', [
            'roles' => Role::query()
                ->withCount('permissions')
                ->orderBy('name')
                ->get(),
        ])->layout('layouts.app');
    }
}
