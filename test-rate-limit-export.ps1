# PowerShell Test Script for Rate Limiting & Export

Write-Host "🧪 Testing Rate Limiting & Export Functionality" -ForegroundColor Cyan
Write-Host "==============================================" -ForegroundColor Cyan
Write-Host ""

$BaseUrl = "http://localhost:8000/api"

# Test 1: Rate Limiting on Login
Write-Host "📝 Test 1: Login Rate Limiting (should block after 5 attempts)" -ForegroundColor Yellow
Write-Host "Attempting 7 login requests..."

for ($i = 1; $i -le 7; $i++) {
    try {
        $response = Invoke-WebRequest -Uri "$BaseUrl/auth/login" `
            -Method POST `
            -ContentType "application/json" `
            -Body '{"email":"test@test.com","password":"wrong"}' `
            -UseBasicParsing `
            -ErrorAction SilentlyContinue
        
        if ($response.StatusCode -eq 401) {
            Write-Host "  ○ Attempt $i: Unauthorized (401) - Normal response" -ForegroundColor Gray
        }
    } catch {
        if ($_.Exception.Response.StatusCode -eq 429) {
            Write-Host "  ✓ Attempt $i: Blocked (429) - Rate limiting working!" -ForegroundColor Green
        } else {
            Write-Host "  ✗ Attempt $i: Unexpected status $($_.Exception.Response.StatusCode)" -ForegroundColor Red
        }
    }
    Start-Sleep -Milliseconds 500
}

Write-Host ""
Write-Host "⏱️  Waiting 20 seconds for rate limit to reset..." -ForegroundColor Yellow
Start-Sleep -Seconds 20

# Test 2: Successful Login
Write-Host ""
Write-Host "📝 Test 2: Successful Login" -ForegroundColor Yellow

try {
    $loginResponse = Invoke-RestMethod -Uri "$BaseUrl/auth/login" `
        -Method POST `
        -ContentType "application/json" `
        -Body '{"email":"admin@example.com","password":"password"}' `
        -UseBasicParsing
    
    $token = $loginResponse.data.token
    
    if ($token) {
        Write-Host "  ✓ Login successful - Token obtained" -ForegroundColor Green
    } else {
        Write-Host "  ✗ Login failed - No token received" -ForegroundColor Red
        Write-Host "Response: $loginResponse"
        exit 1
    }
} catch {
    Write-Host "  ✗ Login failed: $($_.Exception.Message)" -ForegroundColor Red
    exit 1
}

# Test 3: Export Functionality
Write-Host ""
Write-Host "📝 Test 3: Export Expenses (CSV)" -ForegroundColor Yellow

try {
    $headers = @{
        "Authorization" = "Bearer $token"
    }
    
    $exportResponse = Invoke-WebRequest -Uri "$BaseUrl/export/expenses?format=csv" `
        -Method GET `
        -Headers $headers `
        -UseBasicParsing
    
    if ($exportResponse.StatusCode -eq 200) {
        Write-Host "  ✓ Export successful (200)" -ForegroundColor Green
        
        # Save the file
        $filename = "test_export_$(Get-Date -Format 'yyyyMMdd_HHmmss').csv"
        $exportResponse.Content | Out-File -FilePath $filename -Encoding UTF8
        Write-Host "  ✓ File saved as: $filename" -ForegroundColor Green
    }
} catch {
    Write-Host "  ✗ Export failed (Status: $($_.Exception.Response.StatusCode))" -ForegroundColor Red
}

# Test 4: Export Rate Limiting
Write-Host ""
Write-Host "📝 Test 4: Export Rate Limiting (should block after 10 attempts)" -ForegroundColor Yellow
Write-Host "Attempting 12 export requests..."

for ($i = 1; $i -le 12; $i++) {
    try {
        $exportResponse = Invoke-WebRequest -Uri "$BaseUrl/export/expenses?format=csv" `
            -Method GET `
            -Headers $headers `
            -UseBasicParsing `
            -ErrorAction Stop
        
        if ($exportResponse.StatusCode -eq 200) {
            Write-Host "  ○ Attempt $i: Success (200)" -ForegroundColor Gray
        }
    } catch {
        if ($_.Exception.Response.StatusCode -eq 429) {
            Write-Host "  ✓ Attempt $i: Blocked (429) - Export rate limiting working!" -ForegroundColor Green
        } else {
            Write-Host "  ✗ Attempt $i: Unexpected status $($_.Exception.Response.StatusCode)" -ForegroundColor Red
        }
    }
    Start-Sleep -Seconds 1
}

Write-Host ""
Write-Host "==============================================" -ForegroundColor Cyan
Write-Host "🎉 Testing Complete!" -ForegroundColor Green
Write-Host ""
Write-Host "Summary:" -ForegroundColor Cyan
Write-Host "  - Login rate limiting: Check output above"
Write-Host "  - Export functionality: Check output above"
Write-Host "  - Export rate limiting: Check output above"
Write-Host ""
Write-Host "Next steps:" -ForegroundColor Yellow
Write-Host "  1. Check the exported CSV files in backend/storage/app/exports/"
Write-Host "  2. Test the frontend export modal in the dashboard"
Write-Host "  3. Review rate limit headers in the responses"
