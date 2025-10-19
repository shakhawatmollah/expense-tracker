<template>
  <TouchGestureWrapper
    :enable-swipe="enableSwipe"
    :enable-long-press="enableLongPress"
    :enable-haptic-feedback="enableHaptic"
    @swipe-left="handleSwipeLeft"
    @swipe-right="handleSwipeRight"
    @long-press="handleLongPress"
    @tap="handleTap"
    @double-tap="handleDoubleTap"
    class="mobile-card-wrapper"
  >
    <div 
      :class="[
        'mobile-card',
        variant,
        size,
        {
          'elevated': elevated,
          'interactive': interactive,
          'selected': isSelected,
          'swiping': isSwipingActive,
          'compact': compact
        }
      ]"
      :style="cardStyles"
    >
      <!-- Card Header -->
      <div v-if="hasHeader" class="card-header">
        <div class="header-left">
          <div v-if="icon" class="header-icon" :class="iconColor">
            <i :class="icon"></i>
          </div>
          <div class="header-content">
            <h3 v-if="title" class="card-title">{{ title }}</h3>
            <p v-if="subtitle" class="card-subtitle">{{ subtitle }}</p>
          </div>
        </div>
        
        <div class="header-right">
          <div v-if="badge" class="card-badge" :class="badgeType">
            {{ badge }}
          </div>
          <button 
            v-if="showMenuButton"
            @click="toggleMenu"
            class="menu-button"
            :class="{ 'active': menuOpen }"
          >
            <i class="fas fa-ellipsis-v"></i>
          </button>
        </div>
      </div>

      <!-- Card Content -->
      <div class="card-content">
        <slot />
      </div>

      <!-- Card Footer -->
      <div v-if="hasFooter" class="card-footer">
        <div class="footer-left">
          <span v-if="timestamp" class="card-timestamp">
            {{ formatTimestamp(timestamp) }}
          </span>
          <div v-if="tags && tags.length" class="card-tags">
            <span 
              v-for="tag in tags.slice(0, 3)"
              :key="tag"
              class="tag"
            >
              {{ tag }}
            </span>
            <span v-if="tags.length > 3" class="tag-more">
              +{{ tags.length - 3 }}
            </span>
          </div>
        </div>
        
        <div class="footer-right">
          <div v-if="amount !== null" class="card-amount" :class="amountType">
            {{ formatAmount(amount) }}
          </div>
          <div v-if="showProgress" class="progress-indicator">
            <div class="progress-bar" :style="{ width: `${progress}%` }"></div>
          </div>
        </div>
      </div>

      <!-- Swipe Actions Overlay -->
      <div v-if="swipeActions.length > 0" class="swipe-actions">
        <!-- Left Actions -->
        <div class="action-group left">
          <button
            v-for="action in leftActions"
            :key="action.id"
            @click="handleAction(action)"
            :class="['action-btn', action.type || 'default']"
            :style="{ backgroundColor: action.color }"
          >
            <i :class="action.icon"></i>
            <span>{{ action.label }}</span>
          </button>
        </div>
        
        <!-- Right Actions -->
        <div class="action-group right">
          <button
            v-for="action in rightActions"
            :key="action.id"
            @click="handleAction(action)"
            :class="['action-btn', action.type || 'default']"
            :style="{ backgroundColor: action.color }"
          >
            <i :class="action.icon"></i>
            <span>{{ action.label }}</span>
          </button>
        </div>
      </div>

      <!-- Context Menu -->
      <div v-if="menuOpen" class="context-menu" @click.stop>
        <div 
          v-for="menuItem in contextMenuItems"
          :key="menuItem.id"
          @click="handleMenuAction(menuItem)"
          class="menu-item"
          :class="{ 'danger': menuItem.danger }"
        >
          <i :class="menuItem.icon"></i>
          <span>{{ menuItem.label }}</span>
        </div>
      </div>

      <!-- Loading Overlay -->
      <div v-if="loading" class="loading-overlay">
        <div class="loading-spinner">
          <i class="fas fa-spinner fa-spin"></i>
        </div>
      </div>

      <!-- Selection Indicator -->
      <div v-if="selectable" class="selection-indicator" :class="{ 'selected': isSelected }">
        <i class="fas fa-check"></i>
      </div>

      <!-- Touch Ripple Effect -->
      <div 
        v-if="showRipple"
        class="touch-ripple"
        :style="rippleStyles"
      ></div>
    </div>
  </TouchGestureWrapper>
</template>

<script>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import TouchGestureWrapper from './TouchGestureWrapper.vue'

export default {
  name: 'MobileCard',
  components: {
    TouchGestureWrapper
  },
  props: {
    // Appearance
    variant: {
      type: String,
      default: 'default', // default, success, warning, error, info
      validator: (value) => ['default', 'success', 'warning', 'error', 'info'].includes(value)
    },
    size: {
      type: String,
      default: 'md', // sm, md, lg
      validator: (value) => ['sm', 'md', 'lg'].includes(value)
    },
    elevated: {
      type: Boolean,
      default: false
    },
    compact: {
      type: Boolean,
      default: false
    },
    
    // Content
    title: String,
    subtitle: String,
    icon: String,
    iconColor: String,
    badge: String,
    badgeType: {
      type: String,
      default: 'default'
    },
    amount: {
      type: Number,
      default: null
    },
    amountType: {
      type: String,
      default: 'positive' // positive, negative, neutral
    },
    timestamp: [String, Date, Number],
    tags: Array,
    progress: {
      type: Number,
      default: 0
    },
    
    // Behavior
    interactive: {
      type: Boolean,
      default: true
    },
    selectable: {
      type: Boolean,
      default: false
    },
    selected: {
      type: Boolean,
      default: false
    },
    loading: {
      type: Boolean,
      default: false
    },
    
    // Touch interactions
    enableSwipe: {
      type: Boolean,
      default: true
    },
    enableLongPress: {
      type: Boolean,
      default: true
    },
    enableHaptic: {
      type: Boolean,
      default: true
    },
    swipeActions: {
      type: Array,
      default: () => []
    },
    contextMenuItems: {
      type: Array,
      default: () => []
    }
  },
  emits: [
    'click',
    'double-click',
    'long-press',
    'swipe-action',
    'menu-action',
    'selection-change'
  ],
  setup(props, { emit }) {
    // Reactive state
    const isSelected = ref(props.selected)
    const isSwipingActive = ref(false)
    const menuOpen = ref(false)
    const showRipple = ref(false)
    const ripplePosition = ref({ x: 0, y: 0 })

    // Computed properties
    const hasHeader = computed(() => 
      props.title || props.subtitle || props.icon || props.badge
    )

    const hasFooter = computed(() => 
      props.timestamp || props.tags?.length || props.amount !== null || props.progress > 0
    )

    const showMenuButton = computed(() => 
      props.contextMenuItems.length > 0
    )

    const showProgress = computed(() => 
      props.progress > 0 && props.progress <= 100
    )

    const leftActions = computed(() => 
      props.swipeActions.filter(action => action.position === 'left')
    )

    const rightActions = computed(() => 
      props.swipeActions.filter(action => action.position === 'right' || !action.position)
    )

    const cardStyles = computed(() => ({
      '--ripple-x': `${ripplePosition.value.x}px`,
      '--ripple-y': `${ripplePosition.value.y}px`
    }))

    const rippleStyles = computed(() => ({
      left: `${ripplePosition.value.x}px`,
      top: `${ripplePosition.value.y}px`
    }))

    // Methods
    const handleTap = (position) => {
      if (!props.interactive) return

      // Create ripple effect
      if (position) {
        ripplePosition.value = {
          x: position.x,
          y: position.y
        }
        showRipple.value = true
        setTimeout(() => {
          showRipple.value = false
        }, 600)
      }

      emit('click')
    }

    const handleDoubleTap = (position) => {
      if (!props.interactive) return
      emit('double-click')
      
      // Toggle selection on double tap if selectable
      if (props.selectable) {
        toggleSelection()
      }
    }

    const handleLongPress = (position) => {
      if (!props.interactive) return
      
      // Show context menu if available
      if (props.contextMenuItems.length > 0) {
        toggleMenu()
      }
      
      emit('long-press', position)
    }

    const handleSwipeLeft = () => {
      if (!props.enableSwipe) return
      
      isSwipingActive.value = true
      setTimeout(() => {
        isSwipingActive.value = false
      }, 300)

      // Execute first left action if available
      const leftAction = leftActions.value[0]
      if (leftAction) {
        handleAction(leftAction)
      }
    }

    const handleSwipeRight = () => {
      if (!props.enableSwipe) return
      
      isSwipingActive.value = true
      setTimeout(() => {
        isSwipingActive.value = false
      }, 300)

      // Execute first right action if available
      const rightAction = rightActions.value[0]
      if (rightAction) {
        handleAction(rightAction)
      }
    }

    const handleAction = (action) => {
      emit('swipe-action', action)
    }

    const toggleMenu = () => {
      menuOpen.value = !menuOpen.value
    }

    const handleMenuAction = (menuItem) => {
      menuOpen.value = false
      emit('menu-action', menuItem)
    }

    const toggleSelection = () => {
      if (!props.selectable) return
      
      isSelected.value = !isSelected.value
      emit('selection-change', isSelected.value)
    }

    const formatTimestamp = (timestamp) => {
      if (!timestamp) return ''
      
      const date = new Date(timestamp)
      const now = new Date()
      const diff = now - date
      
      const minutes = Math.floor(diff / 60000)
      const hours = Math.floor(diff / 3600000)
      const days = Math.floor(diff / 86400000)
      
      if (minutes < 1) return 'Just now'
      if (minutes < 60) return `${minutes}m ago`
      if (hours < 24) return `${hours}h ago`
      if (days < 7) return `${days}d ago`
      
      return date.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric'
      })
    }

    const formatAmount = (amount) => {
      if (amount === null || amount === undefined) return ''
      
      return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 2
      }).format(Math.abs(amount))
    }

    // Close menu when clicking outside
    const handleOutsideClick = (event) => {
      if (menuOpen.value && !event.target.closest('.context-menu')) {
        menuOpen.value = false
      }
    }

    onMounted(() => {
      document.addEventListener('click', handleOutsideClick)
    })

    onUnmounted(() => {
      document.removeEventListener('click', handleOutsideClick)
    })

    return {
      // State
      isSelected,
      isSwipingActive,
      menuOpen,
      showRipple,
      ripplePosition,
      
      // Computed
      hasHeader,
      hasFooter,
      showMenuButton,
      showProgress,
      leftActions,
      rightActions,
      cardStyles,
      rippleStyles,
      
      // Methods
      handleTap,
      handleDoubleTap,
      handleLongPress,
      handleSwipeLeft,
      handleSwipeRight,
      handleAction,
      toggleMenu,
      handleMenuAction,
      toggleSelection,
      formatTimestamp,
      formatAmount
    }
  }
}
</script>

<style scoped>
/* Mobile Card Container */
.mobile-card-wrapper {
  position: relative;
  width: 100%;
}

.mobile-card {
  position: relative;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 16px;
  overflow: hidden;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  margin-bottom: 1rem;
}

.mobile-card.elevated {
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
}

.mobile-card.interactive {
  cursor: pointer;
}

.mobile-card.interactive:hover {
  transform: translateY(-2px);
  box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
}

.mobile-card.interactive:active {
  transform: translateY(0);
}

.mobile-card.selected {
  border-color: #667eea;
  box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.2);
}

.mobile-card.swiping {
  transform: scale(0.98);
}

/* Card Variants */
.mobile-card.success {
  border-left: 4px solid #10b981;
}

.mobile-card.warning {
  border-left: 4px solid #f59e0b;
}

.mobile-card.error {
  border-left: 4px solid #ef4444;
}

.mobile-card.info {
  border-left: 4px solid #3b82f6;
}

/* Card Sizes */
.mobile-card.sm {
  padding: 0.75rem;
}

.mobile-card.md {
  padding: 1rem;
}

.mobile-card.lg {
  padding: 1.25rem;
}

.mobile-card.compact {
  padding: 0.5rem 0.75rem;
}

.mobile-card.compact .card-header {
  margin-bottom: 0.5rem;
}

.mobile-card.compact .card-footer {
  margin-top: 0.5rem;
}

/* Card Header */
.card-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1rem;
}

.header-left {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  flex: 1;
  min-width: 0;
}

.header-icon {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.125rem;
  color: white;
  flex-shrink: 0;
  background: linear-gradient(135deg, #6b7280, #4b5563);
}

.header-icon.success {
  background: linear-gradient(135deg, #10b981, #059669);
}

.header-icon.warning {
  background: linear-gradient(135deg, #f59e0b, #d97706);
}

.header-icon.error {
  background: linear-gradient(135deg, #ef4444, #dc2626);
}

.header-icon.info {
  background: linear-gradient(135deg, #3b82f6, #2563eb);
}

.header-icon.primary {
  background: linear-gradient(135deg, #667eea, #764ba2);
}

.header-content {
  flex: 1;
  min-width: 0;
}

.card-title {
  margin: 0 0 0.25rem 0;
  font-size: 1rem;
  font-weight: 600;
  color: #1f2937;
  line-height: 1.4;
}

.card-subtitle {
  margin: 0;
  font-size: 0.875rem;
  color: #6b7280;
  line-height: 1.3;
}

.header-right {
  display: flex;
  align-items: flex-start;
  gap: 0.5rem;
  flex-shrink: 0;
}

.card-badge {
  padding: 0.25rem 0.5rem;
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.025em;
  background: #f3f4f6;
  color: #6b7280;
}

.card-badge.success {
  background: #d1fae5;
  color: #065f46;
}

.card-badge.warning {
  background: #fef3c7;
  color: #92400e;
}

.card-badge.error {
  background: #fee2e2;
  color: #991b1b;
}

.card-badge.info {
  background: #dbeafe;
  color: #1e40af;
}

.menu-button {
  width: 32px;
  height: 32px;
  border: none;
  background: transparent;
  color: #9ca3af;
  cursor: pointer;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
  font-size: 0.875rem;
}

.menu-button:hover,
.menu-button.active {
  background: rgba(0, 0, 0, 0.05);
  color: #6b7280;
}

/* Card Content */
.card-content {
  color: #374151;
  line-height: 1.5;
}

/* Card Footer */
.card-footer {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  margin-top: 1rem;
  gap: 1rem;
}

.footer-left {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  flex: 1;
  min-width: 0;
}

.card-timestamp {
  font-size: 0.75rem;
  color: #9ca3af;
  font-weight: 500;
}

.card-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 0.25rem;
}

.tag {
  padding: 0.125rem 0.375rem;
  background: rgba(102, 126, 234, 0.1);
  color: #667eea;
  border-radius: 4px;
  font-size: 0.7rem;
  font-weight: 500;
}

.tag-more {
  padding: 0.125rem 0.375rem;
  background: rgba(107, 114, 128, 0.1);
  color: #6b7280;
  border-radius: 4px;
  font-size: 0.7rem;
  font-weight: 500;
}

.footer-right {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 0.5rem;
  flex-shrink: 0;
}

.card-amount {
  font-size: 1.125rem;
  font-weight: 700;
  line-height: 1;
}

.card-amount.positive {
  color: #059669;
}

.card-amount.negative {
  color: #dc2626;
}

.card-amount.neutral {
  color: #6b7280;
}

.progress-indicator {
  width: 60px;
  height: 4px;
  background: rgba(0, 0, 0, 0.1);
  border-radius: 2px;
  overflow: hidden;
}

.progress-bar {
  height: 100%;
  background: linear-gradient(90deg, #667eea, #764ba2);
  border-radius: 2px;
  transition: width 0.3s ease;
}

/* Swipe Actions */
.swipe-actions {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  display: flex;
  justify-content: space-between;
  pointer-events: none;
  opacity: 0;
  transition: opacity 0.2s ease;
}

.mobile-card.swiping .swipe-actions {
  pointer-events: auto;
  opacity: 1;
}

.action-group {
  display: flex;
  align-items: center;
}

.action-group.left {
  padding-left: 1rem;
}

.action-group.right {
  padding-right: 1rem;
}

.action-btn {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.25rem;
  padding: 0.75rem;
  border: none;
  border-radius: 8px;
  color: white;
  font-size: 0.7rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  margin: 0 0.25rem;
  min-width: 60px;
  background: #6b7280;
}

.action-btn.primary {
  background: #667eea;
}

.action-btn.success {
  background: #10b981;
}

.action-btn.warning {
  background: #f59e0b;
}

.action-btn.danger {
  background: #ef4444;
}

.action-btn:hover {
  transform: scale(1.05);
}

.action-btn i {
  font-size: 1rem;
}

/* Context Menu */
.context-menu {
  position: absolute;
  top: 100%;
  right: 0;
  margin-top: 0.5rem;
  background: rgba(255, 255, 255, 0.98);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(0, 0, 0, 0.1);
  border-radius: 8px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
  z-index: 100;
  overflow: hidden;
  min-width: 160px;
  animation: menuSlideIn 0.2s ease-out;
}

@keyframes menuSlideIn {
  from {
    opacity: 0;
    transform: translateY(-8px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.menu-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1rem;
  color: #374151;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

.menu-item:last-child {
  border-bottom: none;
}

.menu-item:hover {
  background: rgba(102, 126, 234, 0.05);
  color: #667eea;
}

.menu-item.danger {
  color: #dc2626;
}

.menu-item.danger:hover {
  background: rgba(220, 38, 38, 0.05);
  color: #dc2626;
}

.menu-item i {
  width: 16px;
  text-align: center;
}

/* Loading Overlay */
.loading-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(255, 255, 255, 0.8);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10;
}

.loading-spinner {
  color: #667eea;
  font-size: 1.5rem;
}

/* Selection Indicator */
.selection-indicator {
  position: absolute;
  top: 0.75rem;
  left: 0.75rem;
  width: 24px;
  height: 24px;
  border: 2px solid #d1d5db;
  border-radius: 50%;
  background: white;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
  color: transparent;
  font-size: 0.75rem;
}

.selection-indicator.selected {
  border-color: #667eea;
  background: #667eea;
  color: white;
}

/* Touch Ripple */
.touch-ripple {
  position: absolute;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background: rgba(102, 126, 234, 0.3);
  transform: translate(-50%, -50%) scale(0);
  pointer-events: none;
  animation: rippleEffect 0.6s ease-out;
}

@keyframes rippleEffect {
  to {
    transform: translate(-50%, -50%) scale(6);
    opacity: 0;
  }
}

/* Mobile Optimizations */
@media (max-width: 768px) {
  .mobile-card {
    border-radius: 12px;
    margin-bottom: 0.75rem;
  }
  
  .card-header {
    margin-bottom: 0.75rem;
  }
  
  .header-icon {
    width: 36px;
    height: 36px;
    font-size: 1rem;
  }
  
  .card-title {
    font-size: 0.95rem;
  }
  
  .card-subtitle {
    font-size: 0.8rem;
  }
  
  .card-footer {
    margin-top: 0.75rem;
    gap: 0.75rem;
  }
  
  .card-amount {
    font-size: 1rem;
  }
  
  .context-menu {
    min-width: 140px;
  }
  
  .menu-item {
    padding: 0.625rem 0.875rem;
    font-size: 0.8rem;
  }
}

/* Dark Mode */
@media (prefers-color-scheme: dark) {
  .mobile-card {
    background: rgba(30, 41, 59, 0.95);
    border-color: rgba(71, 85, 105, 0.3);
  }
  
  .card-title {
    color: #f1f5f9;
  }
  
  .card-subtitle {
    color: #94a3b8;
  }
  
  .card-content {
    color: #cbd5e1;
  }
  
  .card-timestamp {
    color: #64748b;
  }
  
  .tag-more {
    background: rgba(148, 163, 184, 0.2);
    color: #94a3b8;
  }
  
  .context-menu {
    background: rgba(30, 41, 59, 0.98);
    border-color: rgba(71, 85, 105, 0.3);
  }
  
  .menu-item {
    color: #cbd5e1;
  }
  
  .menu-item:hover {
    color: #667eea;
  }
  
  .loading-overlay {
    background: rgba(30, 41, 59, 0.8);
  }
  
  .selection-indicator {
    background: rgba(30, 41, 59, 0.9);
    border-color: rgba(71, 85, 105, 0.5);
  }
}

/* High contrast mode */
@media (prefers-contrast: high) {
  .mobile-card {
    border: 2px solid;
  }
  
  .selection-indicator {
    border-width: 3px;
  }
}

/* Reduced motion */
@media (prefers-reduced-motion: reduce) {
  .mobile-card,
  .touch-ripple,
  .context-menu,
  .progress-bar,
  .action-btn {
    animation: none;
    transition: none;
  }
  
  .mobile-card.interactive:hover {
    transform: none;
  }
}
</style>