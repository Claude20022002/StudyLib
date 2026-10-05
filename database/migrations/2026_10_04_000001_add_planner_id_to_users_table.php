<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Connexion unique avec HESTIM Planner : identifiant du compte Planner (revendication `sub`).
 * Un compte StudyLib suit ainsi la personne même si son email change dans Planner.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('planner_id', 36)->nullable()->unique()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['planner_id']);
            $table->dropColumn('planner_id');
        });
    }
};
