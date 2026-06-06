<?php

declare(strict_types=1);

namespace App\Http\Requests\Project;

use App\Enums\StudyLevel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectIdeaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:200'],
            'description' => ['required', 'string'],
            'filiere_id' => ['nullable', 'uuid', 'exists:filieres,id'],
            'level' => ['required', 'string', Rule::enum(StudyLevel::class)],
            'repo_url' => ['nullable', 'url', 'max:500'],
        ];
    }
}
