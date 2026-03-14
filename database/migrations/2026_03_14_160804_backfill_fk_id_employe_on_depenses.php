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
        // Backfill depuis les avances liees a une depense.
        DB::statement("
            UPDATE depenses d
            INNER JOIN avances_salaire a ON a.fk_id_depense = d.id
            SET d.fk_id_employe = a.fk_id_employe
            WHERE d.fk_id_employe IS NULL
        ");

        // Backfill depuis la reference PAIE-{employeId}-{timestamp}.
        DB::statement("
            UPDATE depenses d
            INNER JOIN employes e
                ON e.id = CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(d.reference, '-', 2), '-', -1) AS UNSIGNED)
            SET d.fk_id_employe = e.id
            WHERE d.fk_id_employe IS NULL
              AND d.reference LIKE 'PAIE-%'
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op: migration de normalisation.
    }
};
