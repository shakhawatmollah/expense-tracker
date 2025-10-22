import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const useSettingsStore = defineStore('settings', () => {
  // User Profile Settings
  const profile = ref({
    name: '',
    email: '',
    phone: '',
    avatar: '',
    timezone: 'UTC',
    language: 'en',
    dateFormat: 'MM/DD/YYYY',
    currency: 'USD'
  })

  // Notification Preferences
  const notifications = ref({
    email: {
      budgetAlerts: true,
      weeklyReports: true,
      monthlyReports: true,
      goalAchievements: true,
      systemUpdates: false
    },
    push: {
      budgetAlerts: true,
      expenseReminders: true,
      goalAchievements: true
    },
    inApp: {
      budgetAlerts: true,
      expenseAdded: true,
      goalAchievements: true
    }
  })

  // Budget Alert Thresholds
  const budgetAlerts = ref({
    warningThreshold: 75, // Percentage
    criticalThreshold: 90,
    enableSoundAlerts: true,
    alertFrequency: 'once' // 'once', 'daily', 'always'
  })

  // Privacy & Security
  const privacy = ref({
    showAmountsOnDashboard: true,
    enableBiometric: false,
    sessionTimeout: 30, // minutes
    twoFactorAuth: false,
    dataSharing: false
  })

  // Display Preferences
  const display = ref({
    theme: 'light', // 'light', 'dark', 'auto'
    compactMode: false,
    showCategoryIcons: true,
    showCharts: true,
    defaultView: 'dashboard', // 'dashboard', 'expenses', 'budgets'
    itemsPerPage: 10
  })

  // Export & Backup Settings
  const backup = ref({
    autoBackup: false,
    backupFrequency: 'weekly', // 'daily', 'weekly', 'monthly'
    includeAttachments: true,
    backupLocation: 'cloud' // 'cloud', 'local'
  })

  // Expense Defaults
  const expenseDefaults = ref({
    defaultCategory: null,
    roundToNearestDollar: false,
    askForReceipt: true,
    defaultPaymentMethod: 'cash' // 'cash', 'card', 'bank'
  })

  // Computed properties
  const isDarkMode = computed(() => {
    if (display.value.theme === 'auto') {
      return window.matchMedia('(prefers-color-scheme: dark)').matches
    }
    return display.value.theme === 'dark'
  })

  const formattedCurrency = computed(() => {
    const symbols = {
      USD: '$',
      EUR: '€',
      GBP: '£',
      JPY: '¥',
      INR: '₹',
      BDT: '৳'
    }
    return symbols[profile.value.currency] || profile.value.currency
  })

  // Actions
  const updateProfile = async updates => {
    try {
      // API call would go here
      // const response = await fetch('/api/settings/profile', {
      //   method: 'PUT',
      //   body: JSON.stringify(updates)
      // })

      Object.assign(profile.value, updates)
      localStorage.setItem('userProfile', JSON.stringify(profile.value))
      return { success: true, message: 'Profile updated successfully' }
    } catch (error) {
      console.error('Failed to update profile:', error)
      return { success: false, message: 'Failed to update profile' }
    }
  }

  const updateNotifications = async updates => {
    try {
      Object.assign(notifications.value, updates)
      localStorage.setItem('notificationSettings', JSON.stringify(notifications.value))
      return { success: true, message: 'Notification preferences updated' }
    } catch (error) {
      console.error('Failed to update notifications:', error)
      return { success: false, message: 'Failed to update notification settings' }
    }
  }

  const updateBudgetAlerts = async updates => {
    try {
      Object.assign(budgetAlerts.value, updates)
      localStorage.setItem('budgetAlertSettings', JSON.stringify(budgetAlerts.value))
      return { success: true, message: 'Budget alert settings updated' }
    } catch (error) {
      console.error('Failed to update budget alerts:', error)
      return { success: false, message: 'Failed to update alert settings' }
    }
  }

  const updatePrivacy = async updates => {
    try {
      Object.assign(privacy.value, updates)
      localStorage.setItem('privacySettings', JSON.stringify(privacy.value))
      return { success: true, message: 'Privacy settings updated' }
    } catch (error) {
      console.error('Failed to update privacy settings:', error)
      return { success: false, message: 'Failed to update privacy settings' }
    }
  }

  const updateDisplay = async updates => {
    try {
      Object.assign(display.value, updates)
      localStorage.setItem('displaySettings', JSON.stringify(display.value))

      // Apply theme immediately
      if (updates.theme) {
        applyTheme(updates.theme)
      }

      return { success: true, message: 'Display settings updated' }
    } catch (error) {
      console.error('Failed to update display settings:', error)
      return { success: false, message: 'Failed to update display settings' }
    }
  }

  const updateBackup = async updates => {
    try {
      Object.assign(backup.value, updates)
      localStorage.setItem('backupSettings', JSON.stringify(backup.value))
      return { success: true, message: 'Backup settings updated' }
    } catch (error) {
      console.error('Failed to update backup settings:', error)
      return { success: false, message: 'Failed to update backup settings' }
    }
  }

  const updateExpenseDefaults = async updates => {
    try {
      Object.assign(expenseDefaults.value, updates)
      localStorage.setItem('expenseDefaults', JSON.stringify(expenseDefaults.value))
      return { success: true, message: 'Expense defaults updated' }
    } catch (error) {
      console.error('Failed to update expense defaults:', error)
      return { success: false, message: 'Failed to update expense defaults' }
    }
  }

  const applyTheme = theme => {
    const root = document.documentElement
    let appliedTheme = theme

    if (theme === 'auto') {
      appliedTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
    }

    if (appliedTheme === 'dark') {
      root.classList.add('dark-mode')
    } else {
      root.classList.remove('dark-mode')
    }
  }

  const changePassword = async (currentPassword, newPassword) => {
    try {
      // API call would go here
      // const response = await fetch('/api/settings/change-password', {
      //   method: 'POST',
      //   body: JSON.stringify({ currentPassword, newPassword })
      // })

      return { success: true, message: 'Password changed successfully' }
    } catch (error) {
      console.error('Failed to change password:', error)
      return { success: false, message: 'Failed to change password' }
    }
  }

  const deleteAccount = async password => {
    try {
      // API call would go here
      // const response = await fetch('/api/settings/delete-account', {
      //   method: 'DELETE',
      //   body: JSON.stringify({ password })
      // })

      // Clear all data
      resetSettings()
      return { success: true, message: 'Account deleted successfully' }
    } catch (error) {
      console.error('Failed to delete account:', error)
      return { success: false, message: 'Failed to delete account' }
    }
  }

  const exportData = async (format = 'json') => {
    try {
      // Gather all user data
      const data = {
        profile: profile.value,
        notifications: notifications.value,
        budgetAlerts: budgetAlerts.value,
        privacy: privacy.value,
        display: display.value,
        backup: backup.value,
        expenseDefaults: expenseDefaults.value,
        exportDate: new Date().toISOString()
      }

      return { success: true, data, format }
    } catch (error) {
      console.error('Failed to export data:', error)
      return { success: false, message: 'Failed to export data' }
    }
  }

  const loadSettings = () => {
    try {
      const savedProfile = localStorage.getItem('userProfile')
      if (savedProfile) profile.value = JSON.parse(savedProfile)

      const savedNotifications = localStorage.getItem('notificationSettings')
      if (savedNotifications) notifications.value = JSON.parse(savedNotifications)

      const savedBudgetAlerts = localStorage.getItem('budgetAlertSettings')
      if (savedBudgetAlerts) budgetAlerts.value = JSON.parse(savedBudgetAlerts)

      const savedPrivacy = localStorage.getItem('privacySettings')
      if (savedPrivacy) privacy.value = JSON.parse(savedPrivacy)

      const savedDisplay = localStorage.getItem('displaySettings')
      if (savedDisplay) {
        display.value = JSON.parse(savedDisplay)
        applyTheme(display.value.theme)
      }

      const savedBackup = localStorage.getItem('backupSettings')
      if (savedBackup) backup.value = JSON.parse(savedBackup)

      const savedExpenseDefaults = localStorage.getItem('expenseDefaults')
      if (savedExpenseDefaults) expenseDefaults.value = JSON.parse(savedExpenseDefaults)
    } catch (error) {
      console.error('Failed to load settings:', error)
    }
  }

  const resetSettings = () => {
    localStorage.removeItem('userProfile')
    localStorage.removeItem('notificationSettings')
    localStorage.removeItem('budgetAlertSettings')
    localStorage.removeItem('privacySettings')
    localStorage.removeItem('displaySettings')
    localStorage.removeItem('backupSettings')
    localStorage.removeItem('expenseDefaults')
  }

  return {
    // State
    profile,
    notifications,
    budgetAlerts,
    privacy,
    display,
    backup,
    expenseDefaults,

    // Computed
    isDarkMode,
    formattedCurrency,

    // Actions
    updateProfile,
    updateNotifications,
    updateBudgetAlerts,
    updatePrivacy,
    updateDisplay,
    updateBackup,
    updateExpenseDefaults,
    changePassword,
    deleteAccount,
    exportData,
    loadSettings,
    resetSettings,
    applyTheme
  }
})
