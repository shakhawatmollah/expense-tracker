Write-Host "=== Testing Budget API Routes ==="

# Test 1: Login
Write-Host "`n1. Testing Login..."
try {
    $body = @{
        email = "admin@example.com"
        password = "admin123"
    } | ConvertTo-Json

    $loginResponse = Invoke-RestMethod -Uri "http://127.0.0.1:8000/api/auth/login" -Method POST -ContentType "application/json" -Body $body
    Write-Host "✅ Login successful: $($loginResponse.user.email)"
    $token = $loginResponse.token
} catch {
    Write-Host "❌ Login failed: $($_.Exception.Message)"
    exit
}

# Test 2: Basic Budget API
Write-Host "`n2. Testing Budget List API..."
try {
    $headers = @{
        "Accept" = "application/json"
        "Authorization" = "Bearer $token"
    }
    
    $budgetResponse = Invoke-RestMethod -Uri "http://127.0.0.1:8000/api/budgets" -Method GET -Headers $headers
    Write-Host "✅ Budget API successful"
    Write-Host "   Budgets found: $($budgetResponse.data.Count)"
    Write-Host "   Total records: $($budgetResponse.meta.total)"
} catch {
    Write-Host "❌ Budget API failed: $($_.Exception.Message)"
    if ($_.Exception.Response) {
        Write-Host "   Status: $($_.Exception.Response.StatusCode)"
    }
}

# Test 3: Budget Summary API
Write-Host "`n3. Testing Budget Summary API..."
try {
    $summaryResponse = Invoke-RestMethod -Uri "http://127.0.0.1:8000/api/budgets/summary" -Method GET -Headers $headers
    Write-Host "✅ Budget Summary API successful"
} catch {
    Write-Host "❌ Budget Summary API failed: $($_.Exception.Message)"
    if ($_.Exception.Response) {
        Write-Host "   Status: $($_.Exception.Response.StatusCode)"
    }
}

# Test 4: Budget Alerts API
Write-Host "`n4. Testing Budget Alerts API..."
try {
    $alertsResponse = Invoke-RestMethod -Uri "http://127.0.0.1:8000/api/budgets/alerts" -Method GET -Headers $headers
    Write-Host "✅ Budget Alerts API successful"
} catch {
    Write-Host "❌ Budget Alerts API failed: $($_.Exception.Message)"
    if ($_.Exception.Response) {
        Write-Host "   Status: $($_.Exception.Response.StatusCode)"
    }
}

# Test 5: Analytics Recommendations API
Write-Host "`n5. Testing Analytics Recommendations API..."
try {
    $recommendationsResponse = Invoke-RestMethod -Uri "http://127.0.0.1:8000/api/analytics/recommendations" -Method GET -Headers $headers
    Write-Host "✅ Analytics Recommendations API successful"
} catch {
    Write-Host "❌ Analytics Recommendations API failed: $($_.Exception.Message)"
    if ($_.Exception.Response) {
        Write-Host "   Status: $($_.Exception.Response.StatusCode)"
    }
}

Write-Host "`n=== Test Complete ==="