<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Food extends Model
{
    protected $table = 'foods';

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'kcal_per_100g',
        'protein_per_100g',
        'carbs_per_100g',
        'fat_per_100g',
        'is_global',
    ];

    protected $casts = [
        'is_global' => 'boolean',
        'kcal_per_100g' => 'decimal:2',
        'protein_per_100g' => 'decimal:2',
        'carbs_per_100g' => 'decimal:2',
        'fat_per_100g' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function portions(): HasMany
    {
        return $this->hasMany(Portion::class);
    }
}
