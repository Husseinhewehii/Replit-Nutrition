<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePortionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'food_id' => 'required|exists:foods,id',
            'grams' => 'required|numeric|min:0.01|max:99999.99',
            'consumed_at' => 'nullable|date',
        ];
    }

    protected function prepareForValidation(): void
    {
        if (!$this->consumed_at) {
            $this->merge([
                'consumed_at' => now()->format('Y-m-d'),
            ]);
        }
    }
}
