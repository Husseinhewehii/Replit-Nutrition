# GitHub Actions Setup Guide

I've created a comprehensive GitHub Actions workflow to serve as a merge gate for your Laravel Nutrition app.

## 📁 Files Created

### Workflow File
**`.github/workflows/laravel.yml`** - **Laravel CI - Merge Gate**
- Complete CI/CD pipeline in one file
- Serves as a gate before merging
- Includes: Tests, E2E tests, Security checks, Docker Compose test

### Documentation
- **`.github/README.md`** - Detailed workflow documentation
- **`GITHUB_ACTIONS_SETUP.md`** - This setup guide

## 🚀 Quick Setup

### 1. Add Required Secret
1. Go to your GitHub repository
2. Settings → Secrets and variables → Actions
3. Add secret: `OPENAI_API_KEY` with your actual API key

### 2. Push to GitHub
```bash
git add .github/
git commit -m "Add Laravel CI merge gate workflow"
git push
```

## ✅ What the Laravel CI Workflow Checks

### Test Job
- ✅ **Tests Pass**: Runs `php artisan test` with MySQL + Redis
- ✅ **App Starts**: Starts server and checks HTTP response
- ✅ **Database**: Full MySQL setup with migrations and seeders

### Cypress E2E Job
- ✅ **E2E Tests**: Runs Cypress tests in browser
- ✅ **Visual Testing**: Screenshots and videos on failure
- ✅ **Real Environment**: Full MySQL + Redis setup

### Security Job
- ✅ **Security Audit**: Composer vulnerability check
- ✅ **Code Style**: PHP CS Fixer validation

### Docker Compose Job
- ✅ **Docker Compose**: Tests with your actual docker-compose setup
- ✅ **Real Environment**: Uses your existing Docker configuration
- ✅ **Integration Test**: Full stack test with containers

## 🎯 Your Requirements Met

✅ **Project is running**: Tests application startup with docker-compose
✅ **Tests are passing**: Runs full test suite (PHPUnit + Cypress)
✅ **Merge Gate**: Serves as a gate before merging to main/master

## 🔧 Customization

### To modify the workflow:
1. Edit `.github/workflows/laravel.yml`
2. Commit and push
3. GitHub will run the updated workflow

### To add new checks:
1. Add new jobs or steps to the workflow
2. Update this documentation
3. Test locally first

## 📊 Expected Results

### Success ✅
```
✅ Tests Pass: 112 tests, 311 assertions
✅ App Starts: HTTP 200 response with docker-compose
✅ E2E Tests: Cypress tests pass
✅ Security: No vulnerabilities found
✅ Code Style: PHP CS Fixer passes
✅ Docker Compose: Full stack integration test passes
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

# Run Cypress tests
npm run cypress:run

# Test app startup
docker compose up -d
curl http://localhost:8001
docker compose down

# Security check
composer audit
./vendor/bin/pint --test
```

## 📝 Next Steps

1. **Add the OpenAI secret** to GitHub
2. **Push to GitHub** to trigger the workflow
3. **Check the Actions tab** to see results

The workflow will run automatically on every push and pull request to your main/master branch, serving as a merge gate!
