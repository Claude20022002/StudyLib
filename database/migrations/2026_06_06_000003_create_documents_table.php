<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->nullable()
                ->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignUuid('module_id')
                ->constrained('modules')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('type', 20);
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->string('file_path', 500);
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('mime_type', 120)->nullable();
            $table->unsignedSmallInteger('year_concern')->nullable();
            $table->string('status', 20)->default('pending');
            $table->unsignedInteger('downloads_count')->default(0);
            $table->unsignedInteger('ratings_count')->default(0);
            $table->decimal('avg_rating', 2, 1)->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['module_id', 'type']);
            $table->index('user_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
