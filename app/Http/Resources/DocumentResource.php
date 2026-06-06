<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Document */
class DocumentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'title' => $this->title,
            'description' => $this->description,
            'year_concern' => $this->year_concern,
            'status' => $this->status,
            'file_size' => $this->file_size,
            'mime_type' => $this->mime_type,
            'downloads_count' => $this->downloads_count,
            'ratings_count' => $this->ratings_count,
            'avg_rating' => $this->avg_rating,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'author' => UserPublicResource::make($this->whenLoaded('author')),
            'module' => ModuleResource::make($this->whenLoaded('module')),
        ];
    }
}
