# Expense Tracker - Laravel + Vue.js

A modern, full-stack expense tracking application built with Laravel 12 backend API and Vue.js 3 frontend.

## Architecture

This application follows a decoupled architecture with:
- **Backend**: Laravel 12 API with Sanctum authentication
- **Frontend**: Vue.js 3 with Vite, Pinia state management, and Tailwind CSS

## Features

### Core Functionality
- ✅ JWT/Sanctum authentication with secure API endpoints
- ✅ RESTful API architecture with Laravel 12
- ✅ Modern Vue.js 3 frontend with Composition API
- ✅ Pinia for state management
- ✅ Real-time expense tracking and categorization
- ✅ Interactive dashboard with charts and analytics
- ✅ Responsive design with Tailwind CSS
- ✅ Form validation on both frontend and backend
- ✅ Repository pattern with Eloquent ORM
- ✅ API resources for consistent response formatting
- ✅ Comprehensive error handling

### Laravel 12 Enhancements
- ✅ Enhanced performance and optimization
- ✅ Improved developer experience with better debugging
- ✅ Advanced caching mechanisms
- ✅ Enhanced testing capabilities with PHPUnit 11
- ✅ Modern PHP 8.2+ features support
- ✅ Streamlined service container and dependency injection

## Requirements

### Backend
- PHP 8.2 or higher
- MySQL 5.7+ or MariaDB 10.3+
- Composer 2.0+
- Laravel 12

### Key Dependencies
- **Laravel Framework**: ^12.0
- **Laravel Sanctum**: ^4.2 (API Authentication)
- **Laravel Tinker**: ^2.9 (Interactive Console)
- **Laravel Sail**: ^1.30 (Docker Development Environment)
- **PHPUnit**: ^11.0 (Testing Framework)
- **Laravel Pint**: ^1.13 (Code Formatting)

### Frontend
- Node.js 18+ and npm
- Modern web browser with ES6+ support

## Installation

### Backend Setup (Laravel API)

1. **Navigate to backend directory**
   ```bash
   cd backend
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Setup environment**
   ```bash
   cp .env.example .env
   ```
   
   Edit `.env` file with your database credentials:
   ```env
   DB_HOST=localhost
   DB_NAME=expense_tracker
   DB_USER=your_username
   DB_PASS=your_password
   
   # Sanctum settings for API authentication
   SANCTUM_STATEFUL_DOMAINS=localhost:3000
   SESSION_DOMAIN=localhost
   ```

4. **Generate application key**
   ```bash
   php artisan key:generate
   ```

5. **Create database**
   ```sql
   CREATE DATABASE expense_tracker CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

6. **Run migrations**
   ```bash
   php artisan migrate
   ```

7. **Publish Sanctum configuration**
   ```bash
   php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
   ```

8. **Start the Laravel development server**
   ```bash
   php artisan serve
   ```
   
   Backend API will be available at `http://localhost:8000`

### Alternative: Docker Development with Laravel Sail

For a containerized development environment:

1. **Start Laravel Sail**
   ```bash
   cd backend
   ./vendor/bin/sail up -d
   ```

2. **Run migrations in Docker**
   ```bash
   ./vendor/bin/sail artisan migrate
   ```

3. **Access the application**
   - Backend API: `http://localhost`
   - Database: Available on port 3306

### Frontend Setup (Vue.js)

1. **Navigate to frontend directory**
   ```bash
   cd frontend
   ```

2. **Install Node.js dependencies**
   ```bash
   npm install
   ```

3. **Setup environment variables**
   Create `.env` file in frontend directory:
   ```env
   VITE_API_BASE_URL=http://localhost:8000/api
   ```

4. **Start the development server**
   ```bash
   npm run dev
   ```
   
   Frontend will be available at `http://localhost:3000`

## Development Workflow

1. Start the Laravel backend: `cd backend && php artisan serve`
2. Start the Vue.js frontend: `cd frontend && npm run dev`
3. Access the application at `http://localhost:3000`

## Project Structure

```
expense-tracker/
├── backend/ (Laravel 12)
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/Api/     # API Controllers
│   │   │   ├── Requests/           # Form validation
│   │   │   ├── Resources/          # API response formatting
│   │   │   └── Middleware/         # Custom middleware
│   │   ├── Models/                 # Eloquent models
│   │   ├── Services/               # Business logic services
│   │   ├── Repositories/           # Data access layer
│   │   └── Exceptions/             # Custom exceptions
│   ├── database/
│   │   ├── migrations/             # Database migrations
│   │   ├── seeders/               # Database seeders
│   │   └── factories/             # Model factories
│   ├── routes/api.php             # API routes
│   ├── tests/                     # Backend tests
│   └── composer.json              # PHP dependencies
│
└── frontend/ (Vue.js 3 + Vite)
    ├── src/
    │   ├── components/            # Vue components
    │   │   ├── layout/           # Layout components
    │   │   ├── auth/             # Authentication forms
    │   │   ├── expenses/         # Expense components
    │   │   ├── categories/       # Category components
    │   │   ├── dashboard/        # Dashboard widgets
    │   │   └── common/           # Reusable components
    │   ├── views/                # Page components
    │   ├── stores/               # Pinia stores
    │   ├── services/             # API services
    │   ├── router/               # Vue Router config
    │   ├── composables/          # Vue composables
    │   └── assets/               # Static assets
    ├── package.json              # Node.js dependencies
    └── vite.config.js            # Vite configuration
```

## API Endpoints

### Authentication
- `POST /api/auth/register` - Register new user
- `POST /api/auth/login` - Login user
- `POST /api/auth/logout` - Logout user
- `GET /api/auth/me` - Get authenticated user

### Expenses
- `GET /api/expenses` - List user expenses
- `POST /api/expenses` - Create new expense
- `GET /api/expenses/{id}` - Get specific expense
- `PUT /api/expenses/{id}` - Update expense
- `DELETE /api/expenses/{id}` - Delete expense
- `GET /api/expenses/search` - Search expenses
- `GET /api/expenses/date-range` - Get expenses by date range

### Categories
- `GET /api/categories` - List user categories
- `POST /api/categories` - Create new category
- `GET /api/categories/{id}` - Get specific category
- `PUT /api/categories/{id}` - Update category
- `DELETE /api/categories/{id}` - Delete category

### Dashboard
- `GET /api/dashboard` - Get dashboard statistics
- `GET /api/dashboard/monthly-summary` - Monthly summary
- `GET /api/dashboard/yearly-summary` - Yearly summary
- `GET /api/dashboard/trends` - Expense trends

## Usage

### First Time Setup

1. Register a new account via the frontend registration form
2. Login with your credentials
3. Create your first expense category
4. Start adding expenses through the dashboard

### Frontend Features

- **Dashboard**: Interactive charts and expense summaries
- **Expense Management**: Add, edit, delete, and search expenses
- **Category Management**: Organize expenses by categories
- **Responsive Design**: Works on desktop and mobile devices
- **Real-time Updates**: Immediate UI updates after actions

## Security Features

### Backend Security
- **Laravel Sanctum**: Token-based API authentication
- **Password Hashing**: BCrypt hashing with salt
- **Input Validation**: Form Request validation classes
- **SQL Injection Protection**: Eloquent ORM with prepared statements
- **CORS Protection**: Configured for frontend domain
- **Rate Limiting**: API throttling middleware
- **Authorization**: Policy-based access control

### Frontend Security
- **Token Storage**: Secure token storage in localStorage
- **Request Interceptors**: Automatic token attachment
- **Route Guards**: Authentication-based navigation
- **Input Sanitization**: Client-side validation
- **HTTPS Ready**: Production-ready security headers

## Development

### Backend Development

#### Running Tests
```bash
cd backend
php artisan test

# Run with coverage
php artisan test --coverage

# Run specific test suite
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit
```

#### Code Style
- Follows PSR-12 coding standards
- Uses Laravel 12 best practices
- Repository pattern for data access
- Service layer for business logic
- API Resources for response formatting

#### Adding New Features
1. Create migrations: `php artisan make:migration`
2. Create models: `php artisan make:model`
3. Create controllers: `php artisan make:controller`
4. Create form requests: `php artisan make:request`
5. Create API resources: `php artisan make:resource`
6. Create services in `app/Services/`
7. Create repositories in `app/Repositories/`

### Frontend Development

#### Development Server
```bash
cd frontend
npm run dev
```

#### Building for Production
```bash
npm run build
```

#### Code Style
```bash
npm run lint
npm run format
```

#### Tech Stack
- **Vue.js 3**: Composition API with `<script setup>`
- **Pinia**: State management
- **Vue Router**: Client-side routing
- **Axios**: HTTP client for API calls
- **Tailwind CSS**: Utility-first CSS framework
- **Vite**: Fast build tool and dev server

#### Adding New Features
1. Create components in `src/components/`
2. Create views in `src/views/`
3. Add routes in `src/router/index.js`
4. Create stores in `src/stores/`
5. Add services in `src/services/`

## Database Schema

### Users Table (Laravel migrations)
```php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email')->unique();
    $table->timestamp('email_verified_at')->nullable();
    $table->string('password');
    $table->rememberToken();
    $table->timestamps();
});
```

### Categories Table
```php
Schema::create('categories', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->text('description')->nullable();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->timestamps();
    $table->unique(['name', 'user_id']);
});
```

### Expenses Table
```php
Schema::create('expenses', function (Blueprint $table) {
    $table->id();
    $table->string('description');
    $table->decimal('amount', 10, 2);
    $table->date('expense_date');
    $table->foreignId('category_id')->constrained()->onDelete('cascade');
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->timestamps();
});
```

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## Support

For issues and questions, please create an issue in the repository or contact with me.
