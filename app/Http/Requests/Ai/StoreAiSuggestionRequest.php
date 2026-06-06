<?php

declare(strict_types=1);

namespace App\Http\Requests\Ai;

use App\Enums\AiKind;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAiSuggestionRequest extends FormRequest
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
            'kind' => ['required', 'string', Rule::enum(AiKind::class)],
            'prompt' => ['required', 'string', 'max:4000'],
            'module_id' => ['nullable', 'uuid', 'exists:modules,id'],
        ];
    }
}
