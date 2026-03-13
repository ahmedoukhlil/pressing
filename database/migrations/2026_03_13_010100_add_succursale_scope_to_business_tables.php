<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $mainBranchId = DB::table('succursales')->insertGetId([
            'nom' => 'Principale',
            'code' => 'PRINCIPALE',
            'actif' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'fk_id_succursale')) {
                $table->foreignId('fk_id_succursale')->nullable()->after('password')->constrained('succursales')->nullOnDelete();
            }
        });

        Schema::table('clients', function (Blueprint $table) {
            if (!Schema::hasColumn('clients', 'fk_id_succursale')) {
                $table->foreignId('fk_id_succursale')->nullable()->after('id')->constrained('succursales')->cascadeOnDelete();
            }
        });

        Schema::table('commandes', function (Blueprint $table) {
            if (!Schema::hasColumn('commandes', 'fk_id_succursale')) {
                $table->foreignId('fk_id_succursale')->nullable()->after('id')->constrained('succursales')->cascadeOnDelete();
            }
        });

        Schema::table('depenses', function (Blueprint $table) {
            if (!Schema::hasColumn('depenses', 'fk_id_succursale')) {
                $table->foreignId('fk_id_succursale')->nullable()->after('id')->constrained('succursales')->cascadeOnDelete();
            }
        });

        Schema::table('caisse_operations', function (Blueprint $table) {
            if (!Schema::hasColumn('caisse_operations', 'fk_id_succursale')) {
                $table->foreignId('fk_id_succursale')->nullable()->after('id')->constrained('succursales')->cascadeOnDelete();
            }
        });

        DB::table('users')->whereNull('fk_id_succursale')->update(['fk_id_succursale' => $mainBranchId]);
        DB::table('clients')->whereNull('fk_id_succursale')->update(['fk_id_succursale' => $mainBranchId]);
        DB::table('commandes')->whereNull('fk_id_succursale')->update(['fk_id_succursale' => $mainBranchId]);
        DB::table('depenses')->whereNull('fk_id_succursale')->update(['fk_id_succursale' => $mainBranchId]);
        DB::table('caisse_operations')->whereNull('fk_id_succursale')->update(['fk_id_succursale' => $mainBranchId]);

        Schema::table('clients', function (Blueprint $table) {
            try {
                $table->dropUnique('clients_telephone_unique');
            } catch (\Throwable $e) {
                // Ignore if index already removed.
            }
            $table->unique(['fk_id_succursale', 'telephone'], 'clients_succursale_telephone_unique');
        });

        Schema::table('commandes', function (Blueprint $table) {
            try {
                $table->dropUnique('commandes_numero_commande_unique');
            } catch (\Throwable $e) {
            }
            try {
                $table->dropUnique('commandes_annee_commande_n_ordre_unique');
            } catch (\Throwable $e) {
            }
            $table->unique(['fk_id_succursale', 'numero_commande'], 'commandes_succursale_numero_unique');
            $table->unique(['fk_id_succursale', 'annee_commande', 'n_ordre'], 'commandes_succursale_annee_ordre_unique');
        });
    }

    public function down(): void
    {
        Schema::table('commandes', function (Blueprint $table) {
            try {
                $table->dropUnique('commandes_succursale_numero_unique');
                $table->dropUnique('commandes_succursale_annee_ordre_unique');
            } catch (\Throwable $e) {
            }
            $table->unique('numero_commande');
            $table->unique(['annee_commande', 'n_ordre']);
        });

        Schema::table('clients', function (Blueprint $table) {
            try {
                $table->dropUnique('clients_succursale_telephone_unique');
            } catch (\Throwable $e) {
            }
            $table->unique('telephone');
        });

        Schema::table('caisse_operations', function (Blueprint $table) {
            if (Schema::hasColumn('caisse_operations', 'fk_id_succursale')) {
                $table->dropConstrainedForeignId('fk_id_succursale');
            }
        });
        Schema::table('depenses', function (Blueprint $table) {
            if (Schema::hasColumn('depenses', 'fk_id_succursale')) {
                $table->dropConstrainedForeignId('fk_id_succursale');
            }
        });
        Schema::table('commandes', function (Blueprint $table) {
            if (Schema::hasColumn('commandes', 'fk_id_succursale')) {
                $table->dropConstrainedForeignId('fk_id_succursale');
            }
        });
        Schema::table('clients', function (Blueprint $table) {
            if (Schema::hasColumn('clients', 'fk_id_succursale')) {
                $table->dropConstrainedForeignId('fk_id_succursale');
            }
        });
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'fk_id_succursale')) {
                $table->dropConstrainedForeignId('fk_id_succursale');
            }
        });

        DB::table('succursales')->where('code', 'PRINCIPALE')->delete();
    }
};

