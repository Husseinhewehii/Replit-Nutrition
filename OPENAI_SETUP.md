# OpenAI Configuration Setup Guide

Simple setup guide for OpenAI integration in the Laravel Nutrition app.

## Quick Setup (2 steps)

### 1. Get Your OpenAI API Key
1. Go to [OpenAI Platform](https://platform.openai.com/account/api-keys)
2. Sign in and click "Create new secret key"
3. Copy the API key (starts with `sk-`)

### 2. Update Your .env File
Add these lines to your `.env` file:

```env
# OpenAI Configuration
OPENAI_API_KEY=sk-your_actual_api_key_here
OPENAI_ORGANIZATION=
```

**That's it!** You only need these two lines.

## 3. Test Your Configuration

After updating your `.env` file:

```bash
# Restart Docker containers
docker compose down
docker compose up -d

# Test the configuration
docker compose exec app php artisan openai:test
```

**Expected result:**
```
✅ API key is configured
✅ Successfully connected to OpenAI!
🎉 OpenAI configuration is working perfectly!
```

## 4. How to Use

Once configured, you can use AI features:

1. **Quick Add Food**: Go to dashboard and use format like `salmon-200`
2. **API Usage**: POST to `/api/portions/quick-add` with `{"slug_grams": "salmon-200"}`

The AI will automatically:
- Look up nutrition information for the food
- Create a new food entry in your database
- Add the portion to your daily intake

## 5. Troubleshooting

**"Organization header should match" error:**
- Add `OPENAI_ORGANIZATION=` to your `.env` file (empty value)

**"Incorrect API key" error:**
- Check your API key is correct and starts with `sk-`

**"Rate limit exceeded":**
- Wait a moment and try again

**Need help?**
```bash
# Test your configuration
docker compose exec app php artisan openai:test

# Check logs for errors
docker compose exec app tail -f storage/logs/laravel.log
```
