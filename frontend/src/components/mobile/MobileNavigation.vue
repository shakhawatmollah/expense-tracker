<template>
  <div class="mobile-navigation" :class="{ 'active': isVisible }">
    <!-- Mobile Header Bar -->
    <div class="mobile-header">
      <div class="header-left">
        <button @click="toggleSidebar" class="menu-btn">
          <i class="fas fa-bars"></i>
        </button>
        <div class="logo">
          <i class="fas fa-chart-pie"></i>
          <span>Expense Tracker</span>
        </div>
      </div>
      
      <div class="header-right">
        <button @click="openNotifications" class="notification-btn" :class="{ 'has-unread': hasUnread }">
          <i class="fas fa-bell"></i>
          <span v-if="unreadCount > 0" class="notification-badge">{{ unreadCount }}</span>
        </button>
        <button @click="openProfile" class="profile-btn">
          <div class="profile-avatar">
            <i class="fas fa-user"></i>
          </div>
        </button>
      </div>
    </div>

    <!-- Mobile Sidebar Overlay -->
    <div 
      v-if="sidebarOpen" 
      @click="closeSidebar"
      class="sidebar-overlay"
    ></div>

    <!-- Mobile Sidebar -->
    <div class="mobile-sidebar" :class="{ 'open': sidebarOpen }">
      <div class="sidebar-header">
        <div class="user-info">
          <div class="user-avatar">
            <i class="fas fa-user"></i>
          </div>
          <div class="user-details">
            <h3>Welcome back!</h3>
            <p>Manage your expenses</p>
          </div>
        </div>
        <button @click="closeSidebar" class="close-btn">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <div class="sidebar-content">
        <!-- Main Navigation -->
        <nav class="sidebar-nav">
          <div class="nav-section">
            <h4 class="nav-title">Main</h4>
            <router-link 
              v-for="item in mainNavItems"
              :key="item.path"
              :to="item.path"
              @click="handleNavClick"
              class="nav-item"
              :class="{ 'active': isActiveRoute(item.path) }"
            >
              <i :class="item.icon"></i>
              <span>{{ item.label }}</span>
              <div class="nav-indicator"></div>
            </router-link>
          </div>

          <div class="nav-section">
            <h4 class="nav-title">Management</h4>
            <router-link 
              v-for="item in managementNavItems"
              :key="item.path"
              :to="item.path"
              @click="handleNavClick"
              class="nav-item"
              :class="{ 'active': isActiveRoute(item.path) }"
            >
              <i :class="item.icon"></i>
              <span>{{ item.label }}</span>
              <div class="nav-indicator"></div>
            </router-link>
          </div>
        </nav>

        <!-- Quick Actions -->
        <div class="quick-actions">
          <h4 class="section-title">Quick Actions</h4>
          <div class="action-grid">
            <button 
              v-for="action in quickActions"
              :key="action.id"
              @click="handleQuickAction(action)"
              class="action-btn"
            >
              <i :class="action.icon"></i>
              <span>{{ action.label }}</span>
            </button>
          </div>
        </div>

        <!-- Recent Activity -->
        <div class="recent-activity">
          <h4 class="section-title">Recent</h4>
          <div class="activity-list">
            <div 
              v-for="activity in recentActivities"
              :key="activity.id"
              class="activity-item"
              @click="viewActivity(activity)"
            >
              <div class="activity-icon" :class="activity.type">
                <i :class="getActivityIcon(activity.type)"></i>
              </div>
              <div class="activity-content">
                <div class="activity-title">{{ activity.title }}</div>
                <div class="activity-time">{{ formatTime(activity.timestamp) }}</div>
              </div>
              <div class="activity-amount" v-if="activity.amount">
                {{ formatCurrency(activity.amount) }}
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Sidebar Footer -->
      <div class="sidebar-footer">
        <button @click="openSettings" class="footer-btn">
          <i class="fas fa-cog"></i>
          <span>Settings</span>
        </button>
        <button @click="openHelp" class="footer-btn">
          <i class="fas fa-question-circle"></i>
          <span>Help</span>
        </button>
      </div>
    </div>

    <!-- Bottom Navigation -->
    <div class="bottom-navigation">
      <router-link 
        v-for="item in bottomNavItems"
        :key="item.path"
        :to="item.path"
        class="bottom-nav-item"
        :class="{ 'active': isActiveRoute(item.path) }"
      >
        <i :class="item.icon"></i>
        <span>{{ item.label }}</span>
        <div class="nav-ripple"></div>
      </router-link>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRoute } from 'vue-router'

export default {
  name: 'MobileNavigation',
  emits: ['quick-action', 'navigation-change'],
  setup(props, { emit }) {
    const route = useRoute()
    
    // Reactive state
    const isVisible = ref(true)
    const sidebarOpen = ref(false)
    const hasUnread = ref(true)
    const unreadCount = ref(3)
    
    // Navigation items
    const mainNavItems = [
      { path: '/dashboard', label: 'Dashboard', icon: 'fas fa-chart-pie' },
      { path: '/expenses', label: 'Expenses', icon: 'fas fa-receipt' },
      { path: '/budgets', label: 'Budgets', icon: 'fas fa-bullseye' },
      { path: '/analytics', label: 'Analytics', icon: 'fas fa-chart-line' }
    ]

    const managementNavItems = [
      { path: '/categories', label: 'Categories', icon: 'fas fa-tags' },
      { path: '/goals', label: 'Goals', icon: 'fas fa-target' },
      { path: '/reports', label: 'Reports', icon: 'fas fa-file-alt' }
    ]

    const bottomNavItems = [
      { path: '/dashboard', label: 'Home', icon: 'fas fa-home' },
      { path: '/expenses', label: 'Expenses', icon: 'fas fa-receipt' },
      { path: '/budgets', label: 'Budgets', icon: 'fas fa-bullseye' },
      { path: '/analytics', label: 'Analytics', icon: 'fas fa-chart-line' },
      { path: '/profile', label: 'Profile', icon: 'fas fa-user' }
    ]

    const quickActions = [
      { id: 'add-expense', label: 'Add Expense', icon: 'fas fa-plus' },
      { id: 'scan-receipt', label: 'Scan Receipt', icon: 'fas fa-camera' },
      { id: 'set-budget', label: 'Set Budget', icon: 'fas fa-target' },
      { id: 'export-data', label: 'Export', icon: 'fas fa-download' }
    ]

    // Mock recent activities
    const recentActivities = ref([
      {
        id: 1,
        type: 'expense',
        title: 'Coffee at Starbucks',
        amount: 5.50,
        timestamp: new Date(Date.now() - 1000 * 60 * 30) // 30 min ago
      },
      {
        id: 2,
        type: 'budget',
        title: 'Food budget updated',
        timestamp: new Date(Date.now() - 1000 * 60 * 60 * 2) // 2 hours ago
      },
      {
        id: 3,
        type: 'goal',
        title: 'Savings goal achieved',
        timestamp: new Date(Date.now() - 1000 * 60 * 60 * 24) // 1 day ago
      }
    ])

    // Methods
    const toggleSidebar = () => {
      sidebarOpen.value = !sidebarOpen.value
    }

    const closeSidebar = () => {
      sidebarOpen.value = false
    }

    const openNotifications = () => {
      emit('quick-action', 'open-notifications')
    }

    const openProfile = () => {
      emit('quick-action', 'open-profile')
    }

    const openSettings = () => {
      closeSidebar()
      emit('quick-action', 'open-settings')
    }

    const openHelp = () => {
      closeSidebar()
      emit('quick-action', 'open-help')
    }

    const handleNavClick = () => {
      closeSidebar()
      emit('navigation-change')
    }

    const handleQuickAction = (action) => {
      closeSidebar()
      emit('quick-action', action.id)
    }

    const viewActivity = (activity) => {
      closeSidebar()
      emit('quick-action', `view-${activity.type}`, activity)
    }

    const isActiveRoute = (path) => {
      return route.path === path || (path === '/dashboard' && route.path === '/')
    }

    const getActivityIcon = (type) => {
      const icons = {
        expense: 'fas fa-receipt',
        budget: 'fas fa-bullseye',
        goal: 'fas fa-target',
        category: 'fas fa-tag'
      }
      return icons[type] || 'fas fa-circle'
    }

    const formatTime = (timestamp) => {
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

    const formatCurrency = (amount) => {
      return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
      }).format(amount)
    }

    // Handle route changes
    const handleRouteChange = () => {
      closeSidebar()
    }

    // Keyboard shortcuts
    const handleKeyDown = (event) => {
      // ESC to close sidebar
      if (event.key === 'Escape' && sidebarOpen.value) {
        closeSidebar()
      }
    }

    onMounted(() => {
      document.addEventListener('keydown', handleKeyDown)
    })

    onUnmounted(() => {
      document.removeEventListener('keydown', handleKeyDown)
    })

    return {
      // State
      isVisible,
      sidebarOpen,
      hasUnread,
      unreadCount,
      
      // Data
      mainNavItems,
      managementNavItems,
      bottomNavItems,
      quickActions,
      recentActivities,
      
      // Methods
      toggleSidebar,
      closeSidebar,
      openNotifications,
      openProfile,
      openSettings,
      openHelp,
      handleNavClick,
      handleQuickAction,
      viewActivity,
      isActiveRoute,
      getActivityIcon,
      formatTime,
      formatCurrency
    }
  }
}
</script>

<style scoped>
/* Mobile Navigation Container */
.mobile-navigation {
  position: relative;
  z-index: 1000;
}

/* Mobile Header */
.mobile-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border-bottom: 1px solid rgba(0, 0, 0, 0.1);
  position: sticky;
  top: 0;
  z-index: 1001;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.menu-btn {
  width: 40px;
  height: 40px;
  border: none;
  background: transparent;
  color: #374151;
  cursor: pointer;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
  font-size: 1.125rem;
}

.menu-btn:hover {
  background: rgba(0, 0, 0, 0.05);
}

.logo {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 700;
  color: #1f2937;
}

.logo i {
  width: 32px;
  height: 32px;
  background: linear-gradient(135deg, #667eea, #764ba2);
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1rem;
}

.header-right {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.notification-btn,
.profile-btn {
  position: relative;
  width: 40px;
  height: 40px;
  border: none;
  background: transparent;
  color: #374151;
  cursor: pointer;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
}

.notification-btn:hover,
.profile-btn:hover {
  background: rgba(0, 0, 0, 0.05);
}

.notification-btn.has-unread {
  animation: bellShake 0.5s ease-in-out;
}

@keyframes bellShake {
  0%, 100% { transform: rotate(0deg); }
  25% { transform: rotate(-10deg); }
  75% { transform: rotate(10deg); }
}

.notification-badge {
  position: absolute;
  top: 8px;
  right: 8px;
  background: #ef4444;
  color: white;
  font-size: 0.6rem;
  font-weight: 600;
  padding: 0.125rem 0.25rem;
  border-radius: 6px;
  min-width: 16px;
  text-align: center;
}

.profile-avatar {
  width: 32px;
  height: 32px;
  background: linear-gradient(135deg, #f3f4f6, #e5e7eb);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #6b7280;
  font-size: 0.875rem;
}

/* Sidebar Overlay */
.sidebar-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.3);
  backdrop-filter: blur(4px);
  z-index: 1001;
  animation: overlayFadeIn 0.3s ease-out;
}

@keyframes overlayFadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

/* Mobile Sidebar */
.mobile-sidebar {
  position: fixed;
  top: 0;
  left: 0;
  width: 320px;
  height: 100vh;
  background: rgba(255, 255, 255, 0.98);
  backdrop-filter: blur(20px);
  border-right: 1px solid rgba(0, 0, 0, 0.1);
  z-index: 1002;
  transform: translateX(-100%);
  transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  overflow-y: auto;
  display: flex;
  flex-direction: column;
}

.mobile-sidebar.open {
  transform: translateX(0);
}

.sidebar-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  border-bottom: 1px solid rgba(0, 0, 0, 0.1);
}

.user-info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.user-avatar {
  width: 48px;
  height: 48px;
  background: linear-gradient(135deg, #667eea, #764ba2);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1.25rem;
}

.user-details h3 {
  margin: 0;
  font-size: 1rem;
  font-weight: 600;
  color: #1f2937;
}

.user-details p {
  margin: 0;
  font-size: 0.875rem;
  color: #6b7280;
}

.close-btn {
  width: 36px;
  height: 36px;
  border: none;
  background: transparent;
  color: #6b7280;
  cursor: pointer;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
}

.close-btn:hover {
  background: rgba(0, 0, 0, 0.05);
  color: #374151;
}

/* Sidebar Content */
.sidebar-content {
  flex: 1;
  padding: 1rem 0;
  overflow-y: auto;
}

.nav-section {
  margin-bottom: 2rem;
}

.nav-title,
.section-title {
  padding: 0 1.5rem;
  margin: 0 0 0.75rem 0;
  font-size: 0.75rem;
  font-weight: 600;
  color: #9ca3af;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.nav-item {
  position: relative;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.875rem 1.5rem;
  color: #374151;
  text-decoration: none;
  transition: all 0.2s ease;
  font-weight: 500;
}

.nav-item:hover {
  background: rgba(102, 126, 234, 0.05);
  color: #667eea;
}

.nav-item.active {
  background: rgba(102, 126, 234, 0.1);
  color: #667eea;
  font-weight: 600;
}

.nav-item i {
  width: 20px;
  text-align: center;
  font-size: 1rem;
}

.nav-indicator {
  position: absolute;
  left: 0;
  top: 50%;
  transform: translateY(-50%);
  width: 3px;
  height: 24px;
  background: #667eea;
  border-radius: 0 2px 2px 0;
  opacity: 0;
  transition: opacity 0.2s ease;
}

.nav-item.active .nav-indicator {
  opacity: 1;
}

/* Quick Actions */
.quick-actions {
  padding: 0 1.5rem;
  margin-bottom: 2rem;
}

.action-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 0.75rem;
}

.action-btn {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
  padding: 1rem 0.75rem;
  background: rgba(102, 126, 234, 0.05);
  border: 1px solid rgba(102, 126, 234, 0.1);
  border-radius: 12px;
  color: #667eea;
  font-size: 0.8rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.action-btn:hover {
  background: rgba(102, 126, 234, 0.1);
  border-color: rgba(102, 126, 234, 0.2);
  transform: translateY(-1px);
}

.action-btn i {
  font-size: 1.25rem;
}

/* Recent Activity */
.recent-activity {
  padding: 0 1.5rem;
}

.activity-list {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.activity-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem;
  background: rgba(0, 0, 0, 0.02);
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.activity-item:hover {
  background: rgba(0, 0, 0, 0.05);
}

.activity-icon {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 0.875rem;
  flex-shrink: 0;
}

.activity-icon.expense {
  background: linear-gradient(135deg, #10b981, #059669);
}

.activity-icon.budget {
  background: linear-gradient(135deg, #f59e0b, #d97706);
}

.activity-icon.goal {
  background: linear-gradient(135deg, #8b5cf6, #7c3aed);
}

.activity-content {
  flex: 1;
  min-width: 0;
}

.activity-title {
  font-weight: 500;
  color: #1f2937;
  font-size: 0.875rem;
  margin-bottom: 0.125rem;
}

.activity-time {
  font-size: 0.75rem;
  color: #9ca3af;
}

.activity-amount {
  font-weight: 600;
  color: #059669;
  font-size: 0.875rem;
}

/* Sidebar Footer */
.sidebar-footer {
  padding: 1rem 1.5rem;
  border-top: 1px solid rgba(0, 0, 0, 0.1);
  display: flex;
  gap: 0.5rem;
}

.footer-btn {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.75rem;
  background: transparent;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  color: #6b7280;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.footer-btn:hover {
  background: rgba(0, 0, 0, 0.02);
  border-color: #d1d5db;
}

/* Bottom Navigation */
.bottom-navigation {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  display: flex;
  background: rgba(255, 255, 255, 0.98);
  backdrop-filter: blur(20px);
  border-top: 1px solid rgba(0, 0, 0, 0.1);
  padding: 0.5rem 0 env(safe-area-inset-bottom, 0.5rem) 0;
  z-index: 1000;
}

.bottom-nav-item {
  position: relative;
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.25rem;
  padding: 0.5rem 0.25rem;
  color: #9ca3af;
  text-decoration: none;
  font-size: 0.7rem;
  font-weight: 500;
  transition: all 0.2s ease;
  overflow: hidden;
}

.bottom-nav-item:hover {
  color: #667eea;
}

.bottom-nav-item.active {
  color: #667eea;
}

.bottom-nav-item i {
  font-size: 1.125rem;
  margin-bottom: 0.125rem;
}

.nav-ripple {
  position: absolute;
  top: 0;
  left: 50%;
  transform: translateX(-50%);
  width: 32px;
  height: 2px;
  background: #667eea;
  border-radius: 0 0 2px 2px;
  opacity: 0;
  transition: opacity 0.2s ease;
}

.bottom-nav-item.active .nav-ripple {
  opacity: 1;
}

/* Responsive Design */
@media (max-width: 480px) {
  .mobile-sidebar {
    width: 280px;
  }
  
  .sidebar-header {
    padding: 1rem;
  }
  
  .user-avatar {
    width: 40px;
    height: 40px;
    font-size: 1rem;
  }
  
  .user-details h3 {
    font-size: 0.9rem;
  }
  
  .user-details p {
    font-size: 0.8rem;
  }
  
  .nav-item {
    padding: 0.75rem 1rem;
  }
  
  .quick-actions {
    padding: 0 1rem;
  }
  
  .recent-activity {
    padding: 0 1rem;
  }
  
  .bottom-nav-item {
    font-size: 0.65rem;
  }
  
  .bottom-nav-item i {
    font-size: 1rem;
  }
}

/* Dark Mode */
@media (prefers-color-scheme: dark) {
  .mobile-header {
    background: rgba(30, 41, 59, 0.95);
    border-bottom-color: rgba(71, 85, 105, 0.3);
  }
  
  .menu-btn,
  .notification-btn,
  .profile-btn {
    color: #f1f5f9;
  }
  
  .menu-btn:hover,
  .notification-btn:hover,
  .profile-btn:hover {
    background: rgba(255, 255, 255, 0.1);
  }
  
  .logo {
    color: #f1f5f9;
  }
  
  .mobile-sidebar {
    background: rgba(30, 41, 59, 0.98);
    border-right-color: rgba(71, 85, 105, 0.3);
  }
  
  .user-details h3 {
    color: #f1f5f9;
  }
  
  .user-details p {
    color: #94a3b8;
  }
  
  .close-btn {
    color: #94a3b8;
  }
  
  .close-btn:hover {
    background: rgba(255, 255, 255, 0.1);
    color: #f1f5f9;
  }
  
  .nav-item {
    color: #cbd5e1;
  }
  
  .nav-item:hover,
  .nav-item.active {
    color: #667eea;
  }
  
  .activity-title {
    color: #f1f5f9;
  }
  
  .footer-btn {
    color: #94a3b8;
    border-color: rgba(71, 85, 105, 0.5);
  }
  
  .footer-btn:hover {
    background: rgba(255, 255, 255, 0.05);
    border-color: rgba(71, 85, 105, 0.7);
  }
  
  .bottom-navigation {
    background: rgba(30, 41, 59, 0.98);
    border-top-color: rgba(71, 85, 105, 0.3);
  }
  
  .bottom-nav-item {
    color: #64748b;
  }
  
  .bottom-nav-item:hover,
  .bottom-nav-item.active {
    color: #667eea;
  }
}

/* Touch Feedback */
.menu-btn:active,
.notification-btn:active,
.profile-btn:active,
.close-btn:active,
.nav-item:active,
.action-btn:active,
.activity-item:active,
.footer-btn:active,
.bottom-nav-item:active {
  transform: scale(0.95);
}

/* Safe Area Support */
@supports (padding: max(0px)) {
  .mobile-header {
    padding-top: max(1rem, env(safe-area-inset-top));
    padding-left: max(1rem, env(safe-area-inset-left));
    padding-right: max(1rem, env(safe-area-inset-right));
  }
  
  .mobile-sidebar {
    padding-top: env(safe-area-inset-top);
  }
  
  .bottom-navigation {
    padding-bottom: max(0.5rem, env(safe-area-inset-bottom));
    padding-left: env(safe-area-inset-left);
    padding-right: env(safe-area-inset-right);
  }
}
</style>