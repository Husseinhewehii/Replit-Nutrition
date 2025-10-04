# GitHub Actions Setup Guide

I've created GitHub Actions workflows to check if your project is running and tests are passing.

## 📁 Files Created

### Workflow Files
1. **`.github/workflows/simple-check.yml`** - ⭐ **RECOMMENDED**
   - Simple and fast
   - Uses SQLite in-memory database
   - Checks: Tests pass + App starts

2. **`.github/workflows/basic-check.yml`** - Full database setup
   - Uses MySQL service
   - More realistic environment
   - Checks: Tests pass + App starts

3. **`.github/workflows/ci.yml`** - Complete CI/CD pipeline
   - Includes Cypress E2E tests
   - Security checks
   - Docker build test
   - Multiple parallel jobs

### Documentation
- **`.github/README.md`** - Detailed workflow documentation
- **`GITHUB_ACTIONS_SETUP.md`** - This setup guide

## 🚀 Quick Setup

### 1. Choose Your Workflow
I recommend starting with **`simple-check.yml`** - it's fast and covers your requirements.

### 2. Add Required Secret
1. Go to your GitHub repository
2. Settings → Secrets and variables → Actions
3. Add secret: `OPENAI_API_KEY` with your actual API key

### 3. Push to GitHub
```bash
git add .github/
git commit -m "Add GitHub Actions workflows"
git push
```

## ✅ What Each Workflow Checks

### Simple Check (Recommended)
- ✅ **Tests Pass**: Runs `php artisan test`
- ✅ **App Starts**: Starts server and checks HTTP response
- ✅ **Fast**: Uses SQLite in-memory database (~2-3 minutes)

### Basic Check
- ✅ **Tests Pass**: Runs `php artisan test` with MySQL
- ✅ **App Starts**: Starts server and checks HTTP response
- ✅ **Realistic**: Uses MySQL service (~3-5 minutes)

### Full CI Pipeline
- ✅ **Tests Pass**: PHPUnit + Cypress E2E tests
- ✅ **App Starts**: Multiple startup tests
- ✅ **Security**: Composer audit + code style
- ✅ **Docker**: Build and container test
- ✅ **Comprehensive**: (~8-12 minutes)

## 🎯 Your Requirements Met

✅ **Project is running**: All workflows test application startup
✅ **Tests are passing**: All workflows run the test suite

## 🔧 Customization

### To use a different workflow:
1. Delete the workflows you don't want
2. Keep only the one you prefer
3. Push to GitHub

### To modify a workflow:
1. Edit the `.yml` file
2. Commit and push
3. GitHub will run the updated workflow

## 📊 Expected Results

### Success ✅
```
✅ Tests Pass: 112 tests, 311 assertions
✅ App Starts: HTTP 200 response
✅ Build Success: Frontend assets built
```

### Failure ❌
- Check the Actions tab in GitHub for detailed logs
- Fix any failing tests or configuration issues
- Push again to re-run

## 🛠️ Local Testing

Test the same checks locally:

```bash
# Run tests
docker compose exec app php artisan test

# Test app startup
docker compose exec app php artisan serve &
curl http://localhost:8000
```

## 📝 Next Steps

1. **Choose your workflow** (I recommend `simple-check.yml`)
2. **Add the OpenAI secret** to GitHub
3. **Push to GitHub** to trigger the workflow
4. **Check the Actions tab** to see results

The workflow will run automatically on every push and pull request to your main/master branch!
