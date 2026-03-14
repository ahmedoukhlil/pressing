<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('depenses', function (Blueprint $table) {
            if (!Schema::hasColumn('depenses', 'fk_id_employe')) {
                $table->foreignId('fk_id_employe')
                    ->nullable()
                    ->after('fk_id_fournisseur')
                    ->constrained('employes')
                    ->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('depenses', function (Blueprint $table) {
            if (Schema::hasColumn('depenses', 'fk_id_employe')) {
                $table->dropConstrainedForeignId('fk_id_employe');
            }
        });
    }
};
