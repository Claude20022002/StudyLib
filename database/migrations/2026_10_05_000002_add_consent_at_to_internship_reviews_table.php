<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Date de l'accord explicite de l'auteur pour la publication de son retour de stage
 * (nulle pour les retours antérieurs à la case d'accord).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('internship_reviews', function (Blueprint $table) {
            $table->timestamp('consent_at')->nullable()->after('is_paid');
        });
    }

    public function down(): void
    {
        Schema::table('internship_reviews', function (Blueprint $table) {
            $table->dropColumn('consent_at');
        });
    }
};
