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
        Schema::create('types_depenses', function (Blueprint $table) {
            $table->id();
            $table->string('libelle');
            $table->string('icone')->nullable();
            $table->string('couleur', 7)->default('#6B7280');
            $table->boolean('actif')->default(true);
            $table->integer('ordre')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('types_depenses');
    }
};
