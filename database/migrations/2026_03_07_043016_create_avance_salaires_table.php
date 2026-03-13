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
        Schema::create('avances_salaire', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fk_id_employe');
            $table->date('date_avance');
            $table->decimal('montant', 10, 2);
            $table->string('motif')->nullable();
            $table->enum('statut', ['en_cours', 'deduite', 'annulee'])->default('en_cours');
            $table->date('date_deduction')->nullable();
            $table->decimal('salaire_net_verse', 10, 2)->nullable();
            $table->foreignId('fk_id_depense')->nullable();
            $table->foreignId('fk_id_user')->constrained('users');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('avances_salaire');
    }
};
