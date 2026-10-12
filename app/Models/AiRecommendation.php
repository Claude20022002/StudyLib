<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AiKind;
use Database\Factories\AiRecommendationFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiRecommendation extends Model
{
    /** @use HasFactory<AiRecommendationFactory> */
    use HasFactory, HasUuids, MassPrunable;

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

    /**
     * Demandes et réponses de l'IA : elles peuvent décrire un étudiant (filière, centres d'intérêt).
     * Elles ne sont gardées que le temps prévu par studylib.ai.retention_days (0 : jamais effacées).
     *
     * @return Builder<static>
     */
    public function prunable(): Builder
    {
        $days = (int) config('studylib.ai.retention_days');

        return $days > 0
            ? static::query()->where('created_at', '<', now()->subDays($days))
            : static::query()->whereRaw('1 = 0');
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
