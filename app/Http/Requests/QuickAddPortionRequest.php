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
            'quick_add' => ['required', 'string', function ($attribute, $value, $fail) {
                $lines = array_filter(array_map('trim', preg_split('/[\n\r,]+/', $value)));
                
                if (empty($lines)) {
                    $fail('At least one food entry is required.');
                    return;
                }
                
                foreach ($lines as $line) {
                    if (!preg_match('/^[a-z0-9_]+-\d+(\.\d+)?$/', $line)) {
                        $fail("Invalid format: '{$line}'. Use: slug-grams (e.g., chicken_breast-150)");
                        return;
                    }
                }
            }],
        ];
    }

    public function messages(): array
    {
        return [
            'quick_add.required' => 'Food entries are required.',
            'quick_add.string' => 'Food entries must be text.',
        ];
    }
}
