<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('internship_reviews', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->foreignUuid('company_id')
                ->constrained('companies')->cascadeOnDelete();
            $table->foreignUuid('filiere_id')->nullable()
                ->constrained('filieres')->nullOnDelete();
            $table->string('position', 150)->nullable();
            $table->text('description');
            $table->unsignedTinyInteger('rating');
            $table->unsignedTinyInteger('year_level')->nullable();
            $table->unsignedSmallInteger('year_done')->nullable();
            $table->boolean('is_paid')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index('company_id');
            $table->index(['filiere_id', 'year_done']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('internship_reviews');
    }
};
