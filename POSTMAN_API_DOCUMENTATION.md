# Expense Tracker API - Postman Collection

## Overview
This Postman collection provides complete API endpoints for the Laravel-based Expense Tracker application. It includes authentication, expense management, category organization, and analytics dashboard functionality.

## Base Configuration

### Environment Variables
Set up the following variables in your Postman environment:

```
base_url: http://localhost:8000/api
auth_token: (will be auto-populated after login)
```

### Authentication
All endpoints except registration and login require Bearer token authentication. The token is automatically saved to environment variables when you login successfully.

## Pre-seeded Test Users

The application comes with pre-seeded test users you can use immediately:

| Email | Password | Name |
|-------|----------|------|
| `john@example.com` | `password123` | John Doe |
| `jane@example.com` | `password123` | Jane Smith |
| `admin@example.com` | `admin123` | Admin User |
| `demo@example.com` | `demo123` | Demo User |

## Endpoint Categories

### 1. Authentication Endpoints

#### POST `/auth/register`
Register a new user account.

**Request Body:**
```json
{
    "name": "John Doe",
    "email": "john.doe@example.com", 
    "password": "password123",
    "password_confirmation": "password123"
}
```

**Response (201):**
```json
{
    "message": "User registered successfully",
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john.doe@example.com"
    },
    "token": "1|abc123..."
}
```

#### POST `/auth/login`
Authenticate and get access token.

**Request Body:**
```json
{
    "email": "john@example.com",
    "password": "password123"
}
```

**Response (200):**
```json
{
    "message": "Login successful",
    "user": {
        "id": 1,
        "name": "John Doe", 
        "email": "john@example.com"
    },
    "token": "1|def456..."
}
```

#### GET `/auth/me`
Get current user information.

**Response (200):**
```json
{
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com"
    }
}
```

#### POST `/auth/logout`
Logout and revoke token.

**Response (200):**
```json
{
    "message": "Logged out successfully"
}
```

### 2. Category Endpoints

#### GET `/categories`
Get all user categories with expense counts.

**Response (200):**
```json
{
    "data": [
        {
            "id": 1,
            "name": "Food & Dining",
            "description": "Restaurant meals and groceries",
            "color": "#ff6b6b",
            "expenses_count": 45
        },
        {
            "id": 2,
            "name": "Transportation",
            "description": "Car expenses, fuel, public transport",
            "color": "#4ecdc4", 
            "expenses_count": 23
        }
    ]
}
```

#### POST `/categories`
Create a new category.

**Request Body:**
```json
{
    "name": "Entertainment",
    "description": "Movies, games, concerts, and fun activities"
}
```

**Response (201):**
```json
{
    "message": "Category created successfully",
    "data": {
        "id": 3,
        "name": "Entertainment",
        "description": "Movies, games, concerts, and fun activities",
        "color": "#45b7d1",
        "expenses_count": 0
    }
}
```

#### GET `/categories/{id}`
Get specific category details.

#### PUT `/categories/{id}`
Update existing category.

**Request Body:**
```json
{
    "name": "Food & Restaurants",
    "description": "Updated description for dining expenses"
}
```

#### DELETE `/categories/{id}`
Delete category (only if no associated expenses).

### 3. Expense Endpoints

#### GET `/expenses`
Get all user expenses (non-paginated).

**Response (200):**
```json
{
    "data": [
        {
            "id": 1,
            "description": "Lunch at Italian restaurant",
            "amount": "25.50",
            "date": "2024-10-16",
            "notes": "Business lunch with client",
            "category": {
                "id": 1,
                "name": "Food & Dining",
                "color": "#ff6b6b"
            }
        }
    ]
}
```

#### GET `/expenses?paginate=true`
Get paginated expenses with filtering.

**Query Parameters:**
- `paginate=true` - Enable pagination
- `page=1` - Page number (default: 1)
- `per_page=15` - Items per page (default: 15)
- `search=coffee` - Search in description
- `category_id=1` - Filter by category
- `start_date=2024-01-01` - Date range start
- `end_date=2024-12-31` - Date range end

**Response (200):**
```json
{
    "data": [
        {
            "id": 1,
            "description": "Morning coffee",
            "amount": "4.50",
            "date": "2024-10-16",
            "notes": null,
            "category": {
                "id": 1,
                "name": "Food & Dining",
                "color": "#ff6b6b"
            }
        }
    ],
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 38,
        "per_page": 15,
        "to": 15,
        "total": 567
    },
    "links": {
        "first": "http://localhost:8000/api/expenses?page=1",
        "last": "http://localhost:8000/api/expenses?page=38",
        "prev": null,
        "next": "http://localhost:8000/api/expenses?page=2"
    }
}
```

#### POST `/expenses`
Create a new expense.

**Request Body:**
```json
{
    "description": "Grocery shopping at Whole Foods",
    "amount": 87.32,
    "date": "2024-10-16",
    "category_id": 1,
    "notes": "Weekly grocery run - organic produce"
}
```

**Validation Rules:**
- `description`: required, string, 3-255 characters
- `amount`: required, numeric, 0.01-999999.99
- `date`: required, valid date format
- `category_id`: required, must exist in categories table
- `notes`: optional, string

**Response (201):**
```json
{
    "message": "Expense created successfully",
    "data": {
        "id": 568,
        "description": "Grocery shopping at Whole Foods",
        "amount": "87.32",
        "date": "2024-10-16",
        "notes": "Weekly grocery run - organic produce",
        "category": {
            "id": 1,
            "name": "Food & Dining",
            "color": "#ff6b6b"
        }
    }
}
```

#### GET `/expenses/{id}`
Get specific expense details.

#### PUT `/expenses/{id}`
Update existing expense.

#### DELETE `/expenses/{id}`
Delete expense record.

#### GET `/expenses/search`
Advanced expense search.

**Query Parameters:**
- `q=coffee` - Search query
- `category_id=1` - Category filter
- `start_date=2024-01-01` - Date range
- `end_date=2024-12-31` - Date range

#### GET `/expenses/date-range`
Get expenses by date range.

**Query Parameters:**
- `start_date=2024-10-01` - Start date (required)
- `end_date=2024-10-31` - End date (required)

### 4. Dashboard & Analytics Endpoints

#### GET `/dashboard`
Get comprehensive dashboard overview.

**Response (200):**
```json
{
    "data": {
        "total_expenses": "2847.56",
        "expenses_count": 156,
        "categories_count": 8,
        "monthly_average": "284.76",
        "top_categories": [
            {
                "name": "Food & Dining",
                "total": "1245.30",
                "count": 67,
                "percentage": 43.7
            },
            {
                "name": "Transportation", 
                "total": "567.88",
                "count": 34,
                "percentage": 19.9
            }
        ],
        "recent_expenses": [
            {
                "id": 156,
                "description": "Coffee shop",
                "amount": "4.50",
                "date": "2024-10-16",
                "category": "Food & Dining"
            }
        ],
        "monthly_spending": [
            {
                "month": "2024-10",
                "total": "567.23",
                "count": 23
            },
            {
                "month": "2024-09", 
                "total": "623.45",
                "count": 28
            }
        ]
    }
}
```

#### GET `/dashboard/monthly-summary`
Get monthly spending analysis.

**Query Parameters:**
- `period=current_month` - Time period filter

#### GET `/dashboard/yearly-summary`
Get yearly spending statistics.

#### GET `/dashboard/trends`
Get spending trends over time.

**Query Parameters:**
- `months=6` - Number of months to analyze (default: 6)

#### GET `/dashboard/daily-spending`
Get daily spending for current month.

## Error Responses

### Validation Errors (422)
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "email": ["The email field is required."],
        "amount": ["The amount must be greater than 0."]
    }
}
```

### Authentication Errors (401)
```json
{
    "message": "Unauthenticated."
}
```

### Not Found Errors (404)
```json
{
    "message": "Category not found"
}
```

### Server Errors (500)
```json
{
    "message": "Internal server error",
    "error": "Detailed error message"
}
```

## Testing Workflow

1. **Start with Authentication**
   - Use `POST /auth/login` with a pre-seeded user
   - Token will be automatically saved for subsequent requests

2. **Create Categories**
   - Use `POST /categories` to create expense categories
   - Note the category IDs for creating expenses

3. **Add Expenses**
   - Use `POST /expenses` with valid category IDs
   - Test different amounts, dates, and descriptions

4. **Test Pagination**
   - Use `GET /expenses?paginate=true` to test pagination
   - Try different page sizes and search filters

5. **Explore Analytics**
   - Use dashboard endpoints to see spending insights
   - Test different date ranges and periods

## Advanced Features

### Real-time Token Management
The collection includes automatic token extraction and storage. After successful login/registration, the token is saved to environment variables and used for all subsequent requests.

### Comprehensive Filtering
Expense endpoints support:
- Text search in descriptions
- Category filtering
- Date range filtering  
- Pagination with customizable page sizes
- Combined filter queries

### Analytics & Insights
Dashboard endpoints provide:
- Spending summaries by category
- Monthly and yearly trends
- Daily spending patterns
- Top spending categories
- Recent activity tracking

## Setup Instructions

1. **Import Collection**: Import the JSON file into Postman
2. **Set Environment**: Create environment with `base_url` variable
3. **Start Backend**: Ensure Laravel server is running on localhost:8000
4. **Login**: Use any pre-seeded user credentials to get started
5. **Test Endpoints**: Follow the workflow above to test functionality

The collection is designed to work immediately with the seeded database, providing realistic test data for a complete expense tracking experience.