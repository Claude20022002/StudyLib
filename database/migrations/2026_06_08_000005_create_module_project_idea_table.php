<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('module_project_idea', function (Blueprint $table) {
            $table->foreignUuid('module_id')
                ->constrained('modules')->cascadeOnDelete();
            $table->foreignUuid('project_idea_id')
                ->constrained('project_ideas')->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['module_id', 'project_idea_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_project_idea');
    }
};
