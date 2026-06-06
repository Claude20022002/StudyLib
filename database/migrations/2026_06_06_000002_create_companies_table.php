<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 150);
            $table->string('city', 100)->nullable();
            $table->string('sector', 100)->nullable();
            $table->timestamps();

            $table->unique(['name', 'city']);
            $table->index('sector');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
