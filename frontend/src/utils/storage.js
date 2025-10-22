/**
 * Safe localStorage wrapper with error handling
 * Prevents JSON parse errors and localStorage quota errors
 */

/**
 * Safely get and parse JSON from localStorage
 * @param {string} key - The localStorage key
 * @param {any} defaultValue - Default value if key doesn't exist or parse fails
 * @returns {any} Parsed value or defaultValue
 */
export const getItem = (key, defaultValue = null) => {
  try {
    const item = localStorage.getItem(key)

    // Return default if item doesn't exist
    if (item === null || item === undefined) {
      return defaultValue
    }

    // Try to parse as JSON
    try {
      return JSON.parse(item)
    } catch (parseError) {
      // If not valid JSON, return the raw string
      console.warn(`Failed to parse localStorage item "${key}":`, parseError)

      // If it's clearly corrupted (starts with { or [), remove it
      if (item.startsWith('{') || item.startsWith('[')) {
        console.error(`Removing corrupted JSON data for key "${key}"`)
        localStorage.removeItem(key)
        return defaultValue
      }

      // Otherwise return the raw value
      return item
    }
  } catch (error) {
    console.error(`Error reading localStorage key "${key}":`, error)
    return defaultValue
  }
}

/**
 * Safely set JSON value in localStorage
 * @param {string} key - The localStorage key
 * @param {any} value - Value to store (will be JSON stringified)
 * @returns {boolean} Success status
 */
export const setItem = (key, value) => {
  try {
    const serialized = JSON.stringify(value)
    localStorage.setItem(key, serialized)
    return true
  } catch (error) {
    console.error(`Error writing to localStorage key "${key}":`, error)

    // Handle quota exceeded errors
    if (error.name === 'QuotaExceededError') {
      console.error('localStorage quota exceeded. Consider clearing old data.')
    }

    return false
  }
}

/**
 * Safely remove item from localStorage
 * @param {string} key - The localStorage key
 * @returns {boolean} Success status
 */
export const removeItem = key => {
  try {
    localStorage.removeItem(key)
    return true
  } catch (error) {
    console.error(`Error removing localStorage key "${key}":`, error)
    return false
  }
}

/**
 * Clear all localStorage data
 * @returns {boolean} Success status
 */
export const clear = () => {
  try {
    localStorage.clear()
    return true
  } catch (error) {
    console.error('Error clearing localStorage:', error)
    return false
  }
}

/**
 * Check if localStorage is available
 * @returns {boolean} True if localStorage is available
 */
export const isAvailable = () => {
  try {
    const testKey = '__localStorage_test__'
    localStorage.setItem(testKey, 'test')
    localStorage.removeItem(testKey)
    return true
  } catch (error) {
    console.warn('localStorage is not available:', error)
    return false
  }
}

/**
 * Get all keys in localStorage
 * @returns {string[]} Array of keys
 */
export const getAllKeys = () => {
  try {
    return Object.keys(localStorage)
  } catch (error) {
    console.error('Error getting localStorage keys:', error)
    return []
  }
}

/**
 * Get storage usage information
 * @returns {object} Storage info
 */
export const getStorageInfo = () => {
  try {
    let totalSize = 0
    const items = {}

    for (const key in localStorage) {
      if (localStorage.hasOwnProperty(key)) {
        const size = localStorage.getItem(key).length
        totalSize += size
        items[key] = {
          size,
          sizeKB: (size / 1024).toFixed(2)
        }
      }
    }

    return {
      totalSize,
      totalSizeKB: (totalSize / 1024).toFixed(2),
      totalSizeMB: (totalSize / 1024 / 1024).toFixed(2),
      itemCount: Object.keys(items).length,
      items
    }
  } catch (error) {
    console.error('Error getting storage info:', error)
    return null
  }
}

/**
 * Clean up corrupted localStorage entries
 * @returns {number} Number of items cleaned
 */
export const cleanCorrupted = () => {
  let cleaned = 0

  try {
    const keys = Object.keys(localStorage)

    for (const key of keys) {
      const value = localStorage.getItem(key)

      // Try to parse JSON entries
      if (value && (value.startsWith('{') || value.startsWith('['))) {
        try {
          JSON.parse(value)
        } catch (error) {
          console.warn(`Removing corrupted entry: ${key}`)
          localStorage.removeItem(key)
          cleaned++
        }
      }
    }
  } catch (error) {
    console.error('Error cleaning localStorage:', error)
  }

  return cleaned
}

// Export as default object
export default {
  getItem,
  setItem,
  removeItem,
  clear,
  isAvailable,
  getAllKeys,
  getStorageInfo,
  cleanCorrupted
}
