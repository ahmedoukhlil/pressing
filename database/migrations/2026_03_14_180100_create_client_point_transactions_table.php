<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_point_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fk_id_succursale')->constrained('succursales')->cascadeOnDelete();
            $table->foreignId('fk_id_client')->constrained('clients')->cascadeOnDelete();
            $table->foreignId('fk_id_commande')->nullable()->constrained('commandes')->nullOnDelete();
            $table->foreignId('fk_id_caisse_operation')->nullable()->constrained('caisse_operations')->nullOnDelete();
            $table->foreignId('fk_id_user')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('type', ['gain', 'utilisation', 'ajustement', 'annulation']);
            $table->unsignedInteger('points');
            $table->decimal('valeur_mru', 10, 2)->default(0);
            $table->string('reference_unique')->nullable()->unique();
            $table->string('note')->nullable();
            $table->timestamps();

            $table->index(['fk_id_client', 'created_at'], 'client_points_tx_client_created_idx');
            $table->index(['fk_id_succursale', 'type'], 'client_points_tx_succursale_type_idx');
            $table->unique(['fk_id_caisse_operation', 'type'], 'client_points_tx_caisse_type_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_point_transactions');
    }
};

