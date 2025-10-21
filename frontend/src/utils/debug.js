/**
 * Debug utility for development logging
 * Automatically disabled in production builds
 */

const isDevelopment = import.meta.env.DEV

/**
 * Log debug information (only in development)
 * @param {...any} args - Arguments to log
 */
export const log = (...args) => {
  if (isDevelopment) {
    console.log('[DEBUG]', ...args)
  }
}

/**
 * Log warning information (only in development)
 * @param {...any} args - Arguments to log
 */
export const warn = (...args) => {
  if (isDevelopment) {
    console.warn('[WARN]', ...args)
  }
}

/**
 * Log error information (always logged)
 * @param {...any} args - Arguments to log
 */
export const error = (...args) => {
  console.error('[ERROR]', ...args)
}

/**
 * Log table data (only in development)
 * @param {any} data - Data to display in table format
 */
export const table = data => {
  if (isDevelopment) {
    console.table(data)
  }
}

/**
 * Log grouped information (only in development)
 * @param {string} label - Group label
 * @param {Function} callback - Function containing logs to group
 */
export const group = (label, callback) => {
  if (isDevelopment) {
    console.group(label)
    callback()
    console.groupEnd()
  }
}

/**
 * Performance timing utility (only in development)
 * @param {string} label - Timer label
 * @returns {Function} - Function to end the timer
 */
export const time = label => {
  if (isDevelopment) {
    console.time(label)
    return () => console.timeEnd(label)
  }
  return () => {} // No-op in production
}

// Export as default object
export default {
  log,
  warn,
  error,
  table,
  group,
  time
}
