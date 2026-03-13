<?php

namespace App\Livewire\Admin\Users;

use App\Models\Succursale;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class UserForm extends Component
{
    public ?int $userId = null;
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $role = '';
    public ?int $fkIdSuccursale = null;

    public function mount(?int $id = null): void
    {
        if (!$id) {
            return;
        }

        $user = User::findOrFail($id);
        $this->userId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = (string) optional($user->roles()->first())->name;
        $this->fkIdSuccursale = $user->fk_id_succursale;
    }

    public function sauvegarder(): void
    {
        $rules = [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email,' . ($this->userId ?? 'NULL') . ',id'],
            'role' => ['required', 'exists:roles,name'],
            'fkIdSuccursale' => ['required', 'exists:succursales,id'],
        ];

        if ($this->userId) {
            $rules['password'] = ['nullable', 'string', 'min:8'];
        } else {
            $rules['password'] = ['required', 'string', 'min:8'];
        }

        $data = $this->validate($rules);

        $payload = [
            'name' => $data['name'],
            'email' => $data['email'],
            'fk_id_succursale' => $data['fkIdSuccursale'],
        ];

        if (!empty($data['password'])) {
            $payload['password'] = Hash::make($data['password']);
        }

        if ($this->userId) {
            $user = User::findOrFail($this->userId);
            $user->update($payload);
        } else {
            $user = User::create($payload);
        }

        $user->syncRoles([$data['role']]);

        $this->dispatch('notify', type: 'success', message: 'Utilisateur enregistre.');
        $this->redirect(route('admin.users.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.users.user-form', [
            'roles' => Role::query()->orderBy('name')->get(),
            'succursales' => Succursale::query()->where('actif', true)->orderBy('nom')->get(),
        ])->layout('layouts.app');
    }
}
