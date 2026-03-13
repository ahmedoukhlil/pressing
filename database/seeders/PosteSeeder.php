<?php

namespace Database\Seeders;

use App\Models\Poste;
use Illuminate\Database\Seeder;

class PosteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $postes = [
            'Gérant',
            'Caissier',
            'Repasseur / Repasseuse',
            'Lavandier',
            'Livreur',
            'Agent d\'accueil',
            'Stagiaire',
        ];

        foreach ($postes as $libelle) {
            Poste::updateOrCreate(['libelle' => $libelle], ['actif' => true]);
        }
    }
}
