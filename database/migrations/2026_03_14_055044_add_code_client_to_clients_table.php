<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('code_client', 20)->nullable()->after('id');
        });

        DB::table('clients')
            ->select('id')
            ->orderBy('id')
            ->chunkById(200, function ($clients): void {
                foreach ($clients as $client) {
                    DB::table('clients')
                        ->where('id', $client->id)
                        ->update([
                            'code_client' => sprintf('CL%06d', (int) $client->id),
                        ]);
                }
            });

        Schema::table('clients', function (Blueprint $table) {
            $table->unique('code_client');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropUnique('clients_code_client_unique');
            $table->dropColumn('code_client');
        });
    }
};
