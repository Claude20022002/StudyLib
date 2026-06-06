<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\IdeaSource;
use App\Enums\StudyLevel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectIdea extends Model
{
    /** @use HasFactory<\Database\Factories\ProjectIdeaFactory> */
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'user_id',
        'filiere_id',
        'title',
        'description',
        'level',
        'source',
        'repo_url',
    ];

    protected function casts(): array
    {
        return [
            'level' => StudyLevel::class,
            'source' => IdeaSource::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function filiere(): BelongsTo
    {
        return $this->belongsTo(Filiere::class);
    }
}
