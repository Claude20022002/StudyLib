<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\YoutubeRecommendationFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class YoutubeRecommendation extends Model
{
    /** @use HasFactory<YoutubeRecommendationFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'module_id',
        'video_id',
        'title',
        'channel',
        'thumbnail_url',
        'duration',
        'position',
        'fetched_at',
    ];

    protected function casts(): array
    {
        return [
            'duration' => 'integer',
            'position' => 'integer',
            'fetched_at' => 'datetime',
        ];
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }
}
