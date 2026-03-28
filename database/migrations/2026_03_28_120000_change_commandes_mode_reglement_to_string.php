<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Libère mode_reglement pour accepter tout code présent dans modes_paiement (hors ENUM fixe).
     */
    public function up(): void
    {
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        DB::statement("ALTER TABLE commandes MODIFY mode_reglement VARCHAR(30) NOT NULL DEFAULT 'non_paye'");
    }

    public function down(): void
    {
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        DB::statement("ALTER TABLE commandes MODIFY mode_reglement ENUM('especes','carte','virement','non_paye') NOT NULL DEFAULT 'non_paye'");
    }
};
