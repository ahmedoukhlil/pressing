<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class UserIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = User::query()->with(['roles', 'succursale'])->latest();

        if (trim($this->search) !== '') {
            $query->where(function ($q): void {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }

        return view('livewire.admin.users.user-index', [
            'users' => $query->paginate(20),
        ])->layout('layouts.app');
    }
}
