import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const useNotificationsStore = defineStore('notifications', () => {
  // State
  const notifications = ref([])
  const history = ref([])
  const preferences = ref({
    enabled: true,
    sound: true,
    desktop: true,
    email: false,
    positions: {
      default: 'top-right'
    },
    duration: {
      success: 5000,
      error: 8000,
      warning: 6000,
      info: 4000
    }
  })
  
  let notificationId = 0

  // Computed
  const activeNotifications = computed(() => 
    notifications.value.filter(n => !n.dismissed)
  )
  
  const unreadCount = computed(() => 
    history.value.filter(n => !n.read).length
  )
  
  const hasUnread = computed(() => unreadCount.value > 0)

  // Notification types configuration
  const notificationTypes = {
    // Financial notifications
    EXPENSE_ADDED: {
      type: 'expense',
      icon: '💰',
      iconClass: 'fas fa-receipt',
      color: '#10B981',
      sound: '/sounds/expense.mp3',
      priority: 'medium'
    },
    EXPENSE_UPDATED: {
      type: 'expense',
      icon: '✏️',
      iconClass: 'fas fa-edit',
      color: '#3B82F6',
      priority: 'low'
    },
    EXPENSE_DELETED: {
      type: 'expense',
      icon: '🗑️',
      iconClass: 'fas fa-trash',
      color: '#EF4444',
      priority: 'low'
    },
    
    // Budget notifications
    BUDGET_WARNING: {
      type: 'budget',
      icon: '⚠️',
      iconClass: 'fas fa-exclamation-triangle',
      color: '#F59E0B',
      sound: '/sounds/warning.mp3',
      priority: 'high'
    },
    BUDGET_CRITICAL: {
      type: 'budget',
      icon: '🚨',
      iconClass: 'fas fa-exclamation-circle',
      color: '#EF4444',
      sound: '/sounds/alert.mp3',
      priority: 'critical'
    },
    BUDGET_EXCEEDED: {
      type: 'budget',
      icon: '❌',
      iconClass: 'fas fa-ban',
      color: '#DC2626',
      sound: '/sounds/alert.mp3',
      priority: 'critical'
    },
    BUDGET_CREATED: {
      type: 'budget',
      icon: '🎯',
      iconClass: 'fas fa-bullseye',
      color: '#8B5CF6',
      priority: 'low'
    },
    
    // Goal notifications
    GOAL_ACHIEVED: {
      type: 'achievement',
      icon: '🏆',
      iconClass: 'fas fa-trophy',
      color: '#F59E0B',
      sound: '/sounds/achievement.mp3',
      priority: 'high'
    },
    GOAL_MILESTONE: {
      type: 'goal',
      icon: '🎯',
      iconClass: 'fas fa-flag-checkered',
      color: '#10B981',
      priority: 'medium'
    },
    
    // Category notifications
    CATEGORY_CREATED: {
      type: 'category',
      icon: '📁',
      iconClass: 'fas fa-folder-plus',
      color: '#6366F1',
      priority: 'low'
    },
    CATEGORY_UPDATED: {
      type: 'category',
      icon: '📝',
      iconClass: 'fas fa-edit',
      color: '#3B82F6',
      priority: 'low'
    },
    
    // Export notifications
    EXPORT_STARTED: {
      type: 'sync',
      icon: '⬇️',
      iconClass: 'fas fa-download',
      color: '#6366F1',
      priority: 'low'
    },
    EXPORT_COMPLETED: {
      type: 'sync',
      icon: '✅',
      iconClass: 'fas fa-check-circle',
      color: '#10B981',
      priority: 'medium'
    },
    EXPORT_FAILED: {
      type: 'error',
      icon: '❌',
      iconClass: 'fas fa-times-circle',
      color: '#EF4444',
      priority: 'medium'
    },
    
    // Data sync notifications
    SYNC_STARTED: {
      type: 'sync',
      icon: '🔄',
      iconClass: 'fas fa-sync-alt fa-spin',
      color: '#6366F1',
      priority: 'low'
    },
    SYNC_COMPLETED: {
      type: 'sync',
      icon: '✓',
      iconClass: 'fas fa-check',
      color: '#10B981',
      priority: 'low'
    },
    
    // System notifications
    WELCOME: {
      type: 'info',
      icon: '👋',
      iconClass: 'fas fa-hand-wave',
      color: '#3B82F6',
      priority: 'low'
    },
    SUCCESS: {
      type: 'success',
      icon: '✓',
      iconClass: 'fas fa-check-circle',
      color: '#10B981',
      priority: 'low'
    },
    ERROR: {
      type: 'error',
      icon: '✗',
      iconClass: 'fas fa-times-circle',
      color: '#EF4444',
      priority: 'medium'
    },
    WARNING: {
      type: 'warning',
      icon: '⚠',
      iconClass: 'fas fa-exclamation-triangle',
      color: '#F59E0B',
      priority: 'medium'
    },
    INFO: {
      type: 'info',
      icon: 'ℹ',
      iconClass: 'fas fa-info-circle',
      color: '#3B82F6',
      priority: 'low'
    }
  }

  // Methods
  const createNotification = (options) => {
    if (!preferences.value.enabled) return null

    const id = ++notificationId
    const typeConfig = notificationTypes[options.notificationType] || notificationTypes.INFO
    
    const notification = {
      id,
      timestamp: new Date(),
      read: false,
      dismissed: false,
      paused: false,
      ...typeConfig,
      ...options,
      duration: options.duration || preferences.value.duration[typeConfig.type] || 5000,
      position: options.position || preferences.value.positions.default
    }

    notifications.value.push(notification)
    history.value.unshift({ ...notification })

    // Play sound if enabled
    if (preferences.value.sound && typeConfig.sound) {
      playNotificationSound(typeConfig.sound)
    }

    // Show desktop notification if enabled
    if (preferences.value.desktop) {
      showDesktopNotification(notification)
    }

    // Auto dismiss
    if (!notification.persistent && notification.duration > 0) {
      setTimeout(() => {
        dismissNotification(id)
      }, notification.duration)
    }

    // Store in localStorage
    saveToLocalStorage()

    return id
  }

  const dismissNotification = (id) => {
    const notification = notifications.value.find(n => n.id === id)
    if (notification) {
      notification.dismissed = true
      setTimeout(() => {
        notifications.value = notifications.value.filter(n => n.id !== id)
      }, 300) // Allow transition animation
    }
  }

  const dismissAll = () => {
    notifications.value.forEach(n => {
      n.dismissed = true
    })
    setTimeout(() => {
      notifications.value = []
    }, 300)
  }

  const markAsRead = (id) => {
    const notification = history.value.find(n => n.id === id)
    if (notification) {
      notification.read = true
      saveToLocalStorage()
    }
  }

  const markAllAsRead = () => {
    history.value.forEach(n => {
      n.read = true
    })
    saveToLocalStorage()
  }

  const removeFromHistory = (id) => {
    history.value = history.value.filter(n => n.id !== id)
    saveToLocalStorage()
  }

  const clearHistory = () => {
    history.value = []
    saveToLocalStorage()
  }

  const pauseNotification = (id) => {
    const notification = notifications.value.find(n => n.id === id)
    if (notification) {
      notification.paused = true
    }
  }

  const resumeNotification = (id) => {
    const notification = notifications.value.find(n => n.id === id)
    if (notification) {
      notification.paused = false
    }
  }

  // Specialized notification methods
  const notifyExpenseAdded = (expense) => {
    return createNotification({
      notificationType: 'EXPENSE_ADDED',
      title: 'Expense Added',
      message: `${expense.description} - $${expense.amount.toFixed(2)}`,
      data: { expense },
      actions: [
        {
          id: 'view',
          label: 'View',
          type: 'primary',
          callback: () => {
            // Navigate to expense details
          }
        },
        {
          id: 'undo',
          label: 'Undo',
          type: 'secondary',
          callback: () => {
            // Undo expense addition
          }
        }
      ]
    })
  }

  const notifyBudgetAlert = (budget, percentage) => {
    let notificationType = 'BUDGET_WARNING'
    let title = 'Budget Warning'
    let message = `You've used ${percentage}% of your ${budget.name} budget`

    if (percentage >= 100) {
      notificationType = 'BUDGET_EXCEEDED'
      title = 'Budget Exceeded!'
      message = `You've exceeded your ${budget.name} budget by ${(percentage - 100).toFixed(1)}%`
    } else if (percentage >= 90) {
      notificationType = 'BUDGET_CRITICAL'
      title = 'Budget Critical'
      message = `You've used ${percentage}% of your ${budget.name} budget - almost at limit!`
    }

    return createNotification({
      notificationType,
      title,
      message,
      data: { budget, percentage },
      persistent: percentage >= 100,
      actions: [
        {
          id: 'view-budget',
          label: 'View Budget',
          type: 'primary',
          callback: () => {
            // Navigate to budget details
          }
        }
      ]
    })
  }

  const notifyGoalAchieved = (goal) => {
    return createNotification({
      notificationType: 'GOAL_ACHIEVED',
      title: '🎉 Goal Achieved!',
      message: `Congratulations! You've achieved your goal: ${goal.name}`,
      data: { goal },
      duration: 10000,
      actions: [
        {
          id: 'celebrate',
          label: 'View Achievement',
          type: 'primary',
          callback: () => {
            // Show achievement details
          }
        }
      ]
    })
  }

  const notifyExport = (status, message, data = {}) => {
    const notificationTypes = {
      started: 'EXPORT_STARTED',
      completed: 'EXPORT_COMPLETED',
      failed: 'EXPORT_FAILED'
    }

    return createNotification({
      notificationType: notificationTypes[status] || 'INFO',
      title: status === 'started' ? 'Exporting Data...' : 
             status === 'completed' ? 'Export Complete' : 'Export Failed',
      message,
      data,
      persistent: status === 'started'
    })
  }

  const notifySync = (status, message = '') => {
    return createNotification({
      notificationType: status === 'started' ? 'SYNC_STARTED' : 'SYNC_COMPLETED',
      title: status === 'started' ? 'Syncing...' : 'Sync Complete',
      message: message || (status === 'started' ? 'Updating your data...' : 'All data is up to date'),
      duration: status === 'started' ? 0 : 3000,
      persistent: status === 'started'
    })
  }

  const notifyCategoryChange = (action, category) => {
    const types = {
      created: 'CATEGORY_CREATED',
      updated: 'CATEGORY_UPDATED'
    }

    return createNotification({
      notificationType: types[action] || 'INFO',
      title: action === 'created' ? 'Category Created' : 'Category Updated',
      message: `${category.name} has been ${action}`,
      data: { category }
    })
  }

  const notifyWelcome = (userName) => {
    return createNotification({
      notificationType: 'WELCOME',
      title: `Welcome back, ${userName}!`,
      message: 'Your dashboard is ready with the latest data',
      duration: 6000
    })
  }

  const notifySuccess = (title, message, options = {}) => {
    return createNotification({
      notificationType: 'SUCCESS',
      title,
      message,
      ...options
    })
  }

  const notifyError = (title, message, options = {}) => {
    return createNotification({
      notificationType: 'ERROR',
      title,
      message,
      duration: 8000,
      ...options
    })
  }

  const notifyWarning = (title, message, options = {}) => {
    return createNotification({
      notificationType: 'WARNING',
      title,
      message,
      duration: 6000,
      ...options
    })
  }

  const notifyInfo = (title, message, options = {}) => {
    return createNotification({
      notificationType: 'INFO',
      title,
      message,
      ...options
    })
  }

  // Helper functions
  const playNotificationSound = (soundUrl) => {
    try {
      const audio = new Audio(soundUrl)
      audio.volume = 0.5
      audio.play().catch(() => {
        // Ignore if audio fails to play
      })
    } catch (error) {
      console.warn('Failed to play notification sound:', error)
    }
  }

  const showDesktopNotification = (notification) => {
    if ('Notification' in window && Notification.permission === 'granted') {
      try {
        new Notification(notification.title, {
          body: notification.message,
          icon: '/favicon.ico',
          badge: '/favicon.ico',
          tag: notification.id.toString(),
          requireInteraction: notification.priority === 'critical'
        })
      } catch (error) {
        console.warn('Failed to show desktop notification:', error)
      }
    }
  }

  const requestDesktopPermission = async () => {
    if ('Notification' in window && Notification.permission === 'default') {
      const permission = await Notification.requestPermission()
      preferences.value.desktop = permission === 'granted'
      saveToLocalStorage()
      return permission === 'granted'
    }
    return false
  }

  const saveToLocalStorage = () => {
    try {
      localStorage.setItem('notification_history', JSON.stringify(history.value.slice(0, 50))) // Keep last 50
      localStorage.setItem('notification_preferences', JSON.stringify(preferences.value))
    } catch (error) {
      console.warn('Failed to save notifications to localStorage:', error)
    }
  }

  const loadFromLocalStorage = () => {
    try {
      const savedHistory = localStorage.getItem('notification_history')
      const savedPreferences = localStorage.getItem('notification_preferences')
      
      if (savedHistory) {
        history.value = JSON.parse(savedHistory).map(n => ({
          ...n,
          timestamp: new Date(n.timestamp)
        }))
      }
      
      if (savedPreferences) {
        preferences.value = { ...preferences.value, ...JSON.parse(savedPreferences) }
      }
    } catch (error) {
      console.warn('Failed to load notifications from localStorage:', error)
    }
  }

  const updatePreferences = (newPreferences) => {
    preferences.value = { ...preferences.value, ...newPreferences }
    saveToLocalStorage()
  }

  // Initialize
  loadFromLocalStorage()

  return {
    // State
    notifications,
    history,
    preferences,
    activeNotifications,
    unreadCount,
    hasUnread,
    
    // Methods
    createNotification,
    dismissNotification,
    dismissAll,
    markAsRead,
    markAllAsRead,
    removeFromHistory,
    clearHistory,
    pauseNotification,
    resumeNotification,
    
    // Specialized methods
    notifyExpenseAdded,
    notifyBudgetAlert,
    notifyGoalAchieved,
    notifyExport,
    notifySync,
    notifyCategoryChange,
    notifyWelcome,
    notifySuccess,
    notifyError,
    notifyWarning,
    notifyInfo,
    
    // Helper methods
    requestDesktopPermission,
    updatePreferences,
    loadFromLocalStorage,
    saveToLocalStorage
  }
})
