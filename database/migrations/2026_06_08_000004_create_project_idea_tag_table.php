<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_idea_tag', function (Blueprint $table) {
            $table->foreignUuid('project_idea_id')
                ->constrained('project_ideas')->cascadeOnDelete();
            $table->foreignUuid('tag_id')
                ->constrained('tags')->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['project_idea_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_idea_tag');
    }
};
