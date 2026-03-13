<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consommables', function (Blueprint $table) {
            $table->id();
            $table->string('libelle')->unique();
            $table->string('unite', 30)->default('unite');
            $table->decimal('stock_actuel', 10, 2)->default(0);
            $table->decimal('seuil_alerte', 10, 2)->default(0);
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consommables');
    }
};
