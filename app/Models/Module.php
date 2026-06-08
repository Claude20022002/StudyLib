<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ModuleFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Module extends Model
{
    /** @use HasFactory<ModuleFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'code',
        'filiere_id',
        'semester',
    ];

    protected function casts(): array
    {
        return [
            'semester' => 'integer',
        ];
    }

    public function filiere(): BelongsTo
    {
        return $this->belongsTo(Filiere::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function youtubeRecommendations(): HasMany
    {
        return $this->hasMany(YoutubeRecommendation::class);
    }

    /**
     * Étudiants inscrits à ce module (signal de personnalisation : recommandations).
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * Idées de projet rattachées à ce module (signal de matching : recommandations).
     */
    public function projectIdeas(): BelongsToMany
    {
        return $this->belongsToMany(ProjectIdea::class);
    }
}
