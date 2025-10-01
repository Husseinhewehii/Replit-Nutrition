<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Portion extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'food_id',
        'grams',
        'consumed_at',
    ];

    protected $casts = [
        'grams' => 'decimal:2',
        'consumed_at' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function food(): BelongsTo
    {
        return $this->belongsTo(Food::class);
    }
}
