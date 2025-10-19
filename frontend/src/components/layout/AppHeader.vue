<template>
  <header class="modern-header">
    <div class="header-container">
      <div class="header-content">
        <!-- Brand Section -->
        <div class="brand-section" @click="scrollToTop" role="button" tabindex="0" @keydown.enter="scrollToTop">
          <div class="brand-icon">
            <i class="fas fa-wallet"></i>
          </div>
          <div class="brand-text">
            <h1 class="brand-title">ExpenseTracker</h1>
            <span class="brand-subtitle">Financial Management</span>
          </div>
        </div>
        
        <!-- Header Actions -->
        <div class="header-actions">
          <!-- Enhanced Search Bar -->
          <div class="modern-search-container">
            <div class="search-wrapper">
              <i class="fas fa-search search-icon"></i>
              <input 
                type="text" 
                v-model="searchQuery"
                @focus="onSearchFocus"
                @blur="onSearchBlur"
                @input="onSearchInput"
                placeholder="Search expenses, categories..." 
                class="modern-search-input"
                :class="{ 'focused': isSearchFocused }"
              />
              <div v-if="searchQuery" @click="clearSearch" class="search-clear">
                <i class="fas fa-times"></i>
              </div>
            </div>
            
            <!-- Search Suggestions Dropdown -->
            <div v-if="showSearchSuggestions" class="search-suggestions">
              <div class="suggestion-group">
                <h6 class="suggestion-title">Recent Searches</h6>
                <div class="suggestion-item" v-for="item in recentSearches" :key="item">
                  <i class="fas fa-history"></i>
                  <span>{{ item }}</span>
                </div>
              </div>
              <div class="suggestion-group">
                <h6 class="suggestion-title">Quick Actions</h6>
                <div class="suggestion-item action-item">
                  <i class="fas fa-plus-circle"></i>
                  <span>Add Expense</span>
                </div>
                <div class="suggestion-item action-item">
                  <i class="fas fa-chart-bar"></i>
                  <span>View Analytics</span>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Enhanced Notifications -->
          <div class="notifications-container">
            <button 
              @click="toggleNotifications" 
              class="modern-notification-btn" 
              :class="{ 'active': showNotifications }"
              :aria-expanded="showNotifications"
              aria-label="Toggle notifications"
              :aria-describedby="notificationCount > 0 ? 'notification-count' : null"
            >
              <i class="fas fa-bell"></i>
              <span 
                v-if="notificationCount > 0" 
                class="notification-badge" 
                id="notification-count"
                :aria-label="`${notificationCount} unread notifications`"
              >
                {{ notificationCount }}
              </span>
            </button>
            
            <!-- Notifications Dropdown -->
            <div 
              v-if="showNotifications" 
              class="notifications-dropdown" 
              @click.stop
              role="menu"
              aria-label="Notifications menu"
            >
              <div class="notifications-header">
                <h4 class="notifications-title">Notifications</h4>
                <button @click="markAllAsRead" class="mark-all-read">Mark all as read</button>
              </div>
              <div class="notifications-content">
                <div 
                  class="notification-item" 
                  v-for="notification in notifications" 
                  :key="notification.id" 
                  :class="{ 'unread': !notification.read }"
                  :data-notification-id="notification.id"
                  role="menuitem"
                >
                  <div class="notification-icon" :class="notification.type">
                    <i :class="getNotificationIcon(notification.type)"></i>
                  </div>
                  <div class="notification-content">
                    <p class="notification-title">{{ notification.title }}</p>
                    <p class="notification-message">{{ notification.message }}</p>
                    <span class="notification-time">{{ formatTime(notification.time) }}</span>
                  </div>
                  <button 
                    @click="dismissNotification(notification.id)" 
                    class="notification-dismiss"
                    :aria-label="`Dismiss ${notification.title}`"
                  >
                    <i class="fas fa-times"></i>
                  </button>
                </div>
              </div>
              <div v-if="notifications.length === 0" class="empty-notifications">
                <i class="fas fa-bell-slash"></i>
                <p>No notifications</p>
              </div>
            </div>
          </div>
          
          <!-- Enhanced User Profile -->
          <div 
            class="user-profile" 
            @click="toggleDropdown"
            :aria-expanded="showDropdown"
            role="button"
            tabindex="0"
            @keydown.enter="toggleDropdown"
            @keydown.space.prevent="toggleDropdown"
            aria-label="User profile menu"
          >
            <div class="user-avatar">
              <img v-if="user?.avatar" :src="user.avatar" :alt="user.name" class="avatar-image" />
              <span v-else class="avatar-initials">{{ getInitials(user?.name || 'User') }}</span>
            </div>
            <div class="user-info">
              <span class="user-name">{{ user?.name || 'User' }}</span>
              <span class="user-role">{{ user?.role || 'Administrator' }}</span>
            </div>
            <div class="dropdown-trigger">
              <i class="fas fa-chevron-down" :class="{ 'rotated': showDropdown }"></i>
            </div>
            
            <!-- Enhanced Dropdown Menu -->
            <div 
              v-if="showDropdown" 
              class="dropdown-menu" 
              @click.stop
              role="menu"
              aria-label="User profile options"
            >
              <div class="dropdown-item">
                <i class="fas fa-user-circle"></i>
                <span>Profile</span>
              </div>
              <div class="dropdown-item">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
              </div>
              <div class="dropdown-item">
                <i class="fas fa-question-circle"></i>
                <span>Help & Support</span>
              </div>
              <div class="dropdown-divider"></div>
              <button @click="handleLogout" class="dropdown-item logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                <span>Sign Out</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
import { computed, ref, reactive, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()

// Reactive state
const showDropdown = ref(false)
const showNotifications = ref(false)
const showSearchSuggestions = ref(false)
const isSearchFocused = ref(false)
const searchQuery = ref('')

// User data
const user = computed(() => authStore.user)

// Computed notification count
const notificationCount = computed(() => {
  return notifications.value.filter(n => !n.read).length
})
const notifications = ref([
  {
    id: 1,
    type: 'warning',
    title: 'Budget Alert',
    message: 'You\'ve spent 85% of your monthly budget',
    time: new Date(Date.now() - 1000 * 60 * 30), // 30 mins ago
    read: false
  },
  {
    id: 2,
    type: 'success',
    title: 'Goal Achieved',
    message: 'Congratulations! You\'ve reached your savings goal',
    time: new Date(Date.now() - 1000 * 60 * 60 * 2), // 2 hours ago
    read: false
  },
  {
    id: 3,
    type: 'info',
    title: 'Weekly Report',
    message: 'Your weekly expense report is ready',
    time: new Date(Date.now() - 1000 * 60 * 60 * 24), // 1 day ago
    read: true
  }
])

// Search suggestions
const recentSearches = ref(['Coffee', 'Groceries', 'Transportation'])

// User profile methods
const toggleDropdown = (event) => {
  event.stopPropagation()
  showDropdown.value = !showDropdown.value
  showNotifications.value = false
  showSearchSuggestions.value = false
}

const getInitials = (name) => {
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
}

// Utility methods
const scrollToTop = () => {
  window.scrollTo({
    top: 0,
    behavior: 'smooth'
  })
}

// Search methods
const onSearchFocus = () => {
  isSearchFocused.value = true
  showSearchSuggestions.value = true
  showDropdown.value = false
  showNotifications.value = false
}

const onSearchBlur = () => {
  isSearchFocused.value = false
  // Delay hiding suggestions to allow clicking
  setTimeout(() => {
    showSearchSuggestions.value = false
  }, 200)
}

const onSearchInput = () => {
  // Implement search logic here
  console.log('Searching for:', searchQuery.value)
}

const clearSearch = () => {
  searchQuery.value = ''
  showSearchSuggestions.value = false
}

// Notification methods
const toggleNotifications = (event) => {
  event.stopPropagation()
  showNotifications.value = !showNotifications.value
  showDropdown.value = false
  showSearchSuggestions.value = false
}

const markAllAsRead = () => {
  notifications.value.forEach(n => n.read = true)
  // Add a small animation feedback
  const button = document.querySelector('.mark-all-read')
  if (button) {
    button.style.transform = 'scale(0.95)'
    setTimeout(() => {
      button.style.transform = 'scale(1)'
    }, 150)
  }
}

const dismissNotification = (id) => {
  const index = notifications.value.findIndex(n => n.id === id)
  if (index > -1) {
    // Add slide-out animation before removing
    const notificationElement = document.querySelector(`[data-notification-id="${id}"]`)
    if (notificationElement) {
      notificationElement.style.transform = 'translateX(100%)'
      notificationElement.style.opacity = '0'
      setTimeout(() => {
        notifications.value.splice(index, 1)
      }, 300)
    } else {
      notifications.value.splice(index, 1)
    }
  }
}

const getNotificationIcon = (type) => {
  const icons = {
    warning: 'fas fa-exclamation-triangle',
    success: 'fas fa-check-circle',
    info: 'fas fa-info-circle',
    error: 'fas fa-times-circle'
  }
  return icons[type] || 'fas fa-bell'
}

const formatTime = (time) => {
  const now = new Date()
  const diff = now - time
  const minutes = Math.floor(diff / (1000 * 60))
  const hours = Math.floor(diff / (1000 * 60 * 60))
  const days = Math.floor(diff / (1000 * 60 * 60 * 24))
  
  if (minutes < 60) return `${minutes}m ago`
  if (hours < 24) return `${hours}h ago`
  return `${days}d ago`
}

const handleLogout = async (event) => {
  event.stopPropagation()
  try {
    await authStore.logout()
    router.push('/login')
  } catch (error) {
    console.error('Logout failed:', error)
  }
  showDropdown.value = false
}

// Close dropdowns when clicking outside
const closeDropdown = (event) => {
  // Don't close if clicking inside any dropdown
  if (!event.target.closest('.user-profile') && 
      !event.target.closest('.notifications-container') &&
      !event.target.closest('.modern-search-container')) {
    showDropdown.value = false
    showNotifications.value = false
    showSearchSuggestions.value = false
  }
}

// Keyboard support
const handleKeyDown = (event) => {
  if (event.key === 'Escape') {
    showDropdown.value = false
    showNotifications.value = false
    showSearchSuggestions.value = false
  }
}

// Add event listeners
onMounted(() => {
  document.addEventListener('click', closeDropdown)
  document.addEventListener('keydown', handleKeyDown)
})

onUnmounted(() => {
  document.removeEventListener('click', closeDropdown)
  document.removeEventListener('keydown', handleKeyDown)
})
</script>

<style scoped>
.modern-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
  position: sticky;
  top: 0;
  z-index: 1000;
  backdrop-filter: blur(10px);
}

.header-container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 2rem;
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  height: 70px;
}

/* Brand Section */
.brand-section {
  display: flex;
  align-items: center;
  gap: 1rem;
  cursor: pointer;
  transition: all 0.3s ease;
  padding: 0.5rem;
  border-radius: 12px;
}

.brand-section:hover {
  background: rgba(255, 255, 255, 0.1);
  transform: translateY(-1px);
}

.brand-section:focus {
  outline: none;
  box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.3);
}

.brand-icon {
  width: 50px;
  height: 50px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1.5rem;
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.3);
}

.brand-text {
  display: flex;
  flex-direction: column;
}

.brand-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: white;
  margin: 0;
  letter-spacing: -0.5px;
}

.brand-subtitle {
  font-size: 0.75rem;
  color: rgba(255, 255, 255, 0.8);
  font-weight: 400;
  text-transform: uppercase;
  letter-spacing: 1px;
}

/* Header Actions */
.header-actions {
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

/* Modern Search Bar */
.modern-search-container {
  position: relative;
  display: flex;
  align-items: center;
}

.search-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.modern-search-input {
  background: rgba(255, 255, 255, 0.15);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 24px;
  padding: 0.875rem 3rem 0.875rem 3rem;
  color: white;
  font-size: 0.9rem;
  font-weight: 500;
  width: 320px;
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.modern-search-input::placeholder {
  color: rgba(255, 255, 255, 0.7);
  font-weight: 400;
}

.modern-search-input:focus,
.modern-search-input.focused {
  outline: none;
  background: rgba(255, 255, 255, 0.25);
  border-color: rgba(255, 255, 255, 0.4);
  transform: scale(1.03);
  box-shadow: 0 8px 40px rgba(0, 0, 0, 0.15);
  width: 400px;
}

.search-icon {
  position: absolute;
  left: 1.25rem;
  color: rgba(255, 255, 255, 0.8);
  z-index: 2;
  font-size: 0.9rem;
}

.search-clear {
  position: absolute;
  right: 1.25rem;
  color: rgba(255, 255, 255, 0.6);
  cursor: pointer;
  z-index: 2;
  padding: 0.25rem;
  border-radius: 50%;
  transition: all 0.2s ease;
}

.search-clear:hover {
  color: rgba(255, 255, 255, 0.9);
  background: rgba(255, 255, 255, 0.1);
}

/* Search Suggestions */
.search-suggestions {
  position: absolute;
  top: calc(100% + 0.5rem);
  left: 0;
  right: 0;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 20px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
  z-index: 1000;
  overflow: hidden;
  animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-10px) scale(0.95);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

@keyframes slideUp {
  from {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
  to {
    opacity: 0;
    transform: translateY(-10px) scale(0.95);
  }
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.suggestion-group {
  padding: 1rem;
}

.suggestion-group:not(:last-child) {
  border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

.suggestion-title {
  font-size: 0.75rem;
  font-weight: 700;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 0.75rem;
}

.suggestion-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.2s ease;
  color: #334155;
}

.suggestion-item:hover {
  background: rgba(102, 126, 234, 0.1);
  color: #1e293b;
}

.suggestion-item i {
  width: 16px;
  color: #64748b;
}

.action-item {
  font-weight: 500;
}

.action-item:hover {
  background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
}

/* Modern Notifications */
.notifications-container {
  position: relative;
}

.modern-notification-btn {
  background: rgba(255, 255, 255, 0.15);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 50%;
  width: 48px;
  height: 48px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: rgba(255, 255, 255, 0.9);
  cursor: pointer;
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
  position: relative;
  overflow: hidden;
}

.modern-notification-btn::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0.05));
  opacity: 0;
  transition: opacity 0.3s ease;
}

.modern-notification-btn:hover::before {
  opacity: 1;
}

.modern-notification-btn:hover {
  background: rgba(255, 255, 255, 0.25);
  transform: scale(1.08);
  box-shadow: 0 8px 40px rgba(0, 0, 0, 0.15);
}

.modern-notification-btn:focus {
  outline: none;
  box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.3);
}

.modern-notification-btn:active {
  transform: scale(1.02);
}

.notification-badge {
  position: absolute;
  top: -2px;
  right: -2px;
  background: linear-gradient(135deg, #ef4444, #dc2626);
  color: white;
  border-radius: 50%;
  min-width: 20px;
  height: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.7rem;
  font-weight: 700;
  padding: 0 0.25rem;
  box-shadow: 0 2px 10px rgba(239, 68, 68, 0.3);
  animation: pulse 2s infinite;
  border: 2px solid rgba(255, 255, 255, 0.3);
}

@keyframes pulse {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.1); }
}

.notification-badge.new {
  animation: bounce 0.5s ease-out;
}

@keyframes bounce {
  0% { transform: scale(0); }
  50% { transform: scale(1.2); }
  100% { transform: scale(1); }
}

/* Notifications Dropdown */
.notifications-dropdown {
  position: absolute;
  top: calc(100% + 1rem);
  right: 0;
  width: 380px;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 20px;
  box-shadow: 0 25px 80px rgba(0, 0, 0, 0.15);
  z-index: 1000;
  overflow: hidden;
  animation: slideDown 0.3s ease-out;
}

/* Notifications */
.notification-bell {
  position: relative;
  padding: 0.75rem;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.3s ease;
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.3);
}

.notification-bell:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: translateY(-2px);
}

.notification-bell i {
  color: white;
  font-size: 1.1rem;
}

.notification-badge {
  position: absolute;
  top: -5px;
  right: -5px;
  background: #ff4757;
  color: white;
  border-radius: 50%;
  width: 20px;
  height: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.75rem;
  font-weight: 600;
  border: 2px solid white;
}

/* User Profile */
.user-profile {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  background: rgba(255, 255, 255, 0.15);
  backdrop-filter: blur(20px);
  padding: 0.5rem 1rem;
  border-radius: 50px;
  cursor: pointer;
  border: 1px solid rgba(255, 255, 255, 0.2);
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.user-profile:hover {
  background: rgba(255, 255, 255, 0.25);
  transform: translateY(-1px);
  box-shadow: 0 8px 40px rgba(0, 0, 0, 0.15);
}

.user-profile:focus {
  outline: none;
  box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.3);
}

.user-profile:active {
  transform: translateY(0) scale(0.98);
}

.user-avatar {
  width: 40px;
  height: 40px;
  background: linear-gradient(135deg, #667eea, #764ba2);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 0.9rem;
  font-weight: 600;
  position: relative;
  overflow: hidden;
  border: 2px solid rgba(255, 255, 255, 0.3);
}

.avatar-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 50%;
}

.avatar-initials {
  font-weight: 700;
  letter-spacing: 0.5px;
}

.user-info {
  display: flex;
  flex-direction: column;
}

.user-name {
  color: white;
  font-weight: 600;
  font-size: 0.9rem;
}

.user-role {
  color: rgba(255, 255, 255, 0.8);
  font-size: 0.75rem;
}

.dropdown-trigger {
  background: none;
  border: none;
  color: white;
  cursor: pointer;
  padding: 0.25rem;
  transition: transform 0.3s ease;
  pointer-events: none; /* Since parent handles click now */
}

.dropdown-trigger i {
  transition: transform 0.3s ease;
}

.dropdown-trigger i.rotated {
  transform: rotate(180deg);
}

/* Dropdown Menu */
.dropdown-menu {
  position: absolute;
  top: 100%;
  right: 0;
  margin-top: 0.5rem;
  background: white;
  border-radius: 12px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
  min-width: 200px;
  overflow: hidden;
  z-index: 1000;
  border: 1px solid rgba(0, 0, 0, 0.1);
}

.dropdown-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1rem;
  color: #374151;
  text-decoration: none;
  transition: all 0.2s ease;
  border: none;
  background: none;
  width: 100%;
  text-align: left;
  cursor: pointer;
  font-size: 0.9rem;
}

.dropdown-item:hover {
  background: #f3f4f6;
  color: #1f2937;
}

.dropdown-item i {
  width: 16px;
  color: #6b7280;
}

.dropdown-divider {
  height: 1px;
  background: #e5e7eb;
  margin: 0.5rem 0;
}

.logout-btn:hover {
  background: #fef2f2;
  color: #dc2626;
}

.logout-btn:hover i {
  color: #dc2626;
}

/* Responsive Design */
@media (max-width: 768px) {
  .header-container {
    padding: 0 1rem;
  }
  
  .header-content {
    height: 60px;
  }
  
  .search-container {
    display: none;
  }
  
  .brand-title {
    font-size: 1.25rem;
  }
  
  .brand-subtitle {
    display: none;
  }
  
  .user-info {
    display: none;
  }
  
  .header-actions {
    gap: 1rem;
  }
}

@media (max-width: 480px) {
  .header-actions {
    gap: 0.75rem;
  }
  
  .brand-icon {
    width: 40px;
    height: 40px;
    font-size: 1.25rem;
  }
  
  .notification-bell {
    padding: 0.5rem;
  }
}

/* Notification Dropdown Content Styles */
.notifications-header {
  padding: 1.25rem 1.5rem 1rem;
  border-bottom: 1px solid rgba(0, 0, 0, 0.05);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.notifications-title {
  font-size: 1.1rem;
  font-weight: 700;
  color: #1e293b;
}

.mark-all-read {
  font-size: 0.85rem;
  color: #667eea;
  cursor: pointer;
  font-weight: 600;
  transition: color 0.2s ease;
}

.mark-all-read:hover {
  color: #5a67d8;
}

.notifications-content {
  max-height: 400px;
  overflow-y: auto;
}

.notification-item {
  padding: 1rem 1.5rem;
  border-bottom: 1px solid rgba(0, 0, 0, 0.03);
  display: flex;
  gap: 1rem;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  cursor: pointer;
  position: relative;
  animation: fadeInUp 0.3s ease-out;
  animation-fill-mode: both;
}

.notification-item:nth-child(1) { animation-delay: 0ms; }
.notification-item:nth-child(2) { animation-delay: 50ms; }
.notification-item:nth-child(3) { animation-delay: 100ms; }
.notification-item:nth-child(4) { animation-delay: 150ms; }
.notification-item:nth-child(5) { animation-delay: 200ms; }

.notification-item:last-child {
  border-bottom: none;
}

.notification-item:hover {
  background: rgba(102, 126, 234, 0.05);
}

.notification-item.unread {
  background: rgba(102, 126, 234, 0.02);
}

.notification-item.unread::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  width: 3px;
  background: linear-gradient(135deg, #667eea, #764ba2);
}

.notification-icon {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  font-size: 0.9rem;
  color: white;
}

.notification-icon.expense {
  background: linear-gradient(135deg, #ef4444, #dc2626);
}

.notification-icon.budget {
  background: linear-gradient(135deg, #f59e0b, #d97706);
}

.notification-icon.success {
  background: linear-gradient(135deg, #10b981, #065f46);
}

.notification-content {
  flex: 1;
}

.notification-title {
  font-weight: 600;
  color: #1e293b;
  font-size: 0.9rem;
  margin-bottom: 0.25rem;
}

.notification-message {
  color: #64748b;
  font-size: 0.85rem;
  line-height: 1.4;
}

.notification-time {
  color: #94a3b8;
  font-size: 0.75rem;
  margin-top: 0.5rem;
}

.notification-dismiss {
  position: absolute;
  top: 0.75rem;
  right: 0.75rem;
  color: #94a3b8;
  cursor: pointer;
  padding: 0.25rem;
  border-radius: 50%;
  transition: all 0.2s ease;
  opacity: 0;
}

.notification-item:hover .notification-dismiss {
  opacity: 1;
}

.notification-dismiss:hover {
  color: #64748b;
  background: rgba(0, 0, 0, 0.05);
}

.empty-notifications {
  padding: 3rem 1.5rem;
  text-align: center;
  color: #94a3b8;
}

.empty-notifications i {
  font-size: 2.5rem;
  margin-bottom: 1rem;
  color: #cbd5e1;
}

.empty-notifications p {
  font-size: 0.9rem;
}
</style>