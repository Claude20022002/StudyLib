<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_ratings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')
                ->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('document_id')
                ->constrained('documents')->cascadeOnDelete();
            $table->unsignedTinyInteger('score');
            $table->timestamps();

            $table->unique(['user_id', 'document_id']);
            $table->index('document_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_ratings');
    }
};
