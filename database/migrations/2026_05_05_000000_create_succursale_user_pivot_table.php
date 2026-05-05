<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('succursale_user', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('succursale_id')->constrained('succursales')->cascadeOnDelete();
            $table->primary(['user_id', 'succursale_id']);
        });

        // Migrate existing fk_id_succursale assignments into the pivot table
        DB::statement('
            INSERT INTO succursale_user (user_id, succursale_id)
            SELECT id, fk_id_succursale FROM users
            WHERE fk_id_succursale IS NOT NULL
        ');
    }

    public function down(): void
    {
        Schema::dropIfExists('succursale_user');
    }
};
