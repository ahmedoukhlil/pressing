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
            ['libelle' => 'نقدا', 'code' => 'especes', 'icone' => '💵', 'ordre' => 1],
            ['libelle' => 'بطاقة بنكية', 'code' => 'carte', 'icone' => '💳', 'ordre' => 2],
            ['libelle' => 'تحويل بنكي', 'code' => 'virement', 'icone' => '🏦', 'ordre' => 3],
            ['libelle' => 'غير مدفوع', 'code' => 'non_paye', 'icone' => '⏳', 'ordre' => 4],
        ];

        foreach ($modes as $mode) {
            ModePaiement::updateOrCreate(
                ['code' => $mode['code']],
                $mode + ['actif' => true, 'est_systeme' => true]
            );
        }
    }
}
