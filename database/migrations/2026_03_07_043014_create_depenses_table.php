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
        Schema::create('depenses', function (Blueprint $table) {
            $table->id();
            $table->date('date_depense');
            $table->foreignId('fk_id_type_depense');
            $table->string('designation');
            $table->decimal('montant', 10, 2);
            $table->string('mode_paiement', 30)->default('especes');
            $table->foreignId('fk_id_fournisseur')->nullable();
            $table->string('reference')->nullable();
            $table->enum('statut', ['validee', 'annulee'])->default('validee');
            $table->text('notes')->nullable();
            $table->foreignId('fk_id_user')->constrained('users');
            $table->index(['date_depense', 'fk_id_type_depense']);
            $table->index('mode_paiement');
            $table->index('statut');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('depenses');
    }
};
