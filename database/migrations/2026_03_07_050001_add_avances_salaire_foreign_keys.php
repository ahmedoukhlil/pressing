<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('avances_salaire', function (Blueprint $table) {
            $table->foreign('fk_id_employe')->references('id')->on('employes')->restrictOnDelete();
            $table->foreign('fk_id_depense')->references('id')->on('depenses')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('avances_salaire', function (Blueprint $table) {
            $table->dropForeign(['fk_id_employe']);
            $table->dropForeign(['fk_id_depense']);
        });
    }
};
