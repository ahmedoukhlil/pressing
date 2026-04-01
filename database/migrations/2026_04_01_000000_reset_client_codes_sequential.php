<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $clients = DB::table('clients')->orderBy('id')->pluck('id');

        foreach ($clients as $index => $id) {
            $code = str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT);
            DB::table('clients')->where('id', $id)->update(['code_client' => $code]);
        }
    }

    public function down(): void
    {
        // Restaurer les codes à leur valeur originale (id sous forme de string)
        DB::statement('UPDATE clients SET code_client = CAST(id AS CHAR)');
    }
};
