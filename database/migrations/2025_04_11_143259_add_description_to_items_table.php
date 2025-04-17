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
        Schema::table('items', function (Blueprint $table) {
            // Ajouter la colonne description si elle n'existe pas déjà
            if (!Schema::hasColumn('items', 'description')) {
                $table->text('description')->nullable()->after('name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            // Supprimer la colonne description si elle existe
            if (Schema::hasColumn('items', 'description')) {
                $table->dropColumn('description');
            }
        });
    }
};
