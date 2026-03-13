<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use App\Models\Setting;

class CommandeController extends Controller
{
    public function ticket(Commande $commande)
    {
        abort_unless(
            Commande::query()->forCurrentSuccursale()->whereKey($commande->id)->exists(),
            404
        );

        $commande->load(['client', 'details.service']);
        $settings = Setting::query()
            ->whereIn('key', ['nom_pressing', 'adresse_pressing', 'telephone_pressing', 'footer_ticket'])
            ->pluck('value', 'key');

        return view('tickets.commande', [
            'commande' => $commande,
            'settings' => $settings,
        ]);
    }
}
