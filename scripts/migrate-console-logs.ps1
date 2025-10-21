# Console.log Migration Script for Windows PowerShell
# This script automatically migrates console.log/warn to debug utility

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Console.log Migration Script" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

$sourceDir = "frontend\src"
$debugUtilPath = "@/utils/debug"
$totalFiles = 0
$updatedFiles = 0
$errors = 0

# Find all Vue and JS files
Write-Host "Scanning for Vue and JavaScript files..." -ForegroundColor Yellow
$files = Get-ChildItem -Path $sourceDir -Include *.vue,*.js -Recurse -File

Write-Host "Found $($files.Count) files to process`n" -ForegroundColor Green

foreach ($file in $files) {
    $totalFiles++
    $relativePath = $file.FullName.Replace((Get-Location).Path + "\", "")
    
    try {
        $content = Get-Content $file.FullName -Raw -Encoding UTF8
        $originalContent = $content
        $modified = $false
        
        # Check if file uses console.log or console.warn
        $hasConsoleLogs = $content -match 'console\.(log|warn|table)\('
        
        if ($hasConsoleLogs) {
            Write-Host "Processing: $relativePath" -ForegroundColor Cyan
            
            # For Vue files, add import after <script setup>
            if ($file.Extension -eq '.vue') {
                # Check if debug import already exists
                if ($content -notmatch "import debug from") {
                    # Find <script setup> tag
                    if ($content -match '(<script setup>)') {
                        # Add after last import
                        if ($content -match '(<script setup>\s*\n\s*import .+)') {
                            # Add after last import
                            $content = $content -replace '(import .+\n)(\s*\n\s*const)', "`$1  import debug from '$debugUtilPath'`n`$2"
                        } else {
                            # Add right after script setup tag
                            $content = $content -replace '(<script setup>)', "`$1`n  import debug from '$debugUtilPath'"
                        }
                        Write-Host "  [OK] Added debug import" -ForegroundColor Green
                        $modified = $true
                    }
                }
            }
            # For JS files, add import at top
            elseif ($file.Extension -eq '.js') {
                if ($content -notmatch "import debug from") {
                    # Add at the top of the file
                    $content = "import debug from '$debugUtilPath'`n`n" + $content
                    Write-Host "  [OK] Added debug import" -ForegroundColor Green
                    $modified = $true
                }
            }
            
            # Replace console.log with debug.log
            $logCount = ([regex]::Matches($content, 'console\.log\(')).Count
            if ($logCount -gt 0) {
                $content = $content -replace 'console\.log\(', 'debug.log('
                Write-Host "  [OK] Replaced $logCount console.log() calls" -ForegroundColor Green
                $modified = $true
            }
            
            # Replace console.warn with debug.warn
            $warnCount = ([regex]::Matches($content, 'console\.warn\(')).Count
            if ($warnCount -gt 0) {
                $content = $content -replace 'console\.warn\(', 'debug.warn('
                Write-Host "  [OK] Replaced $warnCount console.warn() calls" -ForegroundColor Green
                $modified = $true
            }
            
            # Replace console.table with debug.table
            $tableCount = ([regex]::Matches($content, 'console\.table\(')).Count
            if ($tableCount -gt 0) {
                $content = $content -replace 'console\.table\(', 'debug.table('
                Write-Host "  [OK] Replaced $tableCount console.table() calls" -ForegroundColor Green
                $modified = $true
            }
            
            # Keep console.error as is (or use debug.error)
            $errorCount = ([regex]::Matches($content, 'console\.error\(')).Count
            if ($errorCount -gt 0) {
                Write-Host "  [INFO] Found $errorCount console.error() calls (keeping as is)" -ForegroundColor Yellow
            }
            
            # Save file if modified
            if ($modified -and $content -ne $originalContent) {
                Set-Content -Path $file.FullName -Value $content -Encoding UTF8 -NoNewline
                $updatedFiles++
                Write-Host "  [OK] File updated successfully`n" -ForegroundColor Green
            } elseif (-not $modified) {
                Write-Host "  [INFO] No changes needed`n" -ForegroundColor Yellow
            }
        }
        
    } catch {
        Write-Host "  [ERROR] Error processing file: $_" -ForegroundColor Red
        $errors++
    }
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Migration Complete!" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Total files scanned: $totalFiles" -ForegroundColor White
Write-Host "Files updated: $updatedFiles" -ForegroundColor Green
Write-Host "Errors: $errors" -ForegroundColor $(if ($errors -gt 0) { "Red" } else { "Green" })
Write-Host ""

# Run linter to check for remaining issues
Write-Host "Running ESLint to verify migration..." -ForegroundColor Yellow
Write-Host ""

try {
    Set-Location -Path "frontend"
    npm run lint 2>&1 | Out-Null
    
    if ($LASTEXITCODE -eq 0) {
        Write-Host "[OK] ESLint check passed!" -ForegroundColor Green
    } else {
        Write-Host "[WARN] ESLint found issues. Run 'npm run lint:fix' to auto-fix." -ForegroundColor Yellow
    }
} catch {
    Write-Host "[WARN] Could not run ESLint. Please run manually: npm run lint" -ForegroundColor Yellow
} finally {
    Set-Location -Path ".."
}

Write-Host ""
Write-Host "Next Steps:" -ForegroundColor Cyan
Write-Host "1. Review the changes: git diff" -ForegroundColor White
Write-Host "2. Fix any ESLint issues: cd frontend; npm run lint:fix" -ForegroundColor White
Write-Host "3. Test in development: cd frontend; npm run dev" -ForegroundColor White
Write-Host "4. Build for production: cd frontend; npm run build" -ForegroundColor White
Write-Host ""
