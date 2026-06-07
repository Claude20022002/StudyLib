<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DocumentStatus;
use App\Enums\DocumentType;
use Database\Factories\DocumentFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    /** @use HasFactory<DocumentFactory> */
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'user_id',
        'module_id',
        'type',
        'title',
        'description',
        'file_path',
        'file_size',
        'mime_type',
        'year_concern',
        'status',
    ];

    protected $hidden = [
        'file_path',
    ];

    protected function casts(): array
    {
        return [
            'type' => DocumentType::class,
            'status' => DocumentStatus::class,
            'file_size' => 'integer',
            'year_concern' => 'integer',
            'downloads_count' => 'integer',
            'ratings_count' => 'integer',
            'avg_rating' => 'float',
        ];
    }

    /** @param Builder<Document> $query */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('status', DocumentStatus::Approved);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(DocumentRating::class);
    }

    public function downloads(): HasMany
    {
        return $this->hasMany(DocumentDownload::class);
    }

    public function fileBasename(): string
    {
        if (blank($this->file_path)) {
            return 'document';
        }

        return basename($this->file_path);
    }

    public function fileKindLabel(): string
    {
        $mime = $this->mime_type ?? '';

        if (str_contains($mime, 'pdf')) {
            return 'PDF';
        }

        if (str_contains($mime, 'word') || str_contains($mime, 'document')) {
            return 'DOCX';
        }

        if (str_contains($mime, 'presentation') || str_contains($mime, 'powerpoint')) {
            return 'SLIDES';
        }

        $extension = strtoupper(pathinfo($this->file_path, PATHINFO_EXTENSION));

        return $extension !== '' ? $extension : 'FILE';
    }

    public function thumbColors(): array
    {
        return match ($this->type) {
            DocumentType::Cours => ['bg' => 'var(--color-primary-soft)', 'text' => 'var(--color-primary-active)'],
            DocumentType::Examen => ['bg' => 'var(--color-warning-soft)', 'text' => 'var(--color-warning-ink)'],
            DocumentType::Td, DocumentType::Tp => ['bg' => 'var(--color-success-soft)', 'text' => 'var(--color-success-ink)'],
        };
    }
}
