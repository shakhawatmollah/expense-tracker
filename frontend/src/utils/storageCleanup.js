/**
 * localStorage Cleanup Utility
 * Run this in browser console to fix corrupted data: cleanStorage()
 */

import storage from '@/utils/storage'

/**
 * Clean corrupted localStorage entries and provide report
 */
export const cleanStorage = () => {
  console.log('🧹 Starting localStorage cleanup...')

  // Check if localStorage is available
  if (!storage.isAvailable()) {
    console.error('❌ localStorage is not available')
    return
  }

  // Get current storage info
  const before = storage.getStorageInfo()
  console.log('📊 Storage before cleanup:')
  console.table({
    'Total Size (KB)': before.totalSizeKB,
    'Total Size (MB)': before.totalSizeMB,
    'Item Count': before.itemCount
  })

  // Clean corrupted entries
  const cleaned = storage.cleanCorrupted()

  // Get storage info after cleanup
  const after = storage.getStorageInfo()
  console.log('\n📊 Storage after cleanup:')
  console.table({
    'Total Size (KB)': after.totalSizeKB,
    'Total Size (MB)': after.totalSizeMB,
    'Item Count': after.itemCount
  })

  console.log(`\n✅ Cleanup complete: ${cleaned} corrupted items removed`)

  // Show remaining items
  if (after.itemCount > 0) {
    console.log('\n📦 Remaining items:')
    console.table(after.items)
  }

  return {
    before,
    after,
    cleaned
  }
}

/**
 * Clear all auth-related data
 */
export const clearAuthData = () => {
  console.log('🔐 Clearing auth data...')

  const authKeys = ['token', 'user', 'refresh_token', 'expires_at']
  let removed = 0

  authKeys.forEach(key => {
    if (storage.removeItem(key)) {
      console.log(`✅ Removed: ${key}`)
      removed++
    }
  })

  console.log(`\n✅ ${removed} auth items removed`)
  return removed
}

/**
 * Clear all application data
 */
export const clearAllData = () => {
  console.log('🗑️ Clearing all localStorage data...')

  if (confirm('Are you sure you want to clear ALL localStorage data? This will log you out.')) {
    const before = storage.getAllKeys().length
    storage.clear()
    const after = storage.getAllKeys().length

    console.log(`✅ Cleared ${before - after} items`)
    console.log('🔄 Please refresh the page')

    return true
  }

  return false
}

/**
 * Test localStorage operations
 */
export const testStorage = () => {
  console.log('🧪 Testing localStorage operations...')

  try {
    // Test write
    const testKey = '__test_key__'
    const testData = { test: true, timestamp: Date.now() }

    console.log('📝 Testing write...')
    const writeResult = storage.setItem(testKey, testData)
    console.log(writeResult ? '✅ Write successful' : '❌ Write failed')

    // Test read
    console.log('📖 Testing read...')
    const readData = storage.getItem(testKey)
    console.log(readData ? '✅ Read successful' : '❌ Read failed')
    console.log('Data:', readData)

    // Test remove
    console.log('🗑️ Testing remove...')
    const removeResult = storage.removeItem(testKey)
    console.log(removeResult ? '✅ Remove successful' : '❌ Remove failed')

    console.log('\n✅ All tests passed')
    return true
  } catch (error) {
    console.error('❌ Test failed:', error)
    return false
  }
}

/**
 * Validate auth data
 */
export const validateAuthData = () => {
  console.log('🔍 Validating auth data...')

  const token = storage.getItem('token')
  const user = storage.getItem('user')

  const validation = {
    tokenExists: !!token,
    tokenValid: typeof token === 'string' && token.length > 0,
    userExists: !!user,
    userValid: user && typeof user === 'object' && user.id && user.email
  }

  console.table(validation)

  if (!validation.tokenValid || !validation.userValid) {
    console.warn('⚠️ Auth data appears invalid. Consider running clearAuthData()')
  } else {
    console.log('✅ Auth data is valid')
  }

  return validation
}

// Make functions available globally for console use
if (typeof window !== 'undefined') {
  window.cleanStorage = cleanStorage
  window.clearAuthData = clearAuthData
  window.clearAllData = clearAllData
  window.testStorage = testStorage
  window.validateAuthData = validateAuthData
}

export default {
  cleanStorage,
  clearAuthData,
  clearAllData,
  testStorage,
  validateAuthData
}
