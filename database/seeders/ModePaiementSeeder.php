<?php

namespace Database\Seeders;

use App\Models\ModePaiement;
use Illuminate\Database\Seeder;

class ModePaiementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modes = [
            ['libelle' => 'Espèces', 'code' => 'especes', 'icone' => '💵', 'ordre' => 1],
            ['libelle' => 'Carte bancaire', 'code' => 'carte', 'icone' => '💳', 'ordre' => 2],
            ['libelle' => 'Virement', 'code' => 'virement', 'icone' => '🏦', 'ordre' => 3],
            ['libelle' => 'Non payé', 'code' => 'non_paye', 'icone' => '⏳', 'ordre' => 4],
        ];

        foreach ($modes as $mode) {
            ModePaiement::updateOrCreate(
                ['code' => $mode['code']],
                $mode + ['actif' => true, 'est_systeme' => true]
            );
        }
    }
}
