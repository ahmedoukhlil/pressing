<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_point_wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fk_id_succursale')->constrained('succursales')->cascadeOnDelete();
            $table->foreignId('fk_id_client')->constrained('clients')->cascadeOnDelete();
            $table->unsignedInteger('solde_points')->default(0);
            $table->unsignedInteger('total_points_gagnes')->default(0);
            $table->unsignedInteger('total_points_utilises')->default(0);
            $table->timestamps();

            $table->unique(['fk_id_succursale', 'fk_id_client'], 'client_points_wallet_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_point_wallets');
    }
};

