<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateFoodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('food'));
    }

    public function rules(): array
    {
        $foodId = $this->route('food')->id;

        return [
            'name' => 'required|string|max:255',
            'slug' => [
                'nullable',
                'string',
                'regex:/^[a-z0-9_]+$/',
                Rule::unique('foods', 'slug')->ignore($foodId),
            ],
            'kcal_per_100g' => 'required|numeric|min:0|max:9999.99',
            'protein_per_100g' => 'required|numeric|min:0|max:999.99',
            'carbs_per_100g' => 'required|numeric|min:0|max:999.99',
            'fat_per_100g' => 'required|numeric|min:0|max:999.99',
        ];
    }

    protected function prepareForValidation(): void
    {
        if (!$this->slug && $this->name) {
            $this->merge([
                'slug' => $this->generateSlug($this->name),
            ]);
        }
    }

    private function generateSlug(string $name): string
    {
        $slug = Str::lower($name);
        $slug = preg_replace('/[^a-z0-9_]/', '_', $slug);
        $slug = preg_replace('/_+/', '_', $slug);
        $slug = trim($slug, '_');
        
        return $slug;
    }
}
