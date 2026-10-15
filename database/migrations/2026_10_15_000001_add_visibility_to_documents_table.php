<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Portée d'un document : « school » (la Bibliothèque, toute l'école) ou « class » (déposé
 * depuis l'espace d'une classe dans Planner, invisible dans la Bibliothèque). Les documents
 * existants restent dans la Bibliothèque.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->string('visibility', 10)->default('school')->after('status')->index();
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropIndex(['visibility']);
            $table->dropColumn('visibility');
        });
    }
};
