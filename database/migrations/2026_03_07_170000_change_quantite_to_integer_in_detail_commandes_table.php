<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement('ALTER TABLE detail_commandes MODIFY quantite INT UNSIGNED NOT NULL DEFAULT 1');
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement('ALTER TABLE detail_commandes MODIFY quantite DECIMAL(10,2) NOT NULL DEFAULT 1.00');
    }
};

