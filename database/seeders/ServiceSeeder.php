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
            ['libelle' => 'بدلة', 'libelle_ar' => 'بدلة', 'icone' => '🤵', 'prix' => 150, 'ordre' => 1],
            ['libelle' => 'سترة', 'libelle_ar' => 'سترة', 'icone' => '🧥', 'prix' => 100, 'ordre' => 2],
            ['libelle' => 'ربطة عنق', 'libelle_ar' => 'ربطة عنق', 'icone' => '👔', 'prix' => 20, 'ordre' => 3],
            ['libelle' => 'بلوزون وقميص', 'libelle_ar' => 'بلوزون وقميص', 'icone' => '🧥', 'prix' => 40, 'ordre' => 4],
            ['libelle' => 'قميص', 'libelle_ar' => 'قميص', 'icone' => '👕', 'prix' => 20, 'ordre' => 5],
            ['libelle' => 'بنطال', 'libelle_ar' => 'بنطال', 'icone' => '👖', 'prix' => 20, 'ordre' => 6],
            ['libelle' => 'تيشيرت', 'libelle_ar' => 'تيشيرت', 'icone' => '👕', 'prix' => 10, 'ordre' => 7],
            ['libelle' => 'دراعة', 'libelle_ar' => 'دراعة', 'icone' => '🥻', 'prix' => 50, 'ordre' => 8],
            ['libelle' => 'سروال', 'libelle_ar' => 'سروال', 'icone' => '👖', 'prix' => 20, 'ordre' => 9],
            ['libelle' => 'حولي', 'libelle_ar' => 'حولي', 'icone' => '🧣', 'prix' => 10, 'ordre' => 10],
            ['libelle' => 'جلابية', 'libelle_ar' => 'جلابية', 'icone' => '🥻', 'prix' => 20, 'ordre' => 11],
            ['libelle' => 'ملحفة كنيبة', 'libelle_ar' => 'ملحفة كنيبة', 'icone' => '🧕', 'prix' => 30, 'ordre' => 12],
            ['libelle' => 'ملحفة الشكة', 'libelle_ar' => 'ملحفة الشكة', 'icone' => '🧕', 'prix' => 30, 'ordre' => 13],
            ['libelle' => 'كاز كوم', 'libelle_ar' => 'كاز كوم', 'icone' => '🧣', 'prix' => 30, 'ordre' => 14],
            ['libelle' => 'فستان', 'libelle_ar' => 'فستان', 'icone' => '👗', 'prix' => 10, 'ordre' => 15],
            ['libelle' => 'منشفة', 'libelle_ar' => 'منشفة', 'icone' => '🧻', 'prix' => 20, 'ordre' => 16],
            ['libelle' => 'ستارة', 'libelle_ar' => 'ستارة', 'icone' => '🪟', 'prix' => 50, 'ordre' => 17],
            ['libelle' => 'بطانية السرير', 'libelle_ar' => 'بطانية السرير', 'icone' => '🛏️', 'prix' => 150, 'ordre' => 18],
            ['libelle' => 'غطاء سرير', 'libelle_ar' => 'غطاء سرير', 'icone' => '🛏️', 'prix' => 120, 'ordre' => 19],
            ['libelle' => 'مفرش وممسحة', 'libelle_ar' => 'مفرش وممسحة', 'icone' => '🧺', 'prix' => 30, 'ordre' => 20],
            ['libelle' => 'شرشف', 'libelle_ar' => 'شرشف', 'icone' => '🛏️', 'prix' => 50, 'ordre' => 21],
        ];

        foreach ($services as $service) {
            Service::updateOrCreate(
                ['ordre' => $service['ordre']],
                $service + ['actif' => true]
            );
        }
    }
}
