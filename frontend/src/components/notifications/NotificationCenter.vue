<template>
  <div>
    <!-- Notification Bell Button -->
    <button
      @click="toggleNotificationCenter"
      class="notification-bell-btn"
      :class="{ 'has-unread': hasUnread }"
      title="Notifications"
    >
      <svg class="bell-icon" :class="{ ring: hasUnread }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
        />
      </svg>
      <span v-if="unreadCount > 0" class="notification-badge">
        {{ unreadCount > 99 ? '99+' : unreadCount }}
      </span>
    </button>

    <!-- Notification Center Panel -->
    <Teleport to="body">
      <Transition name="slide-fade">
        <div v-if="isOpen" class="notification-center-panel">
          <!-- Header -->
          <div class="notification-header">
            <div class="header-title">
              <h3>Notifications</h3>
              <span v-if="unreadCount > 0" class="unread-badge">{{ unreadCount }} new</span>
            </div>
            <div class="header-actions">
              <button v-if="hasUnread" @click="markAllAsRead" class="action-btn" title="Mark all as read">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
              </button>
              <button @click="openSettings" class="action-btn" title="Settings">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"
                  />
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                  />
                </svg>
              </button>
              <button @click="closeNotificationCenter" class="action-btn close-btn" title="Close">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          </div>

          <!-- Tabs -->
          <div class="notification-tabs">
            <button @click="activeTab = 'all'" :class="['tab-btn', { active: activeTab === 'all' }]">
              All
              <span v-if="allNotifications.length > 0" class="tab-count">{{ allNotifications.length }}</span>
            </button>
            <button @click="activeTab = 'unread'" :class="['tab-btn', { active: activeTab === 'unread' }]">
              Unread
              <span v-if="unreadNotifications.length > 0" class="tab-count">{{ unreadNotifications.length }}</span>
            </button>
            <button @click="activeTab = 'important'" :class="['tab-btn', { active: activeTab === 'important' }]">
              Important
              <span v-if="importantNotifications.length > 0" class="tab-count">
                {{ importantNotifications.length }}
              </span>
            </button>
          </div>

          <!-- Notification List -->
          <div class="notification-list" ref="notificationList">
            <div v-if="filteredNotifications.length === 0" class="empty-state">
              <div class="empty-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"
                  />
                </svg>
              </div>
              <h4>No notifications</h4>
              <p>You're all caught up!</p>
            </div>

            <TransitionGroup v-else name="notification-list" tag="div">
              <div
                v-for="notification in filteredNotifications"
                :key="notification.id"
                :class="['notification-item', { unread: !notification.read, [notification.type]: true }]"
                @click="handleNotificationClick(notification)"
              >
                <!-- Icon -->
                <div class="notification-icon" :style="{ backgroundColor: notification.color }">
                  <span class="emoji">{{ notification.icon }}</span>
                </div>

                <!-- Content -->
                <div class="notification-content">
                  <div class="notification-title-row">
                    <h4 class="notification-title">{{ notification.title }}</h4>
                    <span class="notification-time">{{ formatRelativeTime(notification.timestamp) }}</span>
                  </div>
                  <p class="notification-message">{{ notification.message }}</p>

                  <!-- Actions -->
                  <div v-if="notification.actions && notification.actions.length > 0" class="notification-actions">
                    <button
                      v-for="action in notification.actions"
                      :key="action.id"
                      @click.stop="handleAction(action, notification)"
                      :class="['action-btn-small', action.type || 'secondary']"
                    >
                      {{ action.label }}
                    </button>
                  </div>
                </div>

                <!-- Context Menu -->
                <div class="notification-menu">
                  <button @click.stop="toggleMenu(notification.id)" class="menu-trigger">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"
                      />
                    </svg>
                  </button>
                  <div v-if="openMenuId === notification.id" class="menu-dropdown">
                    <button
                      @click.stop="
                        markAsRead(notification.id)
                        openMenuId = null
                      "
                    >
                      {{ notification.read ? 'Mark as unread' : 'Mark as read' }}
                    </button>
                    <button
                      @click.stop="
                        removeFromHistory(notification.id)
                        openMenuId = null
                      "
                    >
                      Delete
                    </button>
                  </div>
                </div>
              </div>
            </TransitionGroup>
          </div>

          <!-- Footer -->
          <div class="notification-footer">
            <button @click="clearAll" class="footer-btn danger" v-if="filteredNotifications.length > 0">
              <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                />
              </svg>
              Clear all
            </button>
          </div>
        </div>
      </Transition>

      <!-- Overlay -->
      <Transition name="fade">
        <div v-if="isOpen" @click="closeNotificationCenter" class="notification-overlay"></div>
      </Transition>

      <!-- Settings Modal -->
      <Transition name="modal">
        <div v-if="showSettings" class="settings-modal-overlay" @click="closeSettings">
          <div class="settings-modal" @click.stop>
            <div class="settings-header">
              <h3>Notification Settings</h3>
              <button @click="closeSettings" class="close-btn">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
            <div class="settings-content">
              <div class="setting-item">
                <div class="setting-label">
                  <h4>Enable Notifications</h4>
                  <p>Receive notifications about your expenses and budgets</p>
                </div>
                <label class="toggle-switch">
                  <input type="checkbox" v-model="localPreferences.enabled" @change="savePreferences" />
                  <span class="toggle-slider"></span>
                </label>
              </div>

              <div class="setting-item">
                <div class="setting-label">
                  <h4>Sound Effects</h4>
                  <p>Play sounds for important notifications</p>
                </div>
                <label class="toggle-switch">
                  <input type="checkbox" v-model="localPreferences.sound" @change="savePreferences" />
                  <span class="toggle-slider"></span>
                </label>
              </div>

              <div class="setting-item">
                <div class="setting-label">
                  <h4>Desktop Notifications</h4>
                  <p>Show notifications even when browser is minimized</p>
                </div>
                <label class="toggle-switch">
                  <input type="checkbox" v-model="localPreferences.desktop" @change="handleDesktopToggle" />
                  <span class="toggle-slider"></span>
                </label>
              </div>

              <div class="setting-divider"></div>

              <div class="setting-item">
                <div class="setting-label">
                  <h4>Notification Position</h4>
                  <p>Where notifications appear on screen</p>
                </div>
                <select v-model="localPreferences.positions.default" @change="savePreferences" class="setting-select">
                  <option value="top-right">Top Right</option>
                  <option value="top-left">Top Left</option>
                  <option value="bottom-right">Bottom Right</option>
                  <option value="bottom-left">Bottom Left</option>
                </select>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { useNotificationsStore } from '@/stores/notifications'

const notificationsStore = useNotificationsStore()

// State
const isOpen = ref(false)
const activeTab = ref('all')
const openMenuId = ref(null)
const showSettings = ref(false)
const notificationList = ref(null)
const localPreferences = ref({ ...notificationsStore.preferences })

// Computed
const unreadCount = computed(() => notificationsStore.unreadCount)
const hasUnread = computed(() => notificationsStore.hasUnread)

const allNotifications = computed(() => notificationsStore.history)

const unreadNotifications = computed(() => allNotifications.value.filter(n => !n.read))

const importantNotifications = computed(() =>
  allNotifications.value.filter(n => n.priority === 'high' || n.priority === 'critical')
)

const filteredNotifications = computed(() => {
  switch (activeTab.value) {
      case 'unread':
        return unreadNotifications.value
      case 'important':
        return importantNotifications.value
      default:
        return allNotifications.value
  }
})

// Methods
const toggleNotificationCenter = () => {
  isOpen.value = !isOpen.value
  if (isOpen.value) {
    document.body.style.overflow = 'hidden'
  } else {
    document.body.style.overflow = ''
    openMenuId.value = null
  }
}

const closeNotificationCenter = () => {
  isOpen.value = false
  document.body.style.overflow = ''
  openMenuId.value = null
}

const markAllAsRead = () => {
  notificationsStore.markAllAsRead()
}

const markAsRead = id => {
  const notification = allNotifications.value.find(n => n.id === id)
  if (notification) {
    notification.read = !notification.read
    notificationsStore.markAsRead(id)
  }
}

const removeFromHistory = id => {
  notificationsStore.removeFromHistory(id)
}

const clearAll = () => {
  if (confirm('Are you sure you want to clear all notifications?')) {
    if (activeTab.value === 'all') {
      notificationsStore.clearHistory()
    } else {
      filteredNotifications.value.forEach(n => {
        notificationsStore.removeFromHistory(n.id)
      })
    }
  }
}

const handleNotificationClick = notification => {
  if (!notification.read) {
    markAsRead(notification.id)
  }

  if (notification.actions && notification.actions.length > 0) {
    const primaryAction = notification.actions.find(a => a.type === 'primary')
    if (primaryAction && primaryAction.callback) {
      primaryAction.callback()
    }
  }
}

const handleAction = (action, notification) => {
  if (action.callback) {
    action.callback()
  }
  markAsRead(notification.id)
}

const toggleMenu = id => {
  openMenuId.value = openMenuId.value === id ? null : id
}

const openSettings = () => {
  showSettings.value = true
  localPreferences.value = { ...notificationsStore.preferences }
}

const closeSettings = () => {
  showSettings.value = false
}

const savePreferences = () => {
  notificationsStore.updatePreferences(localPreferences.value)
}

const handleDesktopToggle = async() => {
  if (localPreferences.value.desktop) {
    const granted = await notificationsStore.requestDesktopPermission()
    if (!granted) {
      localPreferences.value.desktop = false
    }
  }
  savePreferences()
}

const formatRelativeTime = timestamp => {
  const now = new Date()
  const date = new Date(timestamp)
  const diffInSeconds = Math.floor((now - date) / 1000)

  if (diffInSeconds < 60) return 'Just now'
  if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)}m ago`
  if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)}h ago`
  if (diffInSeconds < 604800) return `${Math.floor(diffInSeconds / 86400)}d ago`
  return date.toLocaleDateString()
}

// Click outside to close menu
const handleClickOutside = event => {
  if (openMenuId.value && !event.target.closest('.notification-menu')) {
    openMenuId.value = null
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
  document.body.style.overflow = ''
})

// Watch for changes
watch(
  () => notificationsStore.preferences,
  newPrefs => {
    localPreferences.value = { ...newPrefs }
  },
  { deep: true }
)
</script>

<style scoped>
  /* Bell Button */
  .notification-bell-btn {
    position: relative;
    padding: 0.5rem;
    background: transparent;
    border: none;
    border-radius: 0.5rem;
    cursor: pointer;
    transition: all 0.3s ease;
  }

  .notification-bell-btn:hover {
    background-color: rgba(0, 0, 0, 0.05);
  }

  .bell-icon {
    width: 1.5rem;
    height: 1.5rem;
    color: #6b7280;
    transition: all 0.3s ease;
  }

  .notification-bell-btn:hover .bell-icon {
    color: #3b82f6;
  }

  .bell-icon.ring {
    animation: ring 2s ease-in-out infinite;
  }

  @keyframes ring {
    0%,
    100% {
      transform: rotate(0deg);
    }
    10%,
    30% {
      transform: rotate(-10deg);
    }
    20%,
    40% {
      transform: rotate(10deg);
    }
  }

  .notification-badge {
    position: absolute;
    top: 0.25rem;
    right: 0.25rem;
    min-width: 1.25rem;
    height: 1.25rem;
    padding: 0 0.25rem;
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: white;
    font-size: 0.625rem;
    font-weight: 700;
    border-radius: 0.625rem;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    animation: pulse-badge 2s ease-in-out infinite;
  }

  @keyframes pulse-badge {
    0%,
    100% {
      transform: scale(1);
    }
    50% {
      transform: scale(1.1);
    }
  }

  /* Notification Panel */
  .notification-center-panel {
    position: fixed;
    top: 0;
    right: 0;
    width: 100%;
    max-width: 420px;
    height: 100vh;
    background: white;
    box-shadow: -4px 0 24px rgba(0, 0, 0, 0.15);
    z-index: 99999;
    display: flex;
    flex-direction: column;
    overflow: hidden;
  }

  /* Header */
  .notification-header {
    padding: 1.5rem;
    border-bottom: 1px solid #e5e7eb;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .header-title {
    display: flex;
    align-items: center;
    gap: 0.75rem;
  }

  .header-title h3 {
    font-size: 1.25rem;
    font-weight: 700;
    margin: 0;
  }

  .unread-badge {
    padding: 0.25rem 0.5rem;
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    border-radius: 0.5rem;
    font-size: 0.75rem;
    font-weight: 600;
  }

  .header-actions {
    display: flex;
    gap: 0.5rem;
  }

  .action-btn {
    padding: 0.5rem;
    background: rgba(255, 255, 255, 0.1);
    border: none;
    border-radius: 0.5rem;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .action-btn svg {
    width: 1.25rem;
    height: 1.25rem;
    color: white;
  }

  .action-btn:hover {
    background: rgba(255, 255, 255, 0.2);
  }

  /* Tabs */
  .notification-tabs {
    display: flex;
    padding: 0.5rem 1rem;
    gap: 0.5rem;
    border-bottom: 1px solid #e5e7eb;
    background: #f9fafb;
  }

  .tab-btn {
    flex: 1;
    padding: 0.5rem 1rem;
    background: transparent;
    border: none;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    font-weight: 600;
    color: #6b7280;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
  }

  .tab-btn:hover {
    background: rgba(59, 130, 246, 0.1);
    color: #3b82f6;
  }

  .tab-btn.active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
  }

  .tab-count {
    padding: 0.125rem 0.375rem;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 0.375rem;
    font-size: 0.75rem;
    font-weight: 700;
  }

  .tab-btn.active .tab-count {
    background: rgba(255, 255, 255, 0.3);
  }

  /* Notification List */
  .notification-list {
    flex: 1;
    overflow-y: auto;
    padding: 0.5rem;
  }

  .notification-item {
    display: flex;
    gap: 1rem;
    padding: 1rem;
    margin-bottom: 0.5rem;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 0.75rem;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
  }

  .notification-item:hover {
    border-color: #3b82f6;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.1);
    transform: translateX(-2px);
  }

  .notification-item.unread {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.05) 0%, rgba(147, 51, 234, 0.05) 100%);
    border-color: #3b82f6;
  }

  .notification-item.unread::before {
    content: '';
    position: absolute;
    left: 0;
    top: 50%;
    transform: translateY(-50%);
    width: 4px;
    height: 70%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 0 4px 4px 0;
  }

  .notification-icon {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 0.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  }

  .emoji {
    font-size: 1.25rem;
  }

  .notification-content {
    flex: 1;
    min-width: 0;
  }

  .notification-title-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 0.5rem;
    margin-bottom: 0.25rem;
  }

  .notification-title {
    font-size: 0.875rem;
    font-weight: 600;
    color: #1f2937;
    margin: 0;
    flex: 1;
  }

  .notification-time {
    font-size: 0.75rem;
    color: #9ca3af;
    white-space: nowrap;
  }

  .notification-message {
    font-size: 0.875rem;
    color: #6b7280;
    margin: 0;
    line-height: 1.5;
  }

  .notification-actions {
    display: flex;
    gap: 0.5rem;
    margin-top: 0.75rem;
  }

  .action-btn-small {
    padding: 0.375rem 0.75rem;
    border: none;
    border-radius: 0.375rem;
    font-size: 0.75rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
  }

  .action-btn-small.primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
  }

  .action-btn-small.primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
  }

  .action-btn-small.secondary {
    background: #f3f4f6;
    color: #4b5563;
  }

  .action-btn-small.secondary:hover {
    background: #e5e7eb;
  }

  /* Context Menu */
  .notification-menu {
    position: relative;
  }

  .menu-trigger {
    padding: 0.25rem;
    background: transparent;
    border: none;
    border-radius: 0.25rem;
    cursor: pointer;
    opacity: 0;
    transition: all 0.3s ease;
  }

  .notification-item:hover .menu-trigger {
    opacity: 1;
  }

  .menu-trigger svg {
    width: 1rem;
    height: 1rem;
    color: #6b7280;
  }

  .menu-trigger:hover {
    background: #f3f4f6;
  }

  .menu-dropdown {
    position: absolute;
    top: 100%;
    right: 0;
    min-width: 150px;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    z-index: 10;
    overflow: hidden;
  }

  .menu-dropdown button {
    width: 100%;
    padding: 0.75rem 1rem;
    background: transparent;
    border: none;
    text-align: left;
    font-size: 0.875rem;
    color: #374151;
    cursor: pointer;
    transition: all 0.2s ease;
  }

  .menu-dropdown button:hover {
    background: #f3f4f6;
  }

  /* Empty State */
  .empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 3rem 2rem;
    text-align: center;
  }

  .empty-icon {
    width: 4rem;
    height: 4rem;
    margin-bottom: 1rem;
    padding: 1rem;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .empty-icon svg {
    width: 2rem;
    height: 2rem;
    color: #667eea;
  }

  .empty-state h4 {
    font-size: 1.125rem;
    font-weight: 600;
    color: #1f2937;
    margin: 0 0 0.5rem 0;
  }

  .empty-state p {
    font-size: 0.875rem;
    color: #6b7280;
    margin: 0;
  }

  /* Footer */
  .notification-footer {
    padding: 1rem;
    border-top: 1px solid #e5e7eb;
    background: #f9fafb;
  }

  .footer-btn {
    width: 100%;
    padding: 0.75rem;
    border: none;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
  }

  .footer-btn svg {
    width: 1.125rem;
    height: 1.125rem;
  }

  .footer-btn.danger {
    background: #fee2e2;
    color: #dc2626;
  }

  .footer-btn.danger:hover {
    background: #fef2f2;
    box-shadow: 0 2px 8px rgba(220, 38, 38, 0.2);
  }

  /* Overlay */
  .notification-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.4);
    backdrop-filter: blur(2px);
    z-index: 99998;
  }

  /* Settings Modal */
  .settings-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
    z-index: 100000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
  }

  .settings-modal {
    width: 100%;
    max-width: 500px;
    max-height: 90vh;
    background: white;
    border-radius: 1rem;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    overflow: hidden;
    display: flex;
    flex-direction: column;
  }

  .settings-header {
    padding: 1.5rem;
    border-bottom: 1px solid #e5e7eb;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .settings-header h3 {
    font-size: 1.25rem;
    font-weight: 700;
    margin: 0;
  }

  .settings-header .close-btn {
    padding: 0.5rem;
    background: rgba(255, 255, 255, 0.1);
    border: none;
    border-radius: 0.5rem;
    cursor: pointer;
  }

  .settings-header .close-btn svg {
    width: 1.25rem;
    height: 1.25rem;
    color: white;
  }

  .settings-content {
    flex: 1;
    overflow-y: auto;
    padding: 1.5rem;
  }

  .setting-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    margin-bottom: 1rem;
    background: #f9fafb;
    border-radius: 0.75rem;
  }

  .setting-label h4 {
    font-size: 0.875rem;
    font-weight: 600;
    color: #1f2937;
    margin: 0 0 0.25rem 0;
  }

  .setting-label p {
    font-size: 0.75rem;
    color: #6b7280;
    margin: 0;
  }

  .toggle-switch {
    position: relative;
    width: 48px;
    height: 28px;
    flex-shrink: 0;
  }

  .toggle-switch input {
    opacity: 0;
    width: 0;
    height: 0;
  }

  .toggle-slider {
    position: absolute;
    inset: 0;
    background-color: #cbd5e1;
    border-radius: 34px;
    transition: 0.4s;
    cursor: pointer;
  }

  .toggle-slider:before {
    content: '';
    position: absolute;
    height: 20px;
    width: 20px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    border-radius: 50%;
    transition: 0.4s;
  }

  input:checked + .toggle-slider {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  }

  input:checked + .toggle-slider:before {
    transform: translateX(20px);
  }

  .setting-select {
    padding: 0.5rem 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    color: #374151;
    background: white;
    cursor: pointer;
  }

  .setting-divider {
    height: 1px;
    background: #e5e7eb;
    margin: 1rem 0;
  }

  /* Transitions */
  .slide-fade-enter-active {
    transition: all 0.3s ease-out;
  }

  .slide-fade-leave-active {
    transition: all 0.3s ease-in;
  }

  .slide-fade-enter-from {
    transform: translateX(100%);
    opacity: 0;
  }

  .slide-fade-leave-to {
    transform: translateX(100%);
    opacity: 0;
  }

  .fade-enter-active,
  .fade-leave-active {
    transition: opacity 0.3s ease;
  }

  .fade-enter-from,
  .fade-leave-to {
    opacity: 0;
  }

  .modal-enter-active,
  .modal-leave-active {
    transition: all 0.3s ease;
  }

  .modal-enter-from,
  .modal-leave-to {
    opacity: 0;
    transform: scale(0.95);
  }

  .notification-list-enter-active {
    transition: all 0.3s ease-out;
  }

  .notification-list-leave-active {
    transition: all 0.3s ease-in;
    position: absolute;
    width: 100%;
  }

  .notification-list-enter-from {
    opacity: 0;
    transform: translateY(-20px);
  }

  .notification-list-leave-to {
    opacity: 0;
    transform: translateX(100%);
  }

  .notification-list-move {
    transition: transform 0.3s ease;
  }

  /* Responsive */
  @media (max-width: 768px) {
    .notification-center-panel {
      max-width: 100%;
    }

    .notification-tabs {
      overflow-x: auto;
      scrollbar-width: none;
    }

    .notification-tabs::-webkit-scrollbar {
      display: none;
    }

    .tab-btn {
      flex: 0 0 auto;
      min-width: fit-content;
    }
  }
</style>
