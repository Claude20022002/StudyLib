<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('youtube_recommendations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('module_id')
                ->constrained('modules')->cascadeOnDelete();
            $table->string('video_id', 20);
            $table->string('title', 255);
            $table->string('channel', 150)->nullable();
            $table->string('thumbnail_url', 500)->nullable();
            $table->unsignedInteger('duration')->nullable();
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamp('fetched_at')->useCurrent();
            $table->timestamps();

            $table->unique(['module_id', 'video_id']);
            $table->index(['module_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('youtube_recommendations');
    }
};
