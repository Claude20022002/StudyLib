<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_downloads', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->foreignUuid('document_id')
                ->constrained('documents')->cascadeOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('document_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_downloads');
    }
};
