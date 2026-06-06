<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_ideas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->foreignUuid('filiere_id')->nullable()
                ->constrained('filieres')->nullOnDelete();
            $table->string('title', 200);
            $table->text('description');
            $table->string('level', 5);
            $table->string('source', 20)->default('student');
            $table->string('repo_url', 500)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['filiere_id', 'level']);
            $table->index('source');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_ideas');
    }
};
