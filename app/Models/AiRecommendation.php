<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AiKind;
use Database\Factories\AiRecommendationFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiRecommendation extends Model
{
    /** @use HasFactory<AiRecommendationFactory> */
    use HasFactory, HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'kind',
        'module_id',
        'prompt',
        'response',
        'model',
        'tokens_used',
    ];

    protected function casts(): array
    {
        return [
            'kind' => AiKind::class,
            'response' => 'array',
            'tokens_used' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }
}
