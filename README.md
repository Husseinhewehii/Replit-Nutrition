# 🥗 Replit Nutrition App

A comprehensive nutrition tracking application built with Laravel, designed to demonstrate AI integration for food lookup and nutrition fact retrieval.

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12.x-red.svg" alt="Laravel Version">
  <img src="https://img.shields.io/badge/PHP-8.4+-blue.svg" alt="PHP Version">
  <img src="https://img.shields.io/badge/Testing-Cypress-green.svg" alt="Testing">
  <img src="https://img.shields.io/badge/AI-OpenAI-orange.svg" alt="AI Integration">
</p>

## 🎯 Project Overview

This nutrition app was built for testing purposes to demonstrate how AI can be leveraged to enhance traditional web applications. The app combines a local database with AI-powered food lookup capabilities to provide comprehensive nutrition tracking.

### 🚀 Development Journey
- **Built with**: [Replit](https://replit.com) for rapid prototyping and development
- **Maintained with**: [Cursor](https://cursor.sh) for advanced AI-assisted coding
- **AI Integration**: OpenAI API for intelligent food lookup when items aren't in the local database

## ✨ Features

### 📊 Nutrition Tracking
- **Daily Entries**: Track food consumption with detailed nutrition information
- **Smart UI**: Current day highlighted with blue styling, older days collapsible
- **Nutrition Stats**: Real-time calculation of calories, protein, carbs, and fat
- **Food Database**: Local database with common foods and their nutritional values

### 🤖 AI-Powered Food Lookup
- **Intelligent Search**: When foods aren't found locally, AI searches for nutrition facts
- **OpenAI Integration**: Leverages AI to find accurate nutritional information
- **Seamless Experience**: Automatic fallback from local DB to AI lookup

### 🧪 Comprehensive Testing
- **Cypress E2E Tests**: Full browser automation testing
- **Visual Testing**: Verifies current day styling and expansion behavior
- **Interactive Testing**: Tests toggle functionality and user interactions
- **Test Data Seeding**: Automated test data generation for consistent testing

### 🎨 Modern UI/UX
- **Responsive Design**: Works on desktop and mobile devices
- **Intuitive Navigation**: Clear dashboard and entries interface
- **Visual Feedback**: Color-coded current day, smooth animations
- **Accessibility**: Proper contrast and keyboard navigation support

## 🛠️ Technology Stack

- **Backend**: Laravel 12.x with PHP 8.4+
- **Database**: MySQL with Eloquent ORM
- **Frontend**: Blade templates with vanilla JavaScript
- **Styling**: Custom CSS with responsive design
- **Testing**: Cypress for end-to-end testing
- **AI Integration**: OpenAI API for food lookup
- **Development**: Docker containerization
- **Version Control**: Git with GitHub

## 🚀 Getting Started

### Prerequisites
- Docker and Docker Compose
- Node.js and npm
- Git

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/Husseinhewehii/Replit-Nutrition.git
   cd Replit-Nutrition
   ```

2. **Set up environment**
   ```bash
   cp docker-env-example.txt .env
   # Edit .env with your database and OpenAI API settings
   ```

3. **Start the application**
   ```bash
   docker compose up -d
   ```

4. **Run migrations and seed data**
   ```bash
   docker compose exec app php artisan migrate
   docker compose exec app php artisan db:seed --class=CypressTestSeeder
   ```

5. **Access the application**
   - Web app: `https://localhost:8001`
   - Database: `localhost:3306`

## 🧪 Testing

### Running Cypress Tests

1. **Install dependencies**
   ```bash
   npm install
   ```

2. **Run tests in browser**
   ```bash
   npm run cypress:open
   ```

3. **Run tests headlessly**
   ```bash
   npm run cypress:run
   ```

### Test Coverage
- ✅ Current day styling and highlighting
- ✅ Day expansion/collapse functionality
- ✅ Nutrition stats display
- ✅ Interactive toggle behavior
- ✅ Authentication flow
- ✅ Responsive design

## 📁 Project Structure

```
├── app/
│   ├── Http/Controllers/     # API and web controllers
│   ├── Models/              # Eloquent models (User, Food, Portion)
│   ├── Services/            # Business logic (AI lookup, calculations)
│   └── Repositories/        # Data access layer
├── cypress/                 # E2E test suite
│   ├── e2e/                # Test specifications
│   ├── fixtures/           # Test data
│   └── support/            # Custom commands and utilities
├── database/
│   ├── migrations/         # Database schema
│   └── seeders/           # Test data generation
├── public/
│   ├── css/               # Stylesheets
│   └── js/                # JavaScript files
└── resources/views/       # Blade templates
```

## 🤖 AI Integration

The app demonstrates practical AI integration by:

1. **Local Database First**: Checks local food database for quick results
2. **AI Fallback**: Uses OpenAI API when foods aren't found locally
3. **Structured Responses**: AI returns standardized nutrition data
4. **Caching**: Stores AI results in local database for future use

### AI Service Implementation
```php
// Example AI food lookup
$aiService = new AiFoodLookupService();
$nutritionData = $aiService->lookupFood('quinoa salad');
```

## 🎨 UI/UX Highlights

### Entries Page Features
- **Current Day Highlighting**: Blue background (`#f0f8ff`) with blue border (`#4a90e2`)
- **Smart Expansion**: Only current day expanded by default
- **Smooth Animations**: CSS transitions for expand/collapse
- **Nutrition Cards**: Visual stats display for each day
- **Responsive Tables**: Mobile-friendly nutrition data tables

## 🔧 Development Workflow

1. **Replit**: Initial development and prototyping
2. **Cursor**: AI-assisted code maintenance and enhancement
3. **Git**: Version control and collaboration
4. **Docker**: Consistent development environment
5. **Cypress**: Automated testing and quality assurance

## 📊 Key Metrics

- **Test Coverage**: 6 comprehensive E2E test scenarios
- **Performance**: Optimized database queries and caching
- **Accessibility**: WCAG compliant design patterns
- **Responsiveness**: Mobile-first design approach

## 🤝 Contributing

This project demonstrates modern web development practices:
- AI-assisted development workflow
- Comprehensive testing strategies
- Clean architecture patterns
- Modern UI/UX design

## 📄 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

---

**Built with ❤️ using Laravel, AI, and modern development practices**
