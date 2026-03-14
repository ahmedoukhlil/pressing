<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('clients')
            ->select('id')
            ->orderBy('id')
            ->chunkById(200, function ($clients): void {
                foreach ($clients as $client) {
                    DB::table('clients')
                        ->where('id', $client->id)
                        ->update([
                            'code_client' => (string) $client->id,
                        ]);
                }
            });
    }

    public function down(): void
    {
        // No-op: normalization is intentionally irreversible.
    }
};
