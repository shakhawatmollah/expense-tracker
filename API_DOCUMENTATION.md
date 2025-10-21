# 📚 Expense Tracker API Documentation

## Base URL
```
http://localhost:8000/api
```

## Authentication
All protected endpoints require a Bearer token in the Authorization header:
```
Authorization: Bearer {token}
```

---

## 🔐 Authentication Endpoints

### Register User
```http
POST /auth/register
```

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Response:** `201 Created`
```json
{
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    },
    "token": "1|abc123..."
  }
}
```

### Login
```http
POST /auth/login
```

**Request Body:**
```json
{
  "email": "john@example.com",
  "password": "password123"
}
```

**Response:** `200 OK`
```json
{
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    },
    "token": "1|abc123..."
  }
}
```

### Get Current User
```http
GET /auth/me
```

**Headers:** `Authorization: Bearer {token}`

**Response:** `200 OK`
```json
{
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "created_at": "2025-10-21T10:00:00.000000Z"
  }
}
```

### Logout
```http
POST /auth/logout
```

**Headers:** `Authorization: Bearer {token}`

**Response:** `200 OK`
```json
{
  "message": "Logged out successfully"
}
```

---

## 💰 Expense Endpoints

### List Expenses
```http
GET /expenses?page=1&per_page=15&category_id=1&date_from=2025-10-01&date_to=2025-10-31
```

**Query Parameters:**
- `page` (optional): Page number (default: 1)
- `per_page` (optional): Items per page (default: 15)
- `category_id` (optional): Filter by category
- `date_from` (optional): Start date (Y-m-d)
- `date_to` (optional): End date (Y-m-d)

**Response:** `200 OK`
```json
{
  "data": [
    {
      "id": 1,
      "description": "Grocery shopping",
      "amount": {
        "raw": 150.50,
        "formatted": "$150.50"
      },
      "date": "2025-10-21",
      "category": {
        "id": 1,
        "name": "Food",
        "color": "#3B82F6"
      },
      "created_at": "2025-10-21T10:00:00.000000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 73
  },
  "links": {
    "first": "/api/expenses?page=1",
    "last": "/api/expenses?page=5",
    "prev": null,
    "next": "/api/expenses?page=2"
  }
}
```

### Create Expense
```http
POST /expenses
```

**Request Body:**
```json
{
  "description": "Grocery shopping",
  "amount": 150.50,
  "date": "2025-10-21",
  "category_id": 1,
  "notes": "Weekly groceries"
}
```

**Response:** `201 Created`
```json
{
  "data": {
    "id": 1,
    "description": "Grocery shopping",
    "amount": {
      "raw": 150.50,
      "formatted": "$150.50"
    },
    "date": "2025-10-21",
    "category": {
      "id": 1,
      "name": "Food",
      "color": "#3B82F6"
    }
  }
}
```

### Update Expense
```http
PUT /expenses/{id}
```

**Request Body:**
```json
{
  "description": "Updated description",
  "amount": 175.00,
  "date": "2025-10-21",
  "category_id": 1
}
```

**Response:** `200 OK`

### Delete Expense
```http
DELETE /expenses/{id}
```

**Response:** `200 OK`
```json
{
  "message": "Expense deleted successfully"
}
```

---

## 📊 Dashboard Endpoints

### Get Dashboard Overview
```http
GET /dashboard
```

**Response:** `200 OK`
```json
{
  "data": {
    "total_expenses": 5420.50,
    "monthly_expenses": 1250.00,
    "expense_count": 45,
    "categories_count": 8,
    "top_categories": [
      {
        "name": "Food",
        "total": 450.00,
        "percentage": 36.0
      }
    ],
    "recent_expenses": []
  }
}
```

### Get Monthly Summary
```http
GET /dashboard/monthly-summary?period=current_month
```

**Response:** `200 OK`

### Get Trends
```http
GET /dashboard/trends?months=6
```

**Response:** `200 OK`

---

## 💰 Budget Endpoints

### List Budgets
```http
GET /budgets?period=monthly&is_active=true
```

**Response:** `200 OK`
```json
{
  "data": [
    {
      "id": 1,
      "name": "Monthly Food Budget",
      "amount": {
        "raw": 1000.00,
        "formatted": "$1,000.00"
      },
      "spent_amount": {
        "raw": 450.00,
        "formatted": "$450.00"
      },
      "remaining_amount": {
        "raw": 550.00,
        "formatted": "$550.00"
      },
      "period": "monthly",
      "usage_percentage": 45.0,
      "status": "safe",
      "category": {
        "id": 1,
        "name": "Food"
      }
    }
  ]
}
```

### Create Budget
```http
POST /budgets
```

**Request Body:**
```json
{
  "name": "Monthly Food Budget",
  "amount": 1000.00,
  "period": "monthly",
  "category_id": 1,
  "start_date": "2025-10-01",
  "end_date": "2025-10-31",
  "alert_thresholds": {
    "warning": 75,
    "danger": 90
  }
}
```

**Response:** `201 Created`

### Get Budget Alerts
```http
GET /budgets/alerts
```

**Response:** `200 OK`
```json
{
  "data": [
    {
      "budget_id": 1,
      "budget_name": "Monthly Food Budget",
      "alert_type": "warning",
      "usage_percentage": 80.0,
      "message": "You've used 80% of your Monthly Food Budget"
    }
  ]
}
```

---

## 📈 Analytics Endpoints

### Get Analytics Dashboard
```http
GET /analytics/dashboard?period=monthly
```

**Response:** `200 OK`
```json
{
  "data": {
    "total_spending": 5420.50,
    "average_daily": 180.68,
    "spending_trend": "increasing",
    "category_breakdown": [],
    "top_expenses": []
  }
}
```

### Get Spending Patterns
```http
GET /analytics/patterns?type=recurring
```

**Response:** `200 OK`

### Get Financial Health Score
```http
GET /analytics/financial-health?period=monthly
```

**Response:** `200 OK`

### Get Recommendations
```http
GET /analytics/recommendations?period=monthly
```

**Response:** `200 OK`

---

## 🏷️ Category Endpoints

### List Categories
```http
GET /categories
```

**Response:** `200 OK`
```json
{
  "data": [
    {
      "id": 1,
      "name": "Food",
      "color": "#3B82F6",
      "icon": "utensils",
      "description": "Food and groceries",
      "expense_count": 45,
      "total_spent": 1250.00
    }
  ]
}
```

### Create Category
```http
POST /categories
```

**Request Body:**
```json
{
  "name": "Entertainment",
  "color": "#8B5CF6",
  "icon": "film",
  "description": "Movies, games, etc."
}
```

**Response:** `201 Created`

---

## ⚠️ Error Responses

### Validation Error (422)
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password must be at least 8 characters."]
  }
}
```

### Unauthorized (401)
```json
{
  "message": "Unauthenticated."
}
```

### Not Found (404)
```json
{
  "message": "Resource not found."
}
```

### Rate Limit Exceeded (429)
```json
{
  "message": "Too many requests. Please try again later.",
  "retry_after": 60
}
```

---

## 📊 Rate Limits

- **Protected Endpoints**: 60 requests per minute
- **Auth Endpoints**: 5 requests per minute
- **Public Endpoints**: 30 requests per minute

Rate limit headers:
- `X-RateLimit-Limit`: Maximum requests allowed
- `X-RateLimit-Remaining`: Remaining requests
- `Retry-After`: Seconds until rate limit resets

---

## 🔍 Best Practices

1. **Always include the Authorization header** for protected endpoints
2. **Handle rate limiting** by checking response headers
3. **Use pagination** for large datasets
4. **Cache responses** when appropriate
5. **Validate data** before sending requests
6. **Handle errors gracefully** with proper error messages

---

## 📞 Support

For issues or questions, please contact the development team or create an issue in the repository.