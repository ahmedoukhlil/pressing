<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fk_id_succursale');
            $table->date('date_pret');
            $table->string('preteur');
            $table->decimal('montant', 10, 2);
            $table->string('mode_paiement');
            $table->decimal('montant_rembourse', 10, 2)->default(0);
            $table->string('statut')->default('en_cours'); // en_cours | solde
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('fk_id_user');
            $table->timestamps();

            $table->foreign('fk_id_succursale')->references('id')->on('succursales')->onDelete('cascade');
            $table->foreign('fk_id_user')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prets');
    }
};
