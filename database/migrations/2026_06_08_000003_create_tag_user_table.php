<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tag_user', function (Blueprint $table) {
            $table->foreignUuid('user_id')
                ->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('tag_id')
                ->constrained('tags')->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['user_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tag_user');
    }
};
