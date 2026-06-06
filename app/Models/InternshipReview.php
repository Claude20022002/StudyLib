<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\InternshipReviewFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InternshipReview extends Model
{
    /** @use HasFactory<InternshipReviewFactory> */
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'user_id',
        'company_id',
        'filiere_id',
        'position',
        'description',
        'rating',
        'year_level',
        'year_done',
        'is_paid',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'year_level' => 'integer',
            'year_done' => 'integer',
            'is_paid' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function filiere(): BelongsTo
    {
        return $this->belongsTo(Filiere::class);
    }
}
