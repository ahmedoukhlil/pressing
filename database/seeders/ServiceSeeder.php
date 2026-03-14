<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            // 1
            ['libelle' => 'كوستيم',          'libelle_ar' => 'كوستيم',          'icone' => '🤵', 'prix' => 150, 'ordre' => 1],
            // 2
            ['libelle' => 'فست',             'libelle_ar' => 'فست',             'icone' => '🧥', 'prix' => 100, 'ordre' => 2],
            // 3
            ['libelle' => 'كرفت',            'libelle_ar' => 'كرفت',            'icone' => '👔', 'prix' => 20,  'ordre' => 3],
            // 4
            ['libelle' => 'بلزون شميز',      'libelle_ar' => 'بلزون شميز',      'icone' => '🧥', 'prix' => 40,  'ordre' => 4],
            // 5
            ['libelle' => 'شميز',            'libelle_ar' => 'شميز',            'icone' => '👕', 'prix' => 20,  'ordre' => 5],
            // 6
            ['libelle' => 'بنطلون',          'libelle_ar' => 'بنطلون',          'icone' => '👖', 'prix' => 20,  'ordre' => 6],
            // 7
            ['libelle' => 'تي شورت',         'libelle_ar' => 'تي شورت',         'icone' => '👕', 'prix' => 10,  'ordre' => 7],
            // 8
            ['libelle' => 'بلوفير',          'libelle_ar' => 'بلوفير',          'icone' => '🧶', 'prix' => 20,  'ordre' => 8],
            // 9
            ['libelle' => 'فضفاضة كبيرة',   'libelle_ar' => 'فضفاضة كبيرة',   'icone' => '🥻', 'prix' => 50,  'ordre' => 9],
            // 10
            ['libelle' => 'فضفاضة صغيرة',   'libelle_ar' => 'فضفاضة صغيرة',   'icone' => '🥻', 'prix' => 40,  'ordre' => 10],
            // 11
            ['libelle' => 'سروال',           'libelle_ar' => 'سروال',           'icone' => '👖', 'prix' => 20,  'ordre' => 11],
            // 12
            ['libelle' => 'حولي',            'libelle_ar' => 'حولي',            'icone' => '🧣', 'prix' => 10,  'ordre' => 12],
            // 13
            ['libelle' => 'جلابية',          'libelle_ar' => 'جلابية',          'icone' => '🥻', 'prix' => 20,  'ordre' => 13],
            // 14
            ['libelle' => 'ملحفة غاز',       'libelle_ar' => 'ملحفة غاز',       'icone' => '🧕', 'prix' => 20,  'ordre' => 14],
            // 15
            ['libelle' => 'ملحفة أكنيبة',   'libelle_ar' => 'ملحفة أكنيبة',   'icone' => '🧕', 'prix' => 30,  'ordre' => 15],
            // 16
            ['libelle' => 'ملحفة الشكة',    'libelle_ar' => 'ملحفة الشكة',    'icone' => '🧕', 'prix' => 30,  'ordre' => 16],
            // 17
            ['libelle' => 'كاز كوم',         'libelle_ar' => 'كاز كوم',         'icone' => '🧣', 'prix' => 30,  'ordre' => 17],
            // 18
            ['libelle' => 'روبي',            'libelle_ar' => 'روبي',            'icone' => '👗', 'prix' => 10,  'ordre' => 18],
            // 19
            ['libelle' => 'سرفيت',           'libelle_ar' => 'سرفيت',           'icone' => '🧻', 'prix' => 20,  'ordre' => 19],
            // 20
            ['libelle' => 'ريدو',            'libelle_ar' => 'ريدو',            'icone' => '🪟', 'prix' => 50,  'ordre' => 20],
            // 21
            ['libelle' => 'أمبج كوشت',       'libelle_ar' => 'أمبج كوشت',       'icone' => '🛏️', 'prix' => 150, 'ordre' => 21],
            // 22
            ['libelle' => 'كوفرلي',          'libelle_ar' => 'كوفرلي',          'icone' => '🛏️', 'prix' => 120, 'ordre' => 22],
            // 23
            ['libelle' => 'ناب تورش',        'libelle_ar' => 'ناب تورش',        'icone' => '🧺', 'prix' => 30,  'ordre' => 23],
            // 24
            ['libelle' => 'درا',             'libelle_ar' => 'درا',             'icone' => '🛏️', 'prix' => 50,  'ordre' => 24],
            // 25
            ['libelle' => 'متنوع',           'libelle_ar' => 'متنوع',           'icone' => '📦', 'prix' => 0,   'ordre' => 25],
        ];

        foreach ($services as $service) {
            Service::updateOrCreate(
                ['ordre' => $service['ordre']],
                $service + ['actif' => true]
            );
        }
    }
}
