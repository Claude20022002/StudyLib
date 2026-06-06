<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_recommendations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')
                ->constrained('users')->cascadeOnDelete();
            $table->string('kind', 20)->default('other');
            $table->foreignUuid('module_id')->nullable()
                ->constrained('modules')->nullOnDelete();
            $table->text('prompt')->nullable();
            $table->json('response');
            $table->string('model', 80)->nullable();
            $table->unsignedInteger('tokens_used')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['user_id', 'created_at']);
            $table->index('kind');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_recommendations');
    }
};
