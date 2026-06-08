<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('module_user', function (Blueprint $table) {
            $table->foreignUuid('user_id')
                ->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('module_id')
                ->constrained('modules')->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['user_id', 'module_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_user');
    }
};
