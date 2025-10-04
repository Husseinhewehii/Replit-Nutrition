# GitHub Actions Workflows

This directory contains GitHub Actions workflows for the Laravel Nutrition app.

## Available Workflows

### Laravel CI - MVP (`laravel.yml`)
**Simple workflow that serves as a merge gate:**
- ✅ PHPUnit tests with SQLite
- ✅ Frontend build test
- ✅ Application startup test

**Triggers:** Push/PR to main/master branches

## Setup Instructions

### 1. Required Secrets
No secrets are required! The workflow uses mocked OpenAI services for testing.

### 2. Database Setup
The workflow uses:
- SQLite in-memory database (fast and simple)
- No external database services needed

### 3. Environment Configuration
The workflows use the `docker-env-example.txt` file as a template for environment variables.

## Workflow Details

### Basic Check Workflow
```yaml
# Runs on: ubuntu-latest
# Services: MySQL 8.0
# Steps:
1. Setup PHP 8.4 + Node.js 18
2. Install dependencies
3. Setup database
4. Run tests
5. Build frontend
6. Test application startup
```

### Full CI Pipeline
```yaml
# Runs on: ubuntu-latest
# Services: MySQL 8.0 + Redis 7
# Jobs:
- test: PHPUnit tests + app startup
- cypress: E2E tests with screenshots/videos
- security: Composer audit + code style
- docker: Docker build and container test
```

## Test Results

### Success Indicators
- ✅ All PHPUnit tests pass
- ✅ Application responds to HTTP requests
- ✅ Frontend assets build successfully
- ✅ Database migrations run without errors

### Failure Handling
- **Test failures**: Check test output and fix failing tests
- **Build failures**: Check dependencies and configuration
- **Startup failures**: Check environment variables and database connection
- **Cypress failures**: Screenshots and videos are uploaded as artifacts

## Local Testing

To test the workflows locally:

```bash
# Test PHPUnit
docker compose exec app php artisan test

# Test application startup
docker compose exec app php artisan serve &
curl http://localhost:8000

# Test Cypress (if installed)
npm run cypress:run
```

## Customization

### Adding New Tests
1. Add test files to `tests/` directory
2. Tests will automatically run in the workflow

### Adding New Checks
1. Edit the workflow YAML files
2. Add new steps or jobs as needed
3. Update this README with new features

### Environment Variables
- Add new variables to `docker-env-example.txt`
- Update workflow files to use new variables
- Add secrets to GitHub repository settings

## Troubleshooting

### Common Issues

1. **Database connection fails**
   - Check MySQL service configuration
   - Verify database credentials in workflow

2. **Tests fail**
   - Check test environment setup
   - Verify all dependencies are installed

3. **Application startup fails**
   - Check environment variables
   - Verify all required services are running

4. **Cypress tests fail**
   - Check screenshots/videos in artifacts
   - Verify test data is properly seeded

### Debug Commands
```bash
# Check workflow logs
# Go to Actions tab in GitHub repository

# Test locally
docker compose exec app php artisan test --env=testing
docker compose exec app php artisan serve --env=testing
```

## Performance

- **Basic Check**: ~3-5 minutes
- **Full CI Pipeline**: ~8-12 minutes
- **Parallel Jobs**: Security and Docker tests run in parallel

## Security

- Secrets are encrypted and only available to workflows
- No sensitive data is logged
- Database is isolated per workflow run
- All dependencies are locked to specific versions
