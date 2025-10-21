// Test script to verify budget API functionality
// Open browser console and paste this code to test

async function testBudgetAPI() {
  const API_BASE = 'http://127.0.0.1:8000/api'

  console.log('🔍 Testing Budget API...')

  try {
    // Test login
    console.log('📧 Testing login...')
    const loginResponse = await fetch(`${API_BASE}/auth/login`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json'
      },
      body: JSON.stringify({
        email: 'admin@example.com',
        password: 'admin123'
      })
    })

    if (!loginResponse.ok) {
      throw new Error(`Login failed: ${loginResponse.status} ${loginResponse.statusText}`)
    }

    const loginData = await loginResponse.json()
    console.log('✅ Login successful:', loginData)

    const token = loginData.token

    // Test budget fetch
    console.log('💰 Testing budget fetch...')
    const budgetResponse = await fetch(`${API_BASE}/budgets`, {
      method: 'GET',
      headers: {
        Authorization: `Bearer ${token}`,
        Accept: 'application/json'
      }
    })

    if (!budgetResponse.ok) {
      throw new Error(`Budget fetch failed: ${budgetResponse.status} ${budgetResponse.statusText}`)
    }

    const budgetData = await budgetResponse.json()
    console.log('✅ Budget data received:', budgetData)

    // Test normalization
    console.log('🔧 Testing normalization...')
    const budgets = budgetData.data || []
    const normalizedBudgets = budgets.map(budget => {
      const amount =
        budget.amount && typeof budget.amount === 'object' ? (budget.amount.raw ?? 0) : (budget.amount ?? 0)
      const spent =
        budget.spent_amount && typeof budget.spent_amount === 'object'
          ? (budget.spent_amount.raw ?? 0)
          : (budget.spent_amount ?? 0)
      const remaining =
        budget.remaining_amount && typeof budget.remaining_amount === 'object'
          ? (budget.remaining_amount.raw ?? 0)
          : (budget.remaining_amount ?? amount - spent)

      return {
        ...budget,
        amount,
        spent_amount: spent,
        remaining_amount: remaining
      }
    })

    console.log('✅ Normalized budgets:', normalizedBudgets)

    // Calculate totals
    const totalAmount = normalizedBudgets.reduce((sum, budget) => sum + budget.amount, 0)
    const totalSpent = normalizedBudgets.reduce((sum, budget) => sum + budget.spent_amount, 0)
    const overallProgress = totalAmount === 0 ? 0 : Math.round((totalSpent / totalAmount) * 100)

    console.log('📊 Calculated totals:', {
      totalAmount,
      totalSpent,
      overallProgress: `${overallProgress}%`
    })

    return {
      success: true,
      budgets: normalizedBudgets,
      totals: { totalAmount, totalSpent, overallProgress }
    }
  } catch (error) {
    console.error('❌ Error:', error)
    return { success: false, error: error.message }
  }
}

// Run the test
testBudgetAPI()
