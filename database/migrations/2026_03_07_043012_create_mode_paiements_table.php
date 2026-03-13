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
        Schema::create('modes_paiement', function (Blueprint $table) {
            $table->id();
            $table->string('libelle');
            $table->string('code', 30)->unique();
            $table->string('icone')->nullable();
            $table->boolean('actif')->default(true);
            $table->boolean('est_systeme')->default(false);
            $table->integer('ordre')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modes_paiement');
    }
};
