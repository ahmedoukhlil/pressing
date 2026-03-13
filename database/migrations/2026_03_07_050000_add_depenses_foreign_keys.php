<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('depenses', function (Blueprint $table) {
            $table->foreign('fk_id_type_depense')->references('id')->on('types_depenses')->restrictOnDelete();
            $table->foreign('fk_id_fournisseur')->references('id')->on('fournisseurs')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('depenses', function (Blueprint $table) {
            $table->dropForeign(['fk_id_type_depense']);
            $table->dropForeign(['fk_id_fournisseur']);
        });
    }
};
