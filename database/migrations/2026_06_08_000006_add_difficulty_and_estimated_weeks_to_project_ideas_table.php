<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_ideas', function (Blueprint $table): void {
            $table->string('difficulty', 20)->nullable();
            $table->unsignedSmallInteger('estimated_weeks')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('project_ideas', function (Blueprint $table): void {
            $table->dropColumn(['difficulty', 'estimated_weeks']);
        });
    }
};
