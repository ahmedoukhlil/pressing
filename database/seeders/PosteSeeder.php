<?php

namespace Database\Seeders;

use App\Models\Poste;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class PosteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $postes = [
            ['legacy' => 'Gérant', 'libelle' => 'مدير'],
            ['legacy' => 'Caissier', 'libelle' => 'أمين صندوق'],
            ['legacy' => 'Repasseur / Repasseuse', 'libelle' => 'مكوي / كواية'],
            ['legacy' => 'Lavandier', 'libelle' => 'غسال'],
            ['legacy' => 'Livreur', 'libelle' => 'موصل'],
            ['legacy' => 'Agent d\'accueil', 'libelle' => 'موظف استقبال'],
            ['legacy' => 'Stagiaire', 'libelle' => 'متدرب'],
        ];

        foreach ($postes as $poste) {
            $legacy = Arr::get($poste, 'legacy');
            $libelle = Arr::get($poste, 'libelle');

            $existing = Poste::query()
                ->whereIn('libelle', array_filter([$legacy, $libelle]))
                ->first();

            if ($existing) {
                $existing->update(['libelle' => $libelle, 'actif' => true]);
                continue;
            }

            Poste::create(['libelle' => $libelle, 'actif' => true]);
        }
    }
}
