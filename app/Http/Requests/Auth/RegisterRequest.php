<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Rules\HestimEmail;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email', new HestimEmail],
            'password' => ['required', 'confirmed', Password::defaults()],
            'filiere_id' => ['nullable', 'uuid', 'exists:filieres,id'],
            'year_level' => ['nullable', 'integer', 'between:1,5'],
        ];
    }
}
