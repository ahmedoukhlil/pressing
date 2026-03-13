<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_mouvements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fk_id_consommable')->constrained('consommables')->cascadeOnDelete();
            $table->enum('type_mouvement', ['entree', 'sortie']);
            $table->decimal('quantite', 10, 2);
            $table->dateTime('date_mouvement');
            $table->string('motif')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('fk_id_user')->constrained('users');
            $table->timestamps();
            $table->index(['fk_id_consommable', 'date_mouvement']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_mouvements');
    }
};
