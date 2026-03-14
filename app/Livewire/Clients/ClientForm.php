<?php

namespace App\Livewire\Clients;

use App\Models\Client;
use App\Support\SuccursaleContext;
use Illuminate\Validation\Rule;
use Livewire\Component;

class ClientForm extends Component
{
    public ?int $clientId = null;
    public string $codeClient = '';
    public string $nom = '';
    public string $prenom = '';
    public string $telephone = '';
    public string $email = '';
    public string $adresse = '';

    public function mount(?int $id = null): void
    {
        if (!$id) {
            return;
        }

        $c = Client::query()->forCurrentSuccursale()->findOrFail($id);
        $this->clientId = $c->id;
        $this->codeClient = $c->code_client ?? '';
        $this->nom = $c->nom;
        $this->prenom = $c->prenom ?? '';
        $this->telephone = $c->telephone;
        $this->email = $c->email ?? '';
        $this->adresse = $c->adresse ?? '';
    }

    public function sauvegarder(): void
    {
        $this->validate([
            'nom' => ['required', 'string', 'max:100'],
            'telephone' => [
                'required',
                'string',
                'max:20',
                Rule::unique('clients', 'telephone')
                    ->where('fk_id_succursale', SuccursaleContext::currentIdForWrite())
                    ->ignore($this->clientId),
            ],
            'email' => ['nullable', 'email', 'max:255'],
        ], [
            'nom.required' => 'الاسم إلزامي.',
            'telephone.required' => 'الهاتف إلزامي.',
            'telephone.unique' => 'هذا الرقم مستخدم مسبقاً.',
            'email.email' => 'صيغة البريد الإلكتروني غير صحيحة.',
        ]);

        $data = [
            'fk_id_succursale' => SuccursaleContext::currentIdForWrite(),
            'nom' => $this->nom,
            'prenom' => $this->prenom ?: null,
            'telephone' => $this->telephone,
            'email' => $this->email ?: null,
            'adresse' => $this->adresse ?: null,
        ];

        if ($this->clientId) {
            Client::query()->forCurrentSuccursale()->findOrFail($this->clientId)->update($data);
            session()->flash('success', 'تم تحديث بيانات الزبون بنجاح.');
        } else {
            Client::create($data);
            session()->flash('success', 'تم إنشاء الزبون بنجاح.');
        }

        $this->redirect(route('clients.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.clients.client-form')->layout('layouts.app');
    }
}
