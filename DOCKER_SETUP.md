# Docker Setup for Laravel Nutrition App

This Docker configuration provides a complete development environment with:
- **Nginx** (web server) - Port 8001
- **PHP-FPM 8.4** (Laravel application)
- **MySQL 8.0** (database) - Port 3306
- **phpMyAdmin** (database management) - Port 8002
- **Redis** (caching) - Port 6379

## Prerequisites

- Docker and Docker Compose installed
- Git repository cloned

## Quick Start

1. **Copy environment file:**
   ```bash
   cp docker-env-example.txt .env
   ```

2. **Generate application key:**
   ```bash
   # First time setup - generate a new Laravel application key
   php artisan key:generate
   # Copy the generated key to your .env file
   ```

3. **Build and start containers:**
   ```bash
   docker-compose up -d --build
   ```

4. **Install dependencies:**
   ```bash
   docker-compose exec app composer install
   ```

5. **Run migrations:**
   ```bash
   docker-compose exec app php artisan migrate
   ```

6. **Set permissions:**
   ```bash
   docker-compose exec app chown -R www:www /var/www/html
   docker-compose exec app chmod -R 755 /var/www/html/storage
   docker-compose exec app chmod -R 755 /var/www/html/bootstrap/cache
   ```

## Access Points

- **Laravel App:** http://localhost:8001
- **phpMyAdmin:** http://localhost:8082
- **MySQL:** localhost:3306
- **Redis:** localhost:6379

## Database Access

### From Laravel App
- Host: `mysql`
- Port: `3306`
- Database: `laravel_nutrition`
- Username: `laravel_user`
- Password: `user_password`

### From Host Machine
- Host: `localhost`
- Port: `3306`
- Database: `laravel_nutrition`
- Username: `laravel_user`
- Password: `user_password`

### phpMyAdmin Access
- URL: http://localhost:8002
- Username: `laravel_user`
- Password: `user_password`

## Common Commands

### Container Management
```bash
# Start all services
docker-compose up -d

# Stop all services
docker-compose down

# Rebuild containers
docker-compose up -d --build

# View logs
docker-compose logs -f

# View specific service logs
docker-compose logs -f app
docker-compose logs -f nginx
docker-compose logs -f mysql
```

### Laravel Commands
```bash
# Access Laravel container shell
docker-compose exec app bash

# Run Artisan commands
docker-compose exec app php artisan migrate
docker-compose exec app php artisan db:seed
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan route:clear

# Install Composer packages
docker-compose exec app composer install
docker-compose exec app composer update

# Run tests
docker-compose exec app php artisan test
```

### Database Operations
```bash
# Access MySQL shell
docker-compose exec mysql mysql -u laravel_user -p laravel_nutrition

# Backup database
docker-compose exec mysql mysqldump -u laravel_user -p laravel_nutrition > backup.sql

# Restore database
docker-compose exec -T mysql mysql -u laravel_user -p laravel_nutrition < backup.sql
```

## File Structure

```
project/
├── docker/
│   ├── nginx/
│   │   └── default.conf       # Nginx configuration
│   ├── php/
│   │   └── local.ini          # PHP configuration
│   └── mysql/
│       └── my.cnf             # MySQL configuration
├── Dockerfile                 # PHP-FPM container definition
├── docker-compose.yml         # Service orchestration
├── .dockerignore              # Docker ignore rules
└── docker-env-example.txt     # Environment variables template
```

## Environment Configuration

The `docker-env-example.txt` file contains all necessary environment variables for Docker. Key configurations:

- **Database:** MySQL with proper Docker networking
- **Cache:** Redis for sessions and caching
- **Queue:** Database-based queue system
- **Mail:** Log driver for development

## Troubleshooting

### Permission Issues
```bash
# Fix file permissions
docker-compose exec app chown -R www:www /var/www/html
docker-compose exec app chmod -R 755 /var/www/html/storage
docker-compose exec app chmod -R 755 /var/www/html/bootstrap/cache
```

### Database Connection Issues
```bash
# Check MySQL container status
docker-compose ps mysql

# Check MySQL logs
docker-compose logs mysql

# Test database connection from app container
docker-compose exec app php artisan tinker
# Then run: DB::connection()->getPdo();
```

### Clear All Data and Restart
```bash
# Stop containers and remove volumes
docker-compose down -v

# Remove all containers and images
docker-compose down --rmi all

# Rebuild everything
docker-compose up -d --build
```

## Production Notes

For production deployment:
1. Change `APP_ENV` to `production`
2. Set `APP_DEBUG` to `false`
3. Use strong passwords for database
4. Configure proper SSL certificates
5. Use environment-specific configuration files
6. Set up proper logging and monitoring


