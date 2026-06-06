<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Filiere extends Model
{
    /** @use HasFactory<\Database\Factories\FiliereFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'code',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function modules(): HasMany
    {
        return $this->hasMany(Module::class);
    }

    public function projectIdeas(): HasMany
    {
        return $this->hasMany(ProjectIdea::class);
    }

    public function internshipReviews(): HasMany
    {
        return $this->hasMany(InternshipReview::class);
    }
}
