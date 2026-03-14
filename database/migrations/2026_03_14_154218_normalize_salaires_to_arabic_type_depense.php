<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('types_depenses')
            ->where('libelle', 'Salaires')
            ->update([
                'libelle' => 'الرواتب',
                'icone' => '👥',
                'couleur' => '#10B981',
                'actif' => true,
                'ordre' => 1,
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('types_depenses')
            ->where('libelle', 'الرواتب')
            ->update(['libelle' => 'Salaires']);
    }
};
