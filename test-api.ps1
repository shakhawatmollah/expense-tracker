# Test Budget API

Write-Host "Testing Budget API..."

# Login
$body = @{
    email = "admin@example.com"
    password = "admin123"
} | ConvertTo-Json

try {
    $loginResponse = Invoke-RestMethod -Uri "http://127.0.0.1:8000/api/auth/login" -Method POST -ContentType "application/json" -Body $body
    Write-Host "✅ Login successful"
    
    $token = $loginResponse.token
    $headers = @{
        "Accept" = "application/json"
        "Authorization" = "Bearer $token"
    }
    
    # Test Budget API
    $budgetResponse = Invoke-RestMethod -Uri "http://127.0.0.1:8000/api/budgets" -Method GET -Headers $headers
    Write-Host "✅ Budget API successful"
    Write-Host "Budget count: $($budgetResponse.data.Count)"
    Write-Host "Total records: $($budgetResponse.meta.total)"
    
    if ($budgetResponse.data.Count -gt 0) {
        Write-Host "First budget: $($budgetResponse.data[0].name) - $($budgetResponse.data[0].amount.formatted)"
    }
    
} catch {
    Write-Host "❌ Error: $($_.Exception.Message)"
    if ($_.Exception.Response) {
        Write-Host "Status: $($_.Exception.Response.StatusCode)"
    }
}