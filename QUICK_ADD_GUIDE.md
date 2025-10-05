# 🚀 Quick Add Feature Guide

The Quick Add feature allows you to add food portions using a simple text format, supporting both single and multiple food entries with AI-powered lookup for unknown foods.

## 📝 Format

The basic format is:
```
food_slug-grams
```

Where:
- `food_slug`: The food identifier (lowercase, underscores for spaces)
- `grams`: The amount in grams (can include decimals)

## 🎯 Examples

### Single Food
```
chicken_breast-150
rice-200
apple-100
banana-120.5
```

### Multiple Foods (Comma-separated)
```
chicken_breast-150, rice-200, apple-100
```

### Multiple Foods (Newline-separated)
```
chicken_breast-150
rice-200
apple-100
```

### Mixed Format
```
chicken_breast-150, rice-200
apple-100
banana-120
```

## 🌐 Web Interface Usage

1. **Go to Dashboard**: Navigate to the main dashboard
2. **Find Quick Add**: Look for the "Quick Add" input field
3. **Enter Foods**: Type your food entries using the format above
4. **Submit**: Click the add button

### Example Web Usage
```
Input: chicken_breast-150, rice-200, apple-100
Result: ✅ Successfully added 3 foods!
```

## 🔌 API Usage

### Single Food
```bash
curl -X POST /api/portions/quick-add \
  -H "Authorization: Bearer your-token" \
  -H "Content-Type: application/json" \
  -d '{"slug_grams": "chicken_breast-150"}'
```

### Multiple Foods
```bash
curl -X POST /api/portions/quick-add \
  -H "Authorization: Bearer your-token" \
  -H "Content-Type: application/json" \
  -d '{"slug_grams": "chicken_breast-150, rice-200, apple-100"}'
```

### JavaScript Example
```javascript
const response = await fetch('/api/portions/quick-add', {
  method: 'POST',
  headers: {
    'Authorization': 'Bearer ' + token,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    slug_grams: 'chicken_breast-150, rice-200, apple-100'
  })
});

const result = await response.json();
console.log(result.message); // "Successfully added 3 foods!"
```

## 📊 Response Format

### Success Response
```json
{
  "results": [
    {
      "portion": {
        "id": 1,
        "user_id": 1,
        "food_id": 1,
        "grams": "150.00",
        "consumed_at": "2025-01-05",
        "food": {
          "id": 1,
          "name": "Chicken Breast",
          "slug": "chicken_breast",
          "kcal_per_100g": 165
        }
      },
      "food": "Chicken Breast",
      "grams": 150,
      "source": "database"
    }
  ],
  "summary": {
    "total": 3,
    "successful": 3,
    "failed": 0,
    "ai_created": 1
  },
  "message": "Successfully added 3 foods! 1 food was automatically created with AI."
}
```

### Partial Failure Response
```json
{
  "results": [
    {
      "portion": {...},
      "food": "Chicken Breast",
      "grams": 150,
      "source": "database"
    }
  ],
  "summary": {
    "total": 3,
    "successful": 2,
    "failed": 1,
    "ai_created": 0
  },
  "errors": [
    "Unable to find nutrition information for: unknown_food"
  ],
  "message": "Some foods could not be added: Unable to find nutrition information for: unknown_food (2 foods were added successfully)"
}
```

## 🤖 AI Integration

When you add a food that doesn't exist in the database:

1. **Local Check**: System first checks the local database
2. **AI Lookup**: If not found, AI searches for nutrition information
3. **Auto-Create**: AI creates a new food entry with nutrition data
4. **Portion Added**: The portion is added to your daily intake

### AI-Created Foods
- Foods created by AI are marked with `"source": "ai"`
- They're stored in your database for future use
- You can edit them later if needed

## ⚠️ Error Handling

### Validation Errors
- **Invalid Format**: `"Invalid format: 'invalid-format'. Use: slug-grams (e.g., chicken_breast-150)"`
- **Empty Input**: `"At least one food entry is required."`

### AI Failures
- **Rate Limited**: `"AI service is temporarily rate limited. Please wait a moment and try again."`
- **API Error**: `"AI service is currently unavailable. Please try again later."`
- **Invalid Data**: `"AI was unable to provide valid nutrition data for this food item."`

### Partial Failures
- Some foods succeed, others fail
- You get detailed information about what worked and what didn't
- Successful foods are still added to your daily intake

## 🎨 Best Practices

### Food Slug Format
- Use lowercase letters
- Replace spaces with underscores
- Use descriptive names
- Examples: `chicken_breast`, `brown_rice`, `greek_yogurt`

### Batch Operations
- Use comma-separated for small batches (2-5 foods)
- Use newline-separated for larger batches
- Mix both formats as needed

### Error Recovery
- Check error messages for specific issues
- Retry failed foods individually
- Use manual food creation for problematic items

## 🔧 Troubleshooting

### Common Issues

**"Invalid format" error:**
- Check the format is `food_slug-grams`
- Ensure no extra spaces or special characters
- Verify grams is a valid number

**"Unable to find nutrition information" error:**
- Try a different food name
- Check if the food exists in the database
- Verify AI service is working

**"You do not have access to this food" error:**
- The food exists but belongs to another user
- Try creating the food manually first

### Getting Help
```bash
# Check application logs
docker compose exec app tail -f storage/logs/laravel.log

# Test AI configuration
docker compose exec app php artisan openai:test

# Run tests to verify functionality
php artisan test --filter="quick_add"
```

## 📈 Performance Tips

- **Batch Operations**: Add multiple foods at once for better efficiency
- **Common Foods**: Use foods already in the database when possible
- **AI Caching**: AI-created foods are cached for future use
- **Validation**: Check format before submitting to avoid errors

---

**Happy tracking! 🥗**
