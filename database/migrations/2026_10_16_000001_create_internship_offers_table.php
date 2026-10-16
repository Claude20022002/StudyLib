<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Offres de stage partagées par un lien (lot S-1, étendu aux étudiants) : l'offre est recopiée au
 * moment du partage (le site d'origine peut la changer ou la retirer) avec son lien pour postuler.
 *  - type : stage, emploi ou alternance ;
 *  - source : « page » (lue sur le site), « texte » (texte collé, mis en forme par l'IA), « manuel » ;
 *  - status : comme les documents (un étudiant passe par la modération, un enseignant non) ;
 *  - expires_at : le lendemain de la date limite, ou 60 jours après le partage sans date limite.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('internship_offers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title', 200);
            $table->string('company_name', 150);
            $table->string('city', 120)->nullable();
            $table->string('type', 20)->default('stage');
            $table->date('deadline')->nullable();
            $table->string('salary', 150)->nullable();
            $table->text('description')->nullable();
            $table->string('apply_url', 2048);
            $table->string('source', 10)->default('manuel');
            $table->string('status', 20)->default('pending')->index();
            $table->timestamp('expires_at')->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('internship_offers');
    }
};
