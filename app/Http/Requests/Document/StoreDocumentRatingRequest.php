<?php

declare(strict_types=1);

namespace App\Http\Requests\Document;

use App\Models\Document;
use Illuminate\Foundation\Http\FormRequest;

class StoreDocumentRatingRequest extends FormRequest
{
    public function authorize(): bool
    {
        $document = $this->route('document');

        if (! $document instanceof Document || $this->user() === null) {
            return false;
        }

        return $this->user()->can('view', $document);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'score' => ['required', 'integer', 'between:1,5'],
        ];
    }
}
