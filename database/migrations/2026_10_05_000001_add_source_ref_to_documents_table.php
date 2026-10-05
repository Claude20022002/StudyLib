<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Origine d'un document importé (« drive:<id du fichier> ») : un import relancé ne crée
 * pas de doublon, et un document supprimé par la modération n'est pas réimporté.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->string('source_ref', 191)->nullable()->unique()->after('file_path');
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropUnique(['source_ref']);
            $table->dropColumn('source_ref');
        });
    }
};
