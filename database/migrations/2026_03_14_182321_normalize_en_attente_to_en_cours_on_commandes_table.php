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
        if (!Schema::hasTable('commandes')) {
            return;
        }

        DB::table('commandes')
            ->where('statut', 'en_attente')
            ->update(['statut' => 'en_cours']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op: normalization intentionally irreversible.
    }
};
