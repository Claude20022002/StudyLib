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
            // Photo d'illustration facultative, déjà réduite par l'application (JPEG ~1600 px)
            'photo' => ['nullable', 'file', 'image', 'mimes:jpeg,png,webp', 'max:2048', 'dimensions:max_width=4096,max_height=4096'],
            // Accord explicite de l'auteur pour la publication (case de l'application et du formulaire web)
            'consent' => ['accepted'],
        ];
    }
}
