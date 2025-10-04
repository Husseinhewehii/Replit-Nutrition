<?php

namespace App\Services;

use OpenAI\Laravel\Facades\OpenAI;
use App\Services\FoodService;
use Illuminate\Support\Str;

class AiFoodLookupService
{
    public function __construct(
        protected FoodService $foodService
    ) {}

    public function findOrCreateFood(string $slug, ?int $userId = null): ?array
    {
        $food = $this->foodService->findBySlug($slug, $userId);
        
        if ($food) {
            return [
                'food' => $food,
                'source' => 'database'
            ];
        }

        if (!$userId) {
            return null;
        }

        $aiResult = $this->lookupFoodWithAi($slug);
        
        if ($aiResult && isset($aiResult['food'])) {
            $createdFood = $this->foodService->createFood($aiResult['food'], $userId);
            return [
                'food' => $createdFood,
                'source' => 'ai'
            ];
        }

        // Return error information if AI failed
        if ($aiResult && isset($aiResult['error'])) {
            return [
                'error' => $aiResult['error'],
                'error_type' => 'ai_failure'
            ];
        }

        return null;
    }

    protected function lookupFoodWithAi(string $slug): ?array
    {
        try {
            $foodName = str_replace('_', ' ', $slug);

            $response = OpenAI::chat()->create([
                'model' => 'gpt-4o-mini',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a nutrition expert. Provide nutritional information per 100g for foods. Respond ONLY with valid JSON in this exact format: {"name": "food name", "kcal_per_100g": number, "protein_per_100g": number, "carbs_per_100g": number, "fat_per_100g": number}. Use standard USDA nutrition data values.'
                    ],
                    [
                        'role' => 'user',
                        'content' => "Provide nutritional information per 100g for: {$foodName}"
                    ]
                ],
                'max_tokens' => 200,
                'temperature' => 0.3
            ]);

            $content = $response->choices[0]->message->content;
            
            $content = preg_replace('/```json\s*/', '', $content);
            $content = preg_replace('/```\s*/', '', $content);
            $content = trim($content);

            $nutritionData = json_decode($content, true);

            if (!$nutritionData || !isset($nutritionData['kcal_per_100g'])) {
                \Log::error('AI Food Lookup failed - Invalid response format', [
                    'slug' => $slug,
                    'response_content' => $content
                ]);
                return [
                    'error' => 'AI was unable to provide valid nutrition data for this food item.'
                ];
            }

            return [
                'food' => [
                    'name' => $nutritionData['name'] ?? $foodName,
                    'slug' => $slug,
                    'kcal_per_100g' => $nutritionData['kcal_per_100g'],
                    'protein_per_100g' => $nutritionData['protein_per_100g'],
                    'carbs_per_100g' => $nutritionData['carbs_per_100g'],
                    'fat_per_100g' => $nutritionData['fat_per_100g'],
                ]
            ];

        } catch (\Exception $e) {
            \Log::error('AI Food Lookup failed', [
                'slug' => $slug,
                'error' => $e->getMessage(),
                'exception_class' => get_class($e)
            ]);
            return [
                'error' => 'AI service is currently unavailable. Please try again later.'
            ];
        }
    }
}
