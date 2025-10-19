<template>
  <Teleport to="body">
    <div class="notification-container" :class="{ 'mobile': isMobile }">
      <TransitionGroup name="notification" tag="div" class="notifications-list">
        <div
          v-for="notification in notifications"
          :key="notification.id"
          :class="[
            'notification',
            notification.type,
            notification.position || 'top-right',
            { 'persistent': notification.persistent }
          ]"
          @click="handleNotificationClick(notification)"
          @mouseenter="pauseTimer(notification.id)"
          @mouseleave="resumeTimer(notification.id)"
        >
          <!-- Notification Icon -->
          <div class="notification-icon">
            <i :class="getNotificationIcon(notification.type)"></i>
          </div>

          <!-- Notification Content -->
          <div class="notification-content">
            <div class="notification-header">
              <h4 class="notification-title">{{ notification.title }}</h4>
              <div class="notification-meta">
                <span class="notification-time">{{ formatTime(notification.timestamp) }}</span>
                <button 
                  v-if="!notification.persistent"
                  @click.stop="dismissNotification(notification.id)"
                  class="notification-close"
                  title="Dismiss"
                >
                  <i class="fas fa-times"></i>
                </button>
              </div>
            </div>
            
            <p class="notification-message">{{ notification.message }}</p>
            
            <!-- Action Buttons -->
            <div v-if="notification.actions" class="notification-actions">
              <button
                v-for="action in notification.actions"
                :key="action.id"
                @click.stop="handleAction(action, notification)"
                :class="['action-btn', action.type || 'secondary']"
              >
                <i v-if="action.icon" :class="action.icon"></i>
                <span>{{ action.label }}</span>
              </button>
            </div>
          </div>

          <!-- Progress Bar -->
          <div 
            v-if="!notification.persistent && notification.duration"
            class="notification-progress"
            :style="{ 
              animationDuration: `${notification.duration}ms`,
              animationPlayState: notification.paused ? 'paused' : 'running'
            }"
          ></div>
        </div>
      </TransitionGroup>
    </div>

    <!-- Notification Center Toggle -->
    <button 
      v-if="hasNotifications"
      @click="toggleNotificationCenter"
      class="notification-center-toggle"
      :class="{ 'has-unread': hasUnreadNotifications }"
      title="Notification Center"
    >
      <i class="fas fa-bell"></i>
      <span v-if="unreadCount > 0" class="notification-badge">{{ unreadCount }}</span>
    </button>

    <!-- Notification Center -->
    <div 
      v-if="showNotificationCenter"
      class="notification-center"
      :class="{ 'mobile': isMobile }"
    >
      <div class="notification-center-header">
        <h3>Notifications</h3>
        <div class="header-actions">
          <button @click="markAllAsRead" class="action-btn small" v-if="hasUnreadNotifications">
            <i class="fas fa-check-double"></i>
            Mark all read
          </button>
          <button @click="clearAllNotifications" class="action-btn small danger">
            <i class="fas fa-trash"></i>
            Clear all
          </button>
          <button @click="closeNotificationCenter" class="close-btn">
            <i class="fas fa-times"></i>
          </button>
        </div>
      </div>

      <div class="notification-center-content">
        <div v-if="allNotifications.length === 0" class="empty-notifications">
          <div class="empty-icon">
            <i class="fas fa-bell-slash"></i>
          </div>
          <h4>No notifications</h4>
          <p>You're all caught up! New notifications will appear here.</p>
        </div>

        <div v-else class="notifications-history">
          <div
            v-for="notification in allNotifications"
            :key="notification.id"
            :class="['history-notification', { 'unread': !notification.read }]"
            @click="handleHistoryNotificationClick(notification)"
          >
            <div class="history-icon">
              <i :class="getNotificationIcon(notification.type)"></i>
            </div>
            <div class="history-content">
              <h5>{{ notification.title }}</h5>
              <p>{{ notification.message }}</p>
              <span class="history-time">{{ formatRelativeTime(notification.timestamp) }}</span>
            </div>
            <button 
              @click.stop="removeFromHistory(notification.id)"
              class="history-remove"
            >
              <i class="fas fa-times"></i>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Notification Center Overlay -->
    <div 
      v-if="showNotificationCenter"
      @click="closeNotificationCenter"
      class="notification-center-overlay"
    ></div>
  </Teleport>
</template>

<script>
import { ref, computed, onMounted, onUnmounted, TransitionGroup } from 'vue'

export default {
  name: 'NotificationSystem',
  components: {
    TransitionGroup
  },
  emits: ['notification-action'],
  setup(props, { emit }) {
    // Reactive state
    const notifications = ref([])
    const allNotifications = ref([])
    const showNotificationCenter = ref(false)
    const isMobile = ref(false)
    const notificationTimers = ref(new Map())
    
    let notificationId = 0

    // Computed properties
    const hasNotifications = computed(() => notifications.value.length > 0)
    const hasUnreadNotifications = computed(() => 
      allNotifications.value.some(n => !n.read)
    )
    const unreadCount = computed(() => 
      allNotifications.value.filter(n => !n.read).length
    )

    // Default notification types and their icons
    const notificationIcons = {
      success: 'fas fa-check-circle',
      error: 'fas fa-exclamation-circle',
      warning: 'fas fa-exclamation-triangle',
      info: 'fas fa-info-circle',
      expense: 'fas fa-receipt',
      budget: 'fas fa-bullseye',
      goal: 'fas fa-target',
      reminder: 'fas fa-clock',
      achievement: 'fas fa-trophy',
      sync: 'fas fa-sync-alt'
    }

    // Mobile detection
    const detectMobile = () => {
      isMobile.value = window.innerWidth <= 768
    }

    // Notification management
    const addNotification = (notification) => {
      const id = ++notificationId
      const newNotification = {
        id,
        timestamp: new Date(),
        duration: 5000,
        persistent: false,
        position: 'top-right',
        read: false,
        paused: false,
        ...notification
      }

      notifications.value.push(newNotification)
      allNotifications.value.unshift(newNotification)

      // Auto-dismiss non-persistent notifications
      if (!newNotification.persistent && newNotification.duration > 0) {
        const timer = setTimeout(() => {
          dismissNotification(id)
        }, newNotification.duration)
        
        notificationTimers.value.set(id, timer)
      }

      return id
    }

    const dismissNotification = (id) => {
      const index = notifications.value.findIndex(n => n.id === id)
      if (index > -1) {
        notifications.value.splice(index, 1)
      }
      
      // Clear timer
      const timer = notificationTimers.value.get(id)
      if (timer) {
        clearTimeout(timer)
        notificationTimers.value.delete(id)
      }
    }

    const pauseTimer = (id) => {
      const notification = notifications.value.find(n => n.id === id)
      if (notification) {
        notification.paused = true
      }
    }

    const resumeTimer = (id) => {
      const notification = notifications.value.find(n => n.id === id)
      if (notification) {
        notification.paused = false
      }
    }

    // Notification Center
    const toggleNotificationCenter = () => {
      showNotificationCenter.value = !showNotificationCenter.value
    }

    const closeNotificationCenter = () => {
      showNotificationCenter.value = false
    }

    const markAllAsRead = () => {
      allNotifications.value.forEach(notification => {
        notification.read = true
      })
    }

    const clearAllNotifications = () => {
      allNotifications.value.splice(0)
      notifications.value.splice(0)
      notificationTimers.value.clear()
    }

    const removeFromHistory = (id) => {
      const index = allNotifications.value.findIndex(n => n.id === id)
      if (index > -1) {
        allNotifications.value.splice(index, 1)
      }
    }

    // Event handlers
    const handleNotificationClick = (notification) => {
      if (notification.clickAction) {
        emit('notification-action', notification.clickAction)
      }
      if (!notification.persistent) {
        dismissNotification(notification.id)
      }
    }

    const handleAction = (action, notification) => {
      emit('notification-action', action)
      if (action.dismissOnClick !== false) {
        dismissNotification(notification.id)
      }
    }

    const handleHistoryNotificationClick = (notification) => {
      notification.read = true
      if (notification.clickAction) {
        emit('notification-action', notification.clickAction)
      }
    }

    // Utility functions
    const getNotificationIcon = (type) => {
      return notificationIcons[type] || notificationIcons.info
    }

    const formatTime = (timestamp) => {
      return new Intl.DateTimeFormat('en-US', {
        hour: '2-digit',
        minute: '2-digit'
      }).format(timestamp)
    }

    const formatRelativeTime = (timestamp) => {
      const now = new Date()
      const diff = now - timestamp
      const minutes = Math.floor(diff / 60000)
      const hours = Math.floor(diff / 3600000)
      const days = Math.floor(diff / 86400000)

      if (minutes < 1) return 'Just now'
      if (minutes < 60) return `${minutes}m ago`
      if (hours < 24) return `${hours}h ago`
      return `${days}d ago`
    }

    // Public API for external components
    const showSuccess = (title, message, options = {}) => {
      return addNotification({
        type: 'success',
        title,
        message,
        ...options
      })
    }

    const showError = (title, message, options = {}) => {
      return addNotification({
        type: 'error',
        title,
        message,
        persistent: true,
        ...options
      })
    }

    const showWarning = (title, message, options = {}) => {
      return addNotification({
        type: 'warning',
        title,
        message,
        duration: 7000,
        ...options
      })
    }

    const showInfo = (title, message, options = {}) => {
      return addNotification({
        type: 'info',
        title,
        message,
        ...options
      })
    }

    const showExpenseAdded = (amount, category) => {
      return addNotification({
        type: 'expense',
        title: 'Expense Added',
        message: `$${amount} added to ${category}`,
        actions: [
          {
            id: 'undo',
            label: 'Undo',
            type: 'secondary',
            icon: 'fas fa-undo',
            dismissOnClick: true
          }
        ]
      })
    }

    const showBudgetAlert = (category, percentage) => {
      return addNotification({
        type: 'warning',
        title: 'Budget Alert',
        message: `You've used ${percentage}% of your ${category} budget`,
        persistent: true,
        actions: [
          {
            id: 'view-budget',
            label: 'View Budget',
            type: 'primary',
            icon: 'fas fa-chart-pie'
          }
        ]
      })
    }

    // Lifecycle
    onMounted(() => {
      detectMobile()
      window.addEventListener('resize', detectMobile)
      
      // Expose methods globally for other components
      window.notify = {
        success: showSuccess,
        error: showError,
        warning: showWarning,
        info: showInfo,
        custom: addNotification,
        expenseAdded: showExpenseAdded,
        showExpenseAdded: showExpenseAdded, // Alias for compatibility
        budgetAlert: showBudgetAlert,
        showBudgetAlert: showBudgetAlert // Alias for compatibility
      }
      
      // Debug log to verify methods are exposed
      console.log('NotificationSystem: window.notify methods initialized:', Object.keys(window.notify))
    })

    onUnmounted(() => {
      window.removeEventListener('resize', detectMobile)
      
      // Clear all timers
      notificationTimers.value.forEach(timer => clearTimeout(timer))
      notificationTimers.value.clear()
      
      // Clean up global reference
      if (window.notify) {
        delete window.notify
      }
    })

    return {
      // State
      notifications,
      allNotifications,
      showNotificationCenter,
      isMobile,
      
      // Computed
      hasNotifications,
      hasUnreadNotifications,
      unreadCount,
      
      // Methods
      dismissNotification,
      pauseTimer,
      resumeTimer,
      toggleNotificationCenter,
      closeNotificationCenter,
      markAllAsRead,
      clearAllNotifications,
      removeFromHistory,
      handleNotificationClick,
      handleAction,
      handleHistoryNotificationClick,
      getNotificationIcon,
      formatTime,
      formatRelativeTime,
      
      // Public API
      showSuccess,
      showError,
      showWarning,
      showInfo,
      showExpenseAdded,
      showBudgetAlert
    }
  }
}
</script>

<style scoped>
/* Notification Container */
.notification-container {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  pointer-events: none;
  z-index: 9999;
}

.notifications-list {
  position: absolute;
  top: 20px;
  right: 20px;
  display: flex;
  flex-direction: column;
  gap: 12px;
  max-width: 400px;
  width: 100%;
}

/* Individual Notification */
.notification {
  position: relative;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 16px;
  padding: 1rem;
  box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
  pointer-events: auto;
  cursor: pointer;
  overflow: hidden;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  transform: translateX(100%);
  opacity: 0;
}

.notification:hover {
  transform: translateY(-2px) scale(1.02);
  box-shadow: 0 16px 48px rgba(0, 0, 0, 0.2);
}

/* Notification Types */
.notification.success {
  border-left: 4px solid #10b981;
}

.notification.error {
  border-left: 4px solid #ef4444;
}

.notification.warning {
  border-left: 4px solid #f59e0b;
}

.notification.info {
  border-left: 4px solid #3b82f6;
}

.notification.expense {
  border-left: 4px solid #8b5cf6;
}

.notification.budget {
  border-left: 4px solid #f97316;
}

/* Notification Layout */
.notification {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
}

.notification-icon {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.125rem;
  color: white;
  flex-shrink: 0;
}

.notification.success .notification-icon {
  background: linear-gradient(135deg, #10b981, #059669);
}

.notification.error .notification-icon {
  background: linear-gradient(135deg, #ef4444, #dc2626);
}

.notification.warning .notification-icon {
  background: linear-gradient(135deg, #f59e0b, #d97706);
}

.notification.info .notification-icon {
  background: linear-gradient(135deg, #3b82f6, #2563eb);
}

.notification.expense .notification-icon {
  background: linear-gradient(135deg, #8b5cf6, #7c3aed);
}

.notification.budget .notification-icon {
  background: linear-gradient(135deg, #f97316, #ea580c);
}

.notification-content {
  flex: 1;
  min-width: 0;
}

.notification-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 0.5rem;
}

.notification-title {
  font-weight: 600;
  color: #1f2937;
  margin: 0;
  font-size: 0.95rem;
}

.notification-meta {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.notification-time {
  font-size: 0.75rem;
  color: #6b7280;
  font-weight: 500;
}

.notification-close {
  width: 20px;
  height: 20px;
  border: none;
  background: transparent;
  color: #9ca3af;
  cursor: pointer;
  border-radius: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
  font-size: 0.75rem;
}

.notification-close:hover {
  background: rgba(0, 0, 0, 0.1);
  color: #6b7280;
}

.notification-message {
  color: #4b5563;
  font-size: 0.875rem;
  line-height: 1.4;
  margin: 0 0 0.75rem 0;
}

/* Notification Actions */
.notification-actions {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.action-btn {
  display: flex;
  align-items: center;
  gap: 0.375rem;
  padding: 0.5rem 0.875rem;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  background: white;
  color: #374151;
  font-size: 0.8rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.action-btn.primary {
  background: #667eea;
  border-color: #667eea;
  color: white;
}

.action-btn.danger {
  background: #ef4444;
  border-color: #ef4444;
  color: white;
}

.action-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.action-btn.small {
  padding: 0.375rem 0.75rem;
  font-size: 0.75rem;
}

/* Progress Bar */
.notification-progress {
  position: absolute;
  bottom: 0;
  left: 0;
  height: 3px;
  background: linear-gradient(90deg, #667eea, #764ba2);
  border-radius: 0 0 16px 16px;
  animation: progressShrink linear;
  transform-origin: left;
}

@keyframes progressShrink {
  from {
    width: 100%;
  }
  to {
    width: 0%;
  }
}

/* Notification Center Toggle */
.notification-center-toggle {
  position: fixed;
  top: 20px;
  right: 80px;
  width: 48px;
  height: 48px;
  background: rgba(255, 255, 255, 0.9);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.3);
  border-radius: 12px;
  color: #6b7280;
  cursor: pointer;
  transition: all 0.3s ease;
  z-index: 9998;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.125rem;
}

.notification-center-toggle:hover {
  background: rgba(255, 255, 255, 1);
  color: #374151;
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
}

.notification-center-toggle.has-unread {
  animation: bellShake 0.5s ease-in-out;
}

@keyframes bellShake {
  0%, 100% { transform: rotate(0deg); }
  10%, 30%, 50%, 70%, 90% { transform: rotate(-10deg); }
  20%, 40%, 60%, 80% { transform: rotate(10deg); }
}

.notification-badge {
  position: absolute;
  top: -4px;
  right: -4px;
  background: #ef4444;
  color: white;
  font-size: 0.7rem;
  font-weight: 600;
  padding: 0.125rem 0.375rem;
  border-radius: 10px;
  min-width: 18px;
  text-align: center;
  box-shadow: 0 2px 8px rgba(239, 68, 68, 0.3);
}

/* Notification Center */
.notification-center {
  position: fixed;
  top: 80px;
  right: 20px;
  width: 400px;
  max-height: 600px;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 20px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
  z-index: 9997;
  overflow: hidden;
  animation: slideInFromRight 0.3s ease-out;
}

@keyframes slideInFromRight {
  from {
    opacity: 0;
    transform: translateX(100%);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

.notification-center-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  border-bottom: 1px solid rgba(0, 0, 0, 0.1);
}

.notification-center-header h3 {
  margin: 0;
  font-size: 1.25rem;
  font-weight: 700;
  color: #1f2937;
}

.header-actions {
  display: flex;
  gap: 0.5rem;
  align-items: center;
}

.close-btn {
  width: 32px;
  height: 32px;
  border: none;
  background: transparent;
  color: #6b7280;
  cursor: pointer;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
}

.close-btn:hover {
  background: rgba(0, 0, 0, 0.1);
  color: #374151;
}

.notification-center-content {
  max-height: 500px;
  overflow-y: auto;
  padding: 1rem;
}

/* Empty State */
.empty-notifications {
  text-align: center;
  padding: 3rem 1rem;
}

.empty-icon {
  width: 64px;
  height: 64px;
  margin: 0 auto 1rem;
  background: linear-gradient(135deg, #f3f4f6, #e5e7eb);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #9ca3af;
  font-size: 1.5rem;
}

.empty-notifications h4 {
  margin: 0 0 0.5rem 0;
  color: #1f2937;
  font-weight: 600;
}

.empty-notifications p {
  margin: 0;
  color: #6b7280;
  font-size: 0.875rem;
}

/* History Notifications */
.notifications-history {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.history-notification {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  padding: 0.875rem;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.2s ease;
  border: 1px solid transparent;
}

.history-notification:hover {
  background: rgba(102, 126, 234, 0.05);
  border-color: rgba(102, 126, 234, 0.2);
}

.history-notification.unread {
  background: rgba(59, 130, 246, 0.05);
  border-color: rgba(59, 130, 246, 0.2);
}

.history-icon {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.875rem;
  color: white;
  flex-shrink: 0;
  background: linear-gradient(135deg, #6b7280, #4b5563);
}

.history-content {
  flex: 1;
  min-width: 0;
}

.history-content h5 {
  margin: 0 0 0.25rem 0;
  font-size: 0.875rem;
  font-weight: 600;
  color: #1f2937;
}

.history-content p {
  margin: 0 0 0.25rem 0;
  font-size: 0.8rem;
  color: #6b7280;
  line-height: 1.3;
}

.history-time {
  font-size: 0.7rem;
  color: #9ca3af;
}

.history-remove {
  width: 24px;
  height: 24px;
  border: none;
  background: transparent;
  color: #9ca3af;
  cursor: pointer;
  border-radius: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
  font-size: 0.7rem;
  opacity: 0;
}

.history-notification:hover .history-remove {
  opacity: 1;
}

.history-remove:hover {
  background: rgba(239, 68, 68, 0.1);
  color: #ef4444;
}

/* Notification Center Overlay */
.notification-center-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.2);
  backdrop-filter: blur(4px);
  z-index: 9996;
}

/* Notification Animations */
.notification-enter-active,
.notification-leave-active {
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.notification-enter-from {
  opacity: 0;
  transform: translateX(100%) scale(0.9);
}

.notification-enter-to {
  opacity: 1;
  transform: translateX(0) scale(1);
}

.notification-leave-from {
  opacity: 1;
  transform: translateX(0) scale(1);
}

.notification-leave-to {
  opacity: 0;
  transform: translateX(100%) scale(0.8);
}

.notification-move {
  transition: transform 0.3s ease;
}

/* Mobile Styles */
.notification-container.mobile .notifications-list {
  top: 10px;
  right: 10px;
  left: 10px;
  max-width: none;
}

.notification-container.mobile .notification {
  border-radius: 12px;
  padding: 0.875rem;
}

.notification-center.mobile {
  top: 10px;
  right: 10px;
  left: 10px;
  width: auto;
  max-height: 80vh;
}

.notification-center-toggle {
  top: 15px;
  right: 15px;
  width: 44px;
  height: 44px;
}

/* Dark Mode */
@media (prefers-color-scheme: dark) {
  .notification {
    background: rgba(30, 41, 59, 0.95);
    border-color: rgba(71, 85, 105, 0.3);
  }
  
  .notification-title {
    color: #f1f5f9;
  }
  
  .notification-message {
    color: #cbd5e1;
  }
  
  .notification-time {
    color: #94a3b8;
  }
  
  .notification-center {
    background: rgba(30, 41, 59, 0.95);
    border-color: rgba(71, 85, 105, 0.3);
  }
  
  .notification-center-header h3 {
    color: #f1f5f9;
  }
  
  .empty-notifications h4 {
    color: #f1f5f9;
  }
  
  .empty-notifications p {
    color: #94a3b8;
  }
  
  .history-content h5 {
    color: #f1f5f9;
  }
  
  .history-content p {
    color: #94a3b8;
  }
}

/* Position Variants */
.notification.top-left {
  animation: slideInFromLeft 0.4s ease-out;
}

.notification.top-right {
  animation: slideInFromRight 0.4s ease-out;
}

.notification.bottom-left {
  animation: slideInFromBottomLeft 0.4s ease-out;
}

.notification.bottom-right {
  animation: slideInFromBottomRight 0.4s ease-out;
}

@keyframes slideInFromLeft {
  from {
    opacity: 0;
    transform: translateX(-100%);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

@keyframes slideInFromBottomLeft {
  from {
    opacity: 0;
    transform: translateX(-100%) translateY(100%);
  }
  to {
    opacity: 1;
    transform: translateX(0) translateY(0);
  }
}

@keyframes slideInFromBottomRight {
  from {
    opacity: 0;
    transform: translateX(100%) translateY(100%);
  }
  to {
    opacity: 1;
    transform: translateX(0) translateY(0);
  }
}
</style>