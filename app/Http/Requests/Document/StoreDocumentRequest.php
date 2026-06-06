<?php

declare(strict_types=1);

namespace App\Http\Requests\Document;

use App\Enums\DocumentType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDocumentRequest extends FormRequest
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
            'module_id' => ['required', 'uuid', 'exists:modules,id'],
            'type' => ['required', 'string', Rule::enum(DocumentType::class)],
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'year_concern' => ['nullable', 'integer', 'between:2000,2100'],
            'file' => ['required', 'file', 'max:20480'],
        ];
    }
}
