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
        Schema::create('employes', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('prenom')->nullable();
            $table->string('telephone', 20)->nullable()->unique();
            $table->foreignId('fk_id_poste')->nullable()->constrained('postes')->nullOnDelete();
            $table->date('date_embauche')->nullable();
            $table->decimal('salaire_brut', 10, 2);
            $table->boolean('actif')->default(true);
            $table->string('piece_identite_recto')->nullable();
            $table->string('piece_identite_verso')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employes');
    }
};
