# Testing Checklist

## Authentication Flows
- [ ] User registration with valid data
- [ ] User registration with invalid data (test validation)
- [ ] User login with correct credentials
- [ ] User login with incorrect credentials
- [ ] "Remember me" functionality
- [ ] Logout functionality
- [ ] Session persistence after page refresh

## Dashboard
- [ ] Dashboard loads with correct data
- [ ] Real-time updates work
- [ ] Quick actions work (Add Expense, Add Category, etc.)
- [ ] Statistics display correctly
- [ ] Charts render properly
- [ ] Mobile responsive layout

## Expense Management
- [ ] Create new expense
- [ ] Edit existing expense
- [ ] Delete expense
- [ ] Search/filter expenses
- [ ] Sort expenses by different columns
- [ ] Pagination works
- [ ] Expense validation (amount, date, category)

## Category Management
- [ ] Create new category
- [ ] Edit category
- [ ] Delete category (check if has expenses)
- [ ] Category color picker works
- [ ] Categories display in expenses dropdown

## Budget Management
- [ ] Create budget for category
- [ ] Edit budget
- [ ] Delete budget
- [ ] Budget alerts trigger correctly
- [ ] Budget progress displays accurately
- [ ] Different budget periods (weekly, monthly, yearly)

## Analytics
- [ ] Analytics page loads
- [ ] Charts display with real data
- [ ] Date range filters work
- [ ] Category breakdowns show
- [ ] Spending trends accurate
- [ ] Export analytics (if implemented)

## Error Scenarios
- [ ] Network error handling
- [ ] Invalid API responses
- [ ] Unauthorized access redirects
- [ ] Form validation errors display
- [ ] Toast notifications work for all events

## Performance
- [ ] Initial page load time acceptable
- [ ] Navigation between pages smooth
- [ ] Large dataset handling (100+ expenses)
- [ ] Image/asset loading optimized
- [ ] No memory leaks (long session test)

## Browser Compatibility
- [ ] Chrome (latest)
- [ ] Firefox (latest)
- [ ] Safari (latest)
- [ ] Edge (latest)
- [ ] Mobile browsers (Chrome, Safari)

## Security
- [ ] XSS protection (test with malicious inputs)
- [ ] CSRF protection enabled
- [ ] SQL injection prevention
- [ ] Authentication tokens secure
- [ ] Sensitive data not exposed in console/network
