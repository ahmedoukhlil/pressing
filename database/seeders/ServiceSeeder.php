<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = [
            ['libelle' => 'Costume', 'libelle_ar' => 'كوستيم', 'icone' => '🤵', 'prix' => 150, 'ordre' => 1],
            ['libelle' => 'Veste', 'libelle_ar' => 'فيست', 'icone' => '🧥', 'prix' => 100, 'ordre' => 2],
            ['libelle' => 'Cravatte', 'libelle_ar' => 'كرفتة', 'icone' => '👔', 'prix' => 20, 'ordre' => 3],
            ['libelle' => 'Blouson Chemise', 'libelle_ar' => 'بلوزون قميص', 'icone' => '🧥', 'prix' => 40, 'ordre' => 4],
            ['libelle' => 'Chemise', 'libelle_ar' => 'قميص', 'icone' => '👕', 'prix' => 20, 'ordre' => 5],
            ['libelle' => 'Pantalon', 'libelle_ar' => 'بنطلون', 'icone' => '👖', 'prix' => 20, 'ordre' => 6],
            ['libelle' => 'Tee-shirt', 'libelle_ar' => 'تي شيرت', 'icone' => '👕', 'prix' => 10, 'ordre' => 7],
            ['libelle' => 'Boubou', 'libelle_ar' => 'فضفاضة', 'icone' => '🥻', 'prix' => 50, 'ordre' => 8],
            ['libelle' => 'Serwal', 'libelle_ar' => 'سروال', 'icone' => '👖', 'prix' => 20, 'ordre' => 9],
            ['libelle' => 'Hawli', 'libelle_ar' => 'حولي', 'icone' => '🧣', 'prix' => 10, 'ordre' => 10],
            ['libelle' => 'Jelabia', 'libelle_ar' => 'جلابية', 'icone' => '🥻', 'prix' => 20, 'ordre' => 11],
            ['libelle' => 'Voile Perssi (Kneyba)', 'libelle_ar' => 'ملحفة كنيبة', 'icone' => '🧕', 'prix' => 30, 'ordre' => 12],
            ['libelle' => 'Voile Chigue', 'libelle_ar' => 'ملحفة الشكة', 'icone' => '🧕', 'prix' => 30, 'ordre' => 13],
            ['libelle' => 'Gaz Come', 'libelle_ar' => 'كاز كوم', 'icone' => '🧣', 'prix' => 30, 'ordre' => 14],
            ['libelle' => 'Robe', 'libelle_ar' => 'روبي', 'icone' => '👗', 'prix' => 10, 'ordre' => 15],
            ['libelle' => 'Servette', 'libelle_ar' => 'سيرفيت', 'icone' => '🧻', 'prix' => 20, 'ordre' => 16],
            ['libelle' => 'Rideau', 'libelle_ar' => 'ريدو', 'icone' => '🪟', 'prix' => 50, 'ordre' => 17],
            ['libelle' => 'Couverture Couchette', 'libelle_ar' => 'أمبج كوشيت', 'icone' => '🛏️', 'prix' => 150, 'ordre' => 18],
            ['libelle' => 'Cover LT', 'libelle_ar' => 'كوفير لي', 'icone' => '🛏️', 'prix' => 120, 'ordre' => 19],
            ['libelle' => 'Nappe - Torchon', 'libelle_ar' => 'ناب تورشون', 'icone' => '🧺', 'prix' => 30, 'ordre' => 20],
            ['libelle' => 'Drap', 'libelle_ar' => 'دراب', 'icone' => '🛏️', 'prix' => 50, 'ordre' => 21],
        ];

        foreach ($services as $service) {
            Service::updateOrCreate(
                ['libelle' => $service['libelle']],
                $service + ['actif' => true]
            );
        }
    }
}
