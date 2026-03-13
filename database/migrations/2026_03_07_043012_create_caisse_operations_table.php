<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('caisse_operations', function (Blueprint $table) {
            $table->id();
            $table->dateTime('date_operation');
            $table->decimal('montant_operation', 10, 2);
            $table->string('designation')->nullable();
            $table->foreignId('fk_id_client')->nullable()->constrained('clients')->nullOnDelete();
            $table->decimal('entree_espece', 10, 2)->default(0);
            $table->decimal('retrait_espece', 10, 2)->default(0);
            $table->foreignId('fk_id_commande')->nullable()->constrained('commandes')->nullOnDelete();
            $table->foreignId('fk_id_user')->constrained('users');
            $table->string('mode_paiement', 30)->default('especes');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('caisse_operations');
    }
};
