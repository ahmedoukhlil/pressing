<?php

namespace Database\Seeders;

use App\Models\TypeDepense;
use Illuminate\Database\Seeder;

class TypeDepenseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['libelle' => 'الرواتب', 'icone' => '👥', 'couleur' => '#10B981', 'ordre' => 1],
            ['libelle' => 'الإيجار', 'icone' => '🏠', 'couleur' => '#EF4444', 'ordre' => 2],
            ['libelle' => 'الماء والكهرباء', 'icone' => '💡', 'couleur' => '#F59E0B', 'ordre' => 3],
            ['libelle' => 'الضرائب والرسوم', 'icone' => '🧾', 'couleur' => '#DC2626', 'ordre' => 4],
            ['libelle' => 'الأكل والشاي', 'icone' => '🍵', 'couleur' => '#78716C', 'ordre' => 5],
            ['libelle' => 'مواد التنظيف', 'icone' => '🧴', 'couleur' => '#3B82F6', 'ordre' => 6],
            ['libelle' => 'التغليف', 'icone' => '📦', 'couleur' => '#8B5CF6', 'ordre' => 7],
            ['libelle' => 'صيانة المعدات', 'icone' => '🔧', 'couleur' => '#6B7280', 'ordre' => 8],
            ['libelle' => 'النقل', 'icone' => '🚐', 'couleur' => '#06B6D4', 'ordre' => 9],
            ['libelle' => 'متفرقات', 'icone' => '📋', 'couleur' => '#9CA3AF', 'ordre' => 10],
        ];

        foreach ($types as $type) {
            TypeDepense::updateOrCreate(
                ['ordre' => $type['ordre']],
                $type + ['actif' => true]
            );
        }
    }
}
