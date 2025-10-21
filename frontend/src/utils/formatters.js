/**
 * Format currency amount
 * @param {number} amount - The amount to format
 * @param {string} currency - Currency code (default: USD)
 * @returns {string} Formatted currency string
 */
export function formatCurrency(amount, currency = 'USD') {
  if (amount === null || amount === undefined || isNaN(amount)) {
    return '0.00'
  }

  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: currency,
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  })
    .format(amount)
    .replace('$', '')
}

/**
 * Format date
 * @param {string|Date} date - Date to format
 * @param {string} format - Format type
 * @returns {string} Formatted date string
 */
export function formatDate(date, format = 'MMM DD, YYYY') {
  if (!date) return ''

  const dateObj = typeof date === 'string' ? new Date(date) : date

  if (isNaN(dateObj.getTime())) return ''

  const options = {}

  switch (format) {
    case 'MMM DD, YYYY':
      options.year = 'numeric'
      options.month = 'short'
      options.day = 'numeric'
      break
    case 'MM/DD/YYYY':
      options.year = 'numeric'
      options.month = '2-digit'
      options.day = '2-digit'
      break
    case 'YYYY-MM-DD':
      return dateObj.toISOString().split('T')[0]
    case 'relative':
      return formatRelativeDate(dateObj)
    default:
      options.year = 'numeric'
      options.month = 'short'
      options.day = 'numeric'
  }

  return dateObj.toLocaleDateString('en-US', options)
}

/**
 * Format relative date (e.g., "2 days ago")
 * @param {Date} date - Date to format
 * @returns {string} Relative date string
 */
function formatRelativeDate(date) {
  const now = new Date()
  const diffInMs = now - date
  const diffInDays = Math.floor(diffInMs / (1000 * 60 * 60 * 24))

  if (diffInDays === 0) return 'Today'
  if (diffInDays === 1) return 'Yesterday'
  if (diffInDays < 7) return `${diffInDays} days ago`
  if (diffInDays < 30) return `${Math.floor(diffInDays / 7)} weeks ago`
  if (diffInDays < 365) return `${Math.floor(diffInDays / 30)} months ago`

  return `${Math.floor(diffInDays / 365)} years ago`
}

/**
 * Format number with thousand separators
 * @param {number} number - Number to format
 * @returns {string} Formatted number string
 */
export function formatNumber(number) {
  if (number === null || number === undefined || isNaN(number)) {
    return '0'
  }

  return new Intl.NumberFormat('en-US').format(number)
}

/**
 * Format percentage
 * @param {number} value - Value to format as percentage
 * @param {number} decimals - Number of decimal places
 * @returns {string} Formatted percentage string
 */
export function formatPercentage(value, decimals = 1) {
  if (value === null || value === undefined || isNaN(value)) {
    return '0%'
  }

  return `${value.toFixed(decimals)}%`
}

/**
 * Truncate text with ellipsis
 * @param {string} text - Text to truncate
 * @param {number} maxLength - Maximum length
 * @returns {string} Truncated text
 */
export function truncateText(text, maxLength = 50) {
  if (!text || text.length <= maxLength) return text

  return text.substring(0, maxLength) + '...'
}

/**
 * Capitalize first letter
 * @param {string} text - Text to capitalize
 * @returns {string} Capitalized text
 */
export function capitalize(text) {
  if (!text) return ''

  return text.charAt(0).toUpperCase() + text.slice(1).toLowerCase()
}

/**
 * Format file size
 * @param {number} bytes - File size in bytes
 * @returns {string} Formatted file size
 */
export function formatFileSize(bytes) {
  if (bytes === 0) return '0 Bytes'

  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))

  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}
