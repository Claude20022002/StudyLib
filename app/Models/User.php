<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasUuids, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'filiere_id',
        'year_level',
        'role',
        'avatar_path',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'year_level' => 'integer',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function filiere(): BelongsTo
    {
        return $this->belongsTo(Filiere::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(DocumentRating::class);
    }

    public function internshipReviews(): HasMany
    {
        return $this->hasMany(InternshipReview::class);
    }

    public function projectIdeas(): HasMany
    {
        return $this->hasMany(ProjectIdea::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function aiRecommendations(): HasMany
    {
        return $this->hasMany(AiRecommendation::class);
    }
}
