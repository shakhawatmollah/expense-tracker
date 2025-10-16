# Expense Tracker - AI Coding Instructions

## Project Overview
This is a modern full-stack expense tracking application built with Laravel 11 backend API and Vue.js 3 frontend. The project follows a decoupled architecture with RESTful API design and modern frontend practices.

## Architecture & Structure

### Project Structure
```
expense-tracker/
├── backend/ (Laravel 11 API)
│   ├── app/
│   │   ├── Http/Controllers/Api/     # API Controllers
│   │   ├── Http/Requests/           # Form validation
│   │   ├── Http/Resources/          # API response formatting
│   │   ├── Models/                  # Eloquent models
│   │   ├── Services/                # Business logic services
│   │   ├── Repositories/            # Data access layer
│   │   └── Exceptions/              # Custom exceptions
│   ├── database/migrations/         # Database migrations
│   ├── routes/api.php              # API routes
│   └── tests/                      # Backend tests
│
└── frontend/ (Vue.js 3 + Vite)
    ├── src/
    │   ├── components/              # Vue components
    │   ├── views/                   # Page components
    │   ├── stores/                  # Pinia stores
    │   ├── services/                # API services
    │   ├── router/                  # Vue Router config
    │   └── assets/                  # Static assets
    ├── package.json                 # Node.js dependencies
    └── vite.config.js              # Vite configuration
```

## Development Conventions

### PHP Standards
- Follow PSR-4 autoloading standards
- Use PSR-12 coding style guidelines
- Implement proper error handling with try-catch blocks
- Use type declarations for function parameters and return types

### Database Patterns
- Use PDO for database connections with prepared statements
- Implement repository pattern for data access
- Store SQL migrations in `database/migrations/`
- Follow naming convention: `expenses`, `categories`, `users` tables

### Key Models Expected
```php
// Example expense model structure
class Expense {
    private int $id;
    private string $description;
    private float $amount;
    private DateTime $date;
    private int $categoryId;
    private int $userId;
}
```

## Essential Dependencies (composer.json)
```json
{
    "require": {
        "php": "^8.1",
        "vlucas/phpdotenv": "^5.0"
    },
    "require-dev": {
        "phpunit/phpunit": "^10.0"
    }
}
```

## Development Workflow

### Setup Commands
```bash
composer install
cp .env.example .env
# Configure database settings in .env
php database/migrate.php
```

### Testing
```bash
composer test
# or
./vendor/bin/phpunit tests/
```

## Core Features to Implement

### Expense Management
- Create, read, update, delete expenses
- Categorize expenses (food, transport, utilities, etc.)
- Date range filtering and search
- Monthly/yearly expense summaries

### Data Validation
- Validate expense amounts (positive numbers)
- Required fields: description, amount, date, category
- Sanitize user input to prevent XSS/SQL injection

### Authentication (if multi-user)
- Simple session-based authentication
- Password hashing with `password_hash()`
- User registration and login forms

## Configuration Patterns

### Environment Variables (.env)
```
DB_HOST=localhost
DB_NAME=expense_tracker
DB_USER=username
DB_PASS=password
APP_DEBUG=true
```

### Database Connection Example
```php
// Use PDO with error handling
try {
    $pdo = new PDO(
        "mysql:host={$host};dbname={$dbname};charset=utf8mb4",
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    // Handle connection error
}
```

## Security Considerations
- Always use prepared statements for database queries
- Validate and sanitize all user inputs
- Implement CSRF protection for forms
- Use HTTPS in production
- Store sensitive config in .env file (never commit .env)

## Code Examples

### Controller Pattern
```php
class ExpenseController {
    public function store(array $data): bool {
        // Validate input
        // Save to database via repository
        // Return success/failure
    }
}
```

### Repository Pattern
```php
class ExpenseRepository {
    public function findByDateRange(DateTime $start, DateTime $end): array {
        // Return expenses within date range
    }
}
```

## When Working on This Project
1. Start with basic CRUD operations for expenses
2. Implement categories as a separate entity with foreign key relationship
3. Add user authentication if multi-user support is needed
4. Focus on input validation and security from the beginning
5. Use consistent naming conventions throughout
6. Write tests for business logic, especially calculations
7. Keep the UI simple and functional - focus on core expense tracking features

## Common Patterns
- Use dependency injection for services
- Implement proper error logging
- Create reusable form validation helpers
- Use template inheritance for consistent page layouts