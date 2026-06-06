<?php

declare(strict_types=1);

namespace App\Http\Requests\Internship;

use Illuminate\Foundation\Http\FormRequest;

class StoreInternshipReviewRequest extends FormRequest
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
            'company_name' => ['required', 'string', 'max:150'],
            'company_city' => ['nullable', 'string', 'max:100'],
            'company_sector' => ['nullable', 'string', 'max:100'],
            'filiere_id' => ['nullable', 'uuid', 'exists:filieres,id'],
            'position' => ['nullable', 'string', 'max:150'],
            'description' => ['required', 'string'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'year_level' => ['nullable', 'integer', 'between:1,5'],
            'year_done' => ['nullable', 'integer', 'between:2000,2100'],
            'is_paid' => ['boolean'],
        ];
    }
}
