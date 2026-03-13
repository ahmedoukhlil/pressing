<?php

namespace Tests\Feature\Clients;

use App\Livewire\Clients\ClientForm;
use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ClientFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_can_be_created_from_livewire_form(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(ClientForm::class)
            ->set('nom', 'Ali')
            ->set('prenom', 'Ould')
            ->set('telephone', '22220000')
            ->set('email', 'ali@example.com')
            ->set('adresse', 'Nouakchott')
            ->call('sauvegarder')
            ->assertRedirect(route('clients.index'));

        $this->assertDatabaseHas('clients', [
            'nom' => 'Ali',
            'telephone' => '22220000',
            'email' => 'ali@example.com',
        ]);
    }

    public function test_phone_must_be_unique(): void
    {
        $user = User::factory()->create();
        Client::create([
            'nom' => 'Existant',
            'telephone' => '22221111',
        ]);

        Livewire::actingAs($user)
            ->test(ClientForm::class)
            ->set('nom', 'Fatma')
            ->set('telephone', '22221111')
            ->call('sauvegarder')
            ->assertHasErrors(['telephone' => ['unique']]);
    }
}
