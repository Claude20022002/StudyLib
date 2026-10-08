<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Photo facultative pour illustrer un retour de stage (stockée dans MinIO, comme les documents).
 * L'application la réduit avant l'envoi ; le serveur n'accepte qu'une image de 2 Mo au plus.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('internship_reviews', function (Blueprint $table): void {
            $table->string('photo_path', 255)->nullable()->after('is_paid');
        });
    }

    public function down(): void
    {
        Schema::table('internship_reviews', function (Blueprint $table): void {
            $table->dropColumn('photo_path');
        });
    }
};
