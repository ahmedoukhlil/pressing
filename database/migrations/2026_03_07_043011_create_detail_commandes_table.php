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
        Schema::create('detail_commandes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fk_id_commande')->constrained('commandes')->cascadeOnDelete();
            $table->foreignId('fk_id_service')->constrained('services')->restrictOnDelete();
            $table->decimal('prix_unitaire', 10, 2);
            $table->unsignedInteger('quantite')->default(1);
            $table->decimal('sous_total', 10, 2);
            $table->string('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_commandes');
    }
};
