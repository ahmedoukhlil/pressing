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
        Schema::create('commandes', function (Blueprint $table) {
            $table->id();
            $table->string('numero_commande')->unique();
            $table->integer('annee_commande');
            $table->integer('n_ordre');
            $table->foreignId('fk_id_client')->constrained('clients')->restrictOnDelete();
            $table->dateTime('date_depot');
            $table->dateTime('date_livraison_prevue')->nullable();
            $table->dateTime('date_livraison_reelle')->nullable();
            $table->enum('statut', ['en_attente', 'en_cours', 'pret', 'livre', 'annule'])->default('en_attente');
            $table->decimal('montant_total', 10, 2)->default(0);
            $table->decimal('montant_paye', 10, 2)->default(0);
            $table->decimal('reste_a_payer', 10, 2)->default(0);
            $table->enum('mode_reglement', ['especes', 'carte', 'virement', 'non_paye'])->default('non_paye');
            $table->boolean('est_paye')->default(false);
            $table->text('notes')->nullable();
            $table->foreignId('fk_id_user')->constrained('users');
            $table->unique(['annee_commande', 'n_ordre']);
            $table->index(['fk_id_client', 'created_at']);
            $table->index(['statut', 'created_at']);
            $table->index('date_depot');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commandes');
    }
};
