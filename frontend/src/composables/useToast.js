import { ref, reactive } from 'vue'

// Global toast state
const toasts = ref([])
let toastId = 0

// Toast types
const TOAST_TYPES = {
  SUCCESS: 'success',
  ERROR: 'error',
  WARNING: 'warning',
  INFO: 'info'
}

// Default options
const DEFAULT_OPTIONS = {
  duration: 4000,
  position: 'top-right',
  closable: true,
  action: null
}

export function useToast() {
  // Add toast
  const addToast = (message, title = '', type = TOAST_TYPES.INFO, options = {}) => {
    const id = ++toastId
    const toast = {
      id,
      message,
      title,
      type,
      ...DEFAULT_OPTIONS,
      ...options,
      createdAt: Date.now()
    }

    toasts.value.push(toast)

    // Auto remove if duration is set
    if (toast.duration > 0) {
      setTimeout(() => {
        removeToast(id)
      }, toast.duration)
    }

    return id
  }

  // Remove toast
  const removeToast = id => {
    const index = toasts.value.findIndex(toast => toast.id === id)
    if (index > -1) {
      toasts.value.splice(index, 1)
    }
  }

  // Clear all toasts
  const clearAll = () => {
    toasts.value = []
  }

  // Convenience methods
  const success = (message, title = 'Success', options = {}) => {
    return addToast(message, title, TOAST_TYPES.SUCCESS, options)
  }

  const error = (message, title = 'Error', options = {}) => {
    return addToast(message, title, TOAST_TYPES.ERROR, {
      duration: 6000, // Longer duration for errors
      ...options
    })
  }

  const warning = (message, title = 'Warning', options = {}) => {
    return addToast(message, title, TOAST_TYPES.WARNING, options)
  }

  const info = (message, title = 'Info', options = {}) => {
    return addToast(message, title, TOAST_TYPES.INFO, options)
  }

  // Handle API errors
  const handleApiError = (err, fallbackMessage = 'An error occurred') => {
    let message = fallbackMessage
    let title = 'Error'

    if (err.response) {
      // Server responded with error status
      const status = err.response.status
      const data = err.response.data

      switch (status) {
        case 400:
          title = 'Bad Request'
          message = data.message || 'Invalid request'
          break
        case 401:
          title = 'Unauthorized'
          message = 'Please log in to continue'
          break
        case 403:
          title = 'Forbidden'
          message = "You don't have permission to perform this action"
          break
        case 404:
          title = 'Not Found'
          message = 'The requested resource was not found'
          break
        case 422:
          title = 'Validation Error'
          message = data.message || 'Please check your input'
          break
        case 429:
          title = 'Too Many Requests'
          message = 'Please try again later'
          break
        case 500:
          title = 'Server Error'
          message = 'Something went wrong on our end'
          break
        default:
          message = data.message || fallbackMessage
      }
    } else if (err.request) {
      // Network error
      title = 'Connection Error'
      message = 'Please check your internet connection'
    } else {
      // Something else happened
      message = err.message || fallbackMessage
    }

    return error(message, title)
  }

  // Template messages for common scenarios
  const templates = {
    // Auth templates
    loginSuccess: (name = 'User') => success(`Welcome back, ${name}!`, 'Login Successful'),
    loginError: () => error('Invalid credentials', 'Login Failed'),
    logoutSuccess: () => info('You have been logged out', 'Goodbye'),

    // CRUD templates
    itemCreated: itemType => success(`${itemType} created successfully`, 'Created'),
    itemUpdated: itemType => success(`${itemType} updated successfully`, 'Updated'),
    itemDeleted: itemType => success(`${itemType} deleted successfully`, 'Deleted'),

    // Expense templates
    expenseAdded: amount => success(`Expense of $${amount} added`, 'Expense Added'),
    expenseUpdated: amount => success(`Expense updated to $${amount}`, 'Expense Updated'),
    expenseDeleted: () => success('Expense deleted successfully', 'Expense Deleted'),

    // Category templates
    categoryCreated: name => success(`Category "${name}" created`, 'Category Added'),
    categoryUpdated: name => success(`Category "${name}" updated`, 'Category Updated'),
    categoryDeleted: name => success(`Category "${name}" deleted`, 'Category Deleted'),

    // Budget templates
    budgetCreated: (name, amount) => success(`Budget "${name}" created with limit of $${amount}`, 'Budget Created'),
    budgetUpdated: (name, amount) => success(`Budget "${name}" updated with limit of $${amount}`, 'Budget Updated'),
    budgetWarning: (name, percentage) => warning(`Budget "${name}" is ${percentage}% used`, 'Budget Warning'),
    budgetExceeded: name => error(`Budget "${name}" has been exceeded!`, 'Budget Exceeded'),

    // File templates
    exportSuccess: type => success(`${type} exported successfully`, 'Export Complete'),
    importSuccess: count => success(`${count} items imported successfully`, 'Import Complete'),

    // Network templates
    offline: () => warning('You are currently offline', 'Connection Lost'),
    online: () => info('Connection restored', 'Back Online'),

    // Feature templates
    featureComingSoon: feature => info(`${feature} feature coming soon!`, 'Feature Preview'),
    maintenanceMode: () => warning('The application is in maintenance mode', 'Maintenance'),

    // Validation templates
    requiredField: field => error(`${field} is required`, 'Validation Error'),
    invalidFormat: field => error(`Invalid ${field} format`, 'Validation Error'),

    // Save templates
    saveSuccess: () => success('Changes saved successfully', 'Saved'),
    saveError: () => error('Failed to save changes', 'Save Error'),
    autoSaved: () => info('Changes auto-saved', 'Auto-save', { duration: 2000 })
  }

  return {
    // State
    toasts,

    // Core methods
    addToast,
    removeToast,
    clearAll,

    // Convenience methods
    success,
    error,
    warning,
    info,

    // Utility methods
    handleApiError,
    templates,

    // Constants
    TOAST_TYPES
  }
}

// Export for direct usage
export { TOAST_TYPES }
