<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 150);
            $table->string('code', 30);
            $table->foreignUuid('filiere_id')
                ->constrained('filieres')->cascadeOnUpdate()->restrictOnDelete();
            $table->unsignedTinyInteger('semester');
            $table->timestamps();

            $table->unique(['filiere_id', 'code']);
            $table->index(['filiere_id', 'semester']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};
