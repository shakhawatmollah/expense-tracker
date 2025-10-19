import axios from 'axios'

const API_BASE = 'http://127.0.0.1:8000/api'

// Create axios instance
const api = axios.create({
  baseURL: API_BASE,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
})

async function testAnalyticsFlow() {
  try {
    console.log('🚀 Starting Analytics Testing Flow...')
    
    // Step 1: Register or Login
    console.log('\n📝 Step 1: Authentication')
    let authResponse
    
    try {
      // Try to register a test user
      authResponse = await api.post('/auth/register', {
        name: 'Test User',
        email: 'test@example.com',
        password: 'password123',
        password_confirmation: 'password123'
      })
      console.log('✅ User registered successfully')
    } catch (error) {
      if (error.response?.status === 422) {
        // User might already exist, try to login
        console.log('ℹ️  User exists, attempting login...')
        authResponse = await api.post('/auth/login', {
          email: 'test@example.com',
          password: 'password123'
        })
        console.log('✅ User logged in successfully')
      } else {
        throw error
      }
    }
    
    const token = authResponse.data.token
    const user = authResponse.data.user
    console.log(`✅ Authenticated as: ${user.name} (${user.email})`)
    
    // Step 2: Set up authenticated requests
    api.defaults.headers.common['Authorization'] = `Bearer ${token}`
    
    // Step 3: Test Financial Health API
    console.log('\n💊 Step 2: Testing Financial Health API')
    const healthResponse = await api.get('/analytics/financial-health')
    console.log('✅ Financial Health API Response:')
    console.log(JSON.stringify(healthResponse.data, null, 2))
    
    // Step 4: Test Dashboard Analytics API
    console.log('\n📊 Step 3: Testing Dashboard Analytics API')
    const dashboardResponse = await api.get('/analytics/dashboard')
    console.log('✅ Dashboard Analytics API Response:')
    console.log(JSON.stringify(dashboardResponse.data, null, 2))
    
    // Step 5: Test Spending Patterns API
    console.log('\n🔍 Step 4: Testing Spending Patterns API')
    const patternsResponse = await api.get('/analytics/patterns')
    console.log('✅ Spending Patterns API Response:')
    console.log(JSON.stringify(patternsResponse.data, null, 2))
    
    // Step 6: Test User Insights API
    console.log('\n💡 Step 5: Testing User Insights API')
    const insightsResponse = await api.get('/analytics/insights')
    console.log('✅ User Insights API Response:')
    console.log(JSON.stringify(insightsResponse.data, null, 2))
    
    console.log('\n🎉 Analytics Testing Flow Completed Successfully!')
    
    return {
      user,
      token,
      financialHealth: healthResponse.data,
      dashboard: dashboardResponse.data,
      patterns: patternsResponse.data,
      insights: insightsResponse.data
    }
    
  } catch (error) {
    console.error('❌ Analytics Testing Failed:', error.response?.data || error.message)
    throw error
  }
}

// Export for use in Node.js
if (typeof module !== 'undefined' && module.exports) {
  module.exports = { testAnalyticsFlow }
} else {
  // Browser environment
  window.testAnalyticsFlow = testAnalyticsFlow
}