<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QuickAddPortionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'quick_add' => ['required', 'string', 'regex:/^[a-z0-9_]+-\d+(\.\d+)?$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'quick_add.regex' => 'Format should be: slug-grams (e.g., chicken_breast-150)',
        ];
    }
}
