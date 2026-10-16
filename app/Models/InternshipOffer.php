<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DocumentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Offre de stage (ou d'emploi, d'alternance) partagée par un lien. Recopiée au partage ; visible de
 * toute l'école une fois approuvée et tant qu'elle n'a pas expiré.
 */
class InternshipOffer extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    public const TYPES = ['stage', 'emploi', 'alternance'];

    protected $fillable = ['user_id', 'title', 'company_name', 'city', 'type', 'deadline', 'salary', 'description', 'apply_url', 'source', 'status', 'expires_at'];

    protected function casts(): array
    {
        return [
            'status' => DocumentStatus::class,
            'deadline' => 'date',
            'expires_at' => 'datetime',
        ];
    }

    /** @param  Builder<InternshipOffer>  $query */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('status', DocumentStatus::Approved)->where('expires_at', '>', now());
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
