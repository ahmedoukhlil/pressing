<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commandes', function (Blueprint $table) {
            $table->decimal('remise_depot_pourcentage', 5, 2)->default(0)->after('reste_a_payer');
            $table->decimal('remise_depot_montant', 10, 2)->default(0)->after('remise_depot_pourcentage');
            $table->decimal('remise_reglement_montant', 10, 2)->default(0)->after('remise_depot_montant');
            $table->decimal('total_remise', 10, 2)->default(0)->after('remise_reglement_montant');
        });
    }

    public function down(): void
    {
        Schema::table('commandes', function (Blueprint $table) {
            $table->dropColumn([
                'remise_depot_pourcentage',
                'remise_depot_montant',
                'remise_reglement_montant',
                'total_remise',
            ]);
        });
    }
};

