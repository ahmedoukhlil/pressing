<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('detail_commandes', function (Blueprint $table) {
            $table->unsignedInteger('quantite_rendue')->default(0)->after('quantite');
            $table->string('statut_ligne', 20)->default('en_cours')->after('notes');
        });

        $livreIds = DB::table('commandes')->where('statut', 'livre')->pluck('id');
        if ($livreIds->isNotEmpty()) {
            DB::table('detail_commandes')
                ->whereIn('fk_id_commande', $livreIds)
                ->orderBy('id')
                ->chunkById(200, function ($lignes): void {
                    foreach ($lignes as $ligne) {
                        DB::table('detail_commandes')
                            ->where('id', $ligne->id)
                            ->update([
                                'statut_ligne' => 'livre',
                                'quantite_rendue' => (int) $ligne->quantite,
                            ]);
                    }
                });
        }

        $pretIds = DB::table('commandes')->where('statut', 'pret')->pluck('id');
        if ($pretIds->isNotEmpty()) {
            DB::table('detail_commandes')
                ->whereIn('fk_id_commande', $pretIds)
                ->where('statut_ligne', 'en_cours')
                ->update(['statut_ligne' => 'pret']);
        }
    }

    public function down(): void
    {
        Schema::table('detail_commandes', function (Blueprint $table) {
            $table->dropColumn(['quantite_rendue', 'statut_ligne']);
        });
    }
};
