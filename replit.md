# Overview

This is a Laravel-based nutrition tracking application that allows users to log food intake, track daily macronutrient totals, and calculate personalized macro requirements. The application uses a clean architecture with Services and Repositories to separate business logic from controllers, making the codebase reusable across both web (Blade/session-based) and API (Sanctum token-based) interfaces.

# User Preferences

Preferred communication style: Simple, everyday language.

# System Architecture

## Backend Framework
- **Framework**: Laravel 12.x running on PHP 8.2+
- **Authentication**: 
  - Web routes use session-based authentication (`web` guard) with CSRF protection
  - API routes use Laravel Sanctum personal access tokens for mutations
  - Separate authentication mechanisms ensure web and API remain independent
- **Database**: SQLite as default (with Docker artifacts provided for MySQL/phpMyAdmin deployment)
- **ORM**: Eloquent models with a Repository pattern abstracting all database operations

## Architectural Patterns

### Repository Pattern
All database operations are encapsulated in Repository classes (e.g., `FoodRepository`, `PortionRepository`). Controllers and Services never directly interact with Eloquent models, ensuring:
- Centralized query logic
- Easier testing and mocking
- Clear separation of data access from business logic

### Service Layer
Business logic lives in Service classes (e.g., `FoodService`, `MacroCalculationService`). Services:
- Orchestrate operations using repositories
- Contain domain logic (macro calculations, daily rollups)
- Are reused by both web controllers and API endpoints
- Keep controllers thin and focused on HTTP concerns

### Form Requests & Value Objects
- Form Requests handle validation for incoming data
- Value Objects provide strong typing and encapsulation
- Controllers receive validated data, reducing logic in route handlers

## Data Model

### Core Entities
1. **Users**: Standard Laravel authentication users
2. **Foods**: Can be user-owned (private) or global (readable by all)
   - Fields: name, slug, calories, protein, carbs, fat, user_id, is_global
   - Slug pattern: lowercase, digits, underscores only (`^[a-z0-9_]+$`)
3. **Portions/Entries**: User logs intake with grams consumed
   - Links to a food and user
   - Grouped by day for daily totals calculation
4. **Daily Totals**: Aggregated calories and macros (protein, carbs, fat) per day

## Frontend Architecture

### Asset Strategy
- **No build tools**: Vite and dev servers are explicitly not used
- **Static assets**: CSS and JS files live in `/public/css` and `/public/js`
- Simple `<link>` and `<script>` tags in Blade templates
- Tailwind CSS v4 is configured but assets are compiled statically

### Web UI (Blade Views)
- **Dashboard**: Shows today's totals with two "Add Portion" methods:
  1. Single input accepting `slug-grams` format (e.g., `chicken_breast-150`)
  2. Food cards with individual gram inputs and "Add" buttons
- **Foods Page**: CRUD for user-owned foods; view-only for global foods
- **Entries Page**: Paginated list of days with macro totals
- **Macro Calculator Page**: Form-based calculator using service layer

## External Dependencies

### Third-Party Services
- **OpenAI PHP Client** (`openai-php/laravel`): Integrated for potential AI features (likely food suggestions, macro recommendations, or chatbot functionality)

### Key Laravel Packages
- **Laravel Sanctum**: API authentication via personal access tokens
- **Laravel Tinker**: REPL for debugging
- **Laravel Pail**: CLI log tailing for development
- **Laravel Sail**: Docker development environment (optional, not wired by default)

### Development Tools
- **Faker**: Test data generation
- **PHPUnit**: Testing framework
- **Laravel Pint**: Code formatting
- **Mockery**: Mocking for tests

### HTTP & Utility Libraries
- **Guzzle**: HTTP client for external API calls
- **CORS Support** (`fruitcake/php-cors`): Handles cross-origin requests for API

## Environment Configuration
- `APP_URL` must be HTTPS (critical for Replit deployment)
- SQLite database auto-created at `database/database.sqlite`
- Default environment: local with debug enabled
- Session-based authentication for web; token-based for API

## Design Decisions

### Why Repository Pattern?
**Problem**: Controllers often directly query models, mixing HTTP concerns with data access.  
**Solution**: Repositories centralize all DB operations.  
**Benefit**: Controllers stay thin; business logic in Services can be tested without hitting the database.

### Why Separate Web and API Auth?
**Problem**: Mixing session and token auth can cause conflicts and security issues.  
**Solution**: Web uses `web` guard (sessions/cookies), API uses `auth:sanctum` (tokens).  
**Benefit**: Clean separation; API can be consumed by mobile apps or SPAs without affecting web sessions.

### Why Static Assets Without Vite?
**Problem**: Vite requires a dev server, complicating deployment on constrained environments like Replit.  
**Solution**: Pre-compiled CSS/JS served as static files from `/public`.  
**Benefit**: Simpler deployment; no build step or node processes required in production.

### Why SQLite Default?
**Problem**: MySQL requires additional services and configuration.  
**Solution**: SQLite provides zero-config database for development.  
**Benefit**: Faster onboarding; Docker artifacts available for production MySQL when needed.