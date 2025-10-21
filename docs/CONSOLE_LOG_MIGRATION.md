# Console.log Migration Guide

## Overview
This guide helps migrate all `console.log` statements to use the new debug utility for proper production builds.

## Quick Reference

### Import the debug utility
```javascript
import debug from '@/utils/debug'
```

### Replace console statements

| Old Statement | New Statement | Production Behavior |
|--------------|--------------|-------------------|
| `console.log('message')` | `debug.log('message')` | Removed |
| `console.warn('warning')` | `debug.warn('warning')` | Removed |
| `console.error('error')` | `debug.error('error')` | **Kept** (always logged) |
| `console.table(data)` | `debug.table(data)` | Removed |
| `console.group('label')` | `debug.group('label', () => {})` | Removed |
| `console.time('label')` | `const end = debug.time('label'); end()` | Removed |

## Automated Migration Script

Run this command to find all console.log statements:

```bash
# PowerShell
Get-ChildItem -Path frontend\src -Include *.vue,*.js -Recurse | Select-String -Pattern "console\.(log|warn|error|table)" | Select-Object Path, LineNumber, Line | Format-Table -AutoSize
```

## Files to Update

Based on the audit, the following files need updating:

### High Priority (Most occurrences)
1. ✅ **Dashboard.vue** - UPDATED (25+ occurrences)
2. **Analytics.vue** - 6 occurrences
3. **ExpenseChart.vue** - 4 occurrences
4. **Categories.vue** - 1 occurrence
5. **QuickAddExpense.vue** - 2 occurrences

### Medium Priority
6. **AppSidebar.vue** - 1 occurrence
7. **AppHeader.vue** - 1 occurrence
8. **ExpenseList.vue** - 1 occurrence
9. **ExpenseForm.vue** - 2 occurrences
10. **SummaryCards.vue** - 1 occurrence
11. **RecentExpenses.vue** - 3 occurrences
12. **CategoryBreakdown.vue** - 1 occurrence
13. **RealTimeData.vue** - 1 occurrence

## Migration Steps

### Step 1: Add debug import to each file

Find the `<script setup>` section and add:

```javascript
<script setup>
  import { ref, computed } from 'vue'
  import debug from '@/utils/debug'  // ← Add this line
  // ... other imports
```

### Step 2: Replace console.log with debug.log

**Before:**
```javascript
console.log('Loading data...')
console.warn('Warning message')
console.error('Error occurred:', error)
```

**After:**
```javascript
debug.log('Loading data...')
debug.warn('Warning message')
debug.error('Error occurred:', error)
```

### Step 3: Verify with ESLint

ESLint will now show errors for any remaining `console.log` statements:

```bash
npm run lint
```

Fix any errors:
```bash
npm run lint:fix
```

## Example Migration

### Before Migration
```vue
<script setup>
import { ref, onMounted } from 'vue'
import { useExpenseStore } from '@/stores/expenses'

const expenseStore = useExpenseStore()
const loading = ref(false)

onMounted(async () => {
  console.log('Component mounted')
  try {
    loading.value = true
    await expenseStore.fetchExpenses()
    console.log('Expenses loaded:', expenseStore.expenses.length)
  } catch (error) {
    console.error('Failed to load expenses:', error)
  } finally {
    loading.value = false
  }
})
</script>
```

### After Migration
```vue
<script setup>
import { ref, onMounted } from 'vue'
import { useExpenseStore } from '@/stores/expenses'
import debug from '@/utils/debug'  // ← Added

const expenseStore = useExpenseStore()
const loading = ref(false)

onMounted(async () => {
  debug.log('Component mounted')  // ← Changed
  try {
    loading.value = true
    await expenseStore.fetchExpenses()
    debug.log('Expenses loaded:', expenseStore.expenses.length)  // ← Changed
  } catch (error) {
    debug.error('Failed to load expenses:', error)  // ← Changed (still logs in production)
  } finally {
    loading.value = false
  }
})
</script>
```

## Advanced Usage

### Grouped Logging
```javascript
debug.group('User Data', () => {
  debug.log('Name:', user.name)
  debug.log('Email:', user.email)
  debug.log('Role:', user.role)
})
```

### Performance Timing
```javascript
const endTimer = debug.time('Data Processing')
// ... expensive operation ...
endTimer()  // Logs time elapsed in development only
```

### Table Display
```javascript
const expenses = [
  { id: 1, amount: 50, description: 'Lunch' },
  { id: 2, amount: 100, description: 'Gas' }
]
debug.table(expenses)  // Displays as table in console (dev only)
```

## Testing After Migration

### Development Build
```bash
npm run dev
```
✅ Debug logs should appear in console
✅ ESLint should show no console.log errors

### Production Build
```bash
npm run build
npm run preview
```
✅ No debug logs in console (only errors)
✅ Smaller bundle size
✅ Better performance

## Automated Migration (Optional)

### PowerShell Script
Create `scripts/migrate-console-logs.ps1`:

```powershell
$files = Get-ChildItem -Path frontend\src -Include *.vue,*.js -Recurse

foreach ($file in $files) {
    $content = Get-Content $file.FullName -Raw
    
    # Check if file uses console.log/warn
    if ($content -match 'console\.(log|warn)\(') {
        # Add debug import if not exists
        if ($content -notmatch "import debug from") {
            $content = $content -replace '(<script setup>)', "`$1`nimport debug from '@/utils/debug'"
        }
        
        # Replace console.log with debug.log
        $content = $content -replace 'console\.log\(', 'debug.log('
        $content = $content -replace 'console\.warn\(', 'debug.warn('
        
        # Save file
        Set-Content -Path $file.FullName -Value $content
        Write-Host "Updated: $($file.FullName)"
    }
}

Write-Host "Migration complete! Run 'npm run lint:fix' to verify."
```

Run:
```bash
.\scripts\migrate-console-logs.ps1
npm run lint:fix
```

## Verification Checklist

- [ ] All files import `debug` utility
- [ ] No `console.log` in production code (except `console.error`)
- [ ] ESLint passes without console warnings
- [ ] Development logs still visible in dev mode
- [ ] Production builds have no debug logs
- [ ] Bundle size reduced

## Benefits

✅ **Performance:** Debug code removed in production
✅ **Security:** No sensitive data leaking to console
✅ **Professional:** Clean production logs
✅ **Maintainable:** Centralized logging control
✅ **Flexible:** Easy to add log levels/formatters

## Next Steps

1. Update remaining 40+ files
2. Run `npm run lint:fix`
3. Test in development
4. Build for production
5. Verify no logs in production console

## Support

If you encounter issues:
1. Check ESLint errors: `npm run lint`
2. Verify import path: `@/utils/debug`
3. Ensure file exists: `frontend/src/utils/debug.js`
4. Check Vite alias configuration in `vite.config.js`
