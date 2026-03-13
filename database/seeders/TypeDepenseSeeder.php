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
            ['libelle' => 'Salaires', 'icone' => '👥', 'couleur' => '#10B981', 'ordre' => 1],
            ['libelle' => 'Loyer', 'icone' => '🏠', 'couleur' => '#EF4444', 'ordre' => 2],
            ['libelle' => 'Eau & Électricité', 'icone' => '💡', 'couleur' => '#F59E0B', 'ordre' => 3],
            ['libelle' => 'Impôts & Taxes', 'icone' => '🧾', 'couleur' => '#DC2626', 'ordre' => 4],
            ['libelle' => 'Nourriture & Thé', 'icone' => '🍵', 'couleur' => '#78716C', 'ordre' => 5],
            ['libelle' => 'Produits nettoyage', 'icone' => '🧴', 'couleur' => '#3B82F6', 'ordre' => 6],
            ['libelle' => 'Emballages', 'icone' => '📦', 'couleur' => '#8B5CF6', 'ordre' => 7],
            ['libelle' => 'Entretien matériel', 'icone' => '🔧', 'couleur' => '#6B7280', 'ordre' => 8],
            ['libelle' => 'Transport', 'icone' => '🚐', 'couleur' => '#06B6D4', 'ordre' => 9],
            ['libelle' => 'Divers', 'icone' => '📋', 'couleur' => '#9CA3AF', 'ordre' => 10],
        ];

        foreach ($types as $type) {
            TypeDepense::updateOrCreate(
                ['libelle' => $type['libelle']],
                $type + ['actif' => true]
            );
        }
    }
}
