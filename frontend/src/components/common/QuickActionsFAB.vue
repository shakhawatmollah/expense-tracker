<template>
  <div class="quick-actions-fab" :class="{ 'expanded': isExpanded, 'mobile': isMobile }">
    <!-- Main FAB Button -->
    <button 
      @click="toggleFAB"
      class="main-fab"
      :class="{ 'active': isExpanded }"
      :aria-label="isExpanded ? 'Close quick actions' : 'Open quick actions'"
      :aria-expanded="isExpanded"
    >
      <i :class="isExpanded ? 'fas fa-times' : 'fas fa-plus'"></i>
    </button>

    <!-- Action Buttons -->
    <div class="fab-actions" v-show="isExpanded">
      <!-- Add Expense -->
      <button 
        @click="handleAction('add-expense')"
        class="fab-action expense"
        title="Add New Expense (Ctrl+E)"
        data-action="add-expense"
      >
        <i class="fas fa-receipt"></i>
        <span class="fab-label">Add Expense</span>
      </button>

      <!-- Add Category -->
      <button 
        @click="handleAction('add-category')"
        class="fab-action category"
        title="Add New Category (Ctrl+C)"
        data-action="add-category"
      >
        <i class="fas fa-tag"></i>
        <span class="fab-label">Add Category</span>
      </button>

      <!-- Set Budget -->
      <button 
        @click="handleAction('set-budget')"
        class="fab-action budget"
        title="Set Budget (Ctrl+B)"
        data-action="set-budget"
      >
        <i class="fas fa-bullseye"></i>
        <span class="fab-label">Set Budget</span>
      </button>

      <!-- View Analytics -->
      <button 
        @click="handleAction('view-analytics')"
        class="fab-action analytics"
        title="View Analytics (Ctrl+A)"
        data-action="view-analytics"
      >
        <i class="fas fa-chart-line"></i>
        <span class="fab-label">Analytics</span>
      </button>

      <!-- Export Data -->
      <button 
        @click="handleAction('export-data')"
        class="fab-action export"
        title="Export Data (Ctrl+Shift+E)"
        data-action="export-data"
      >
        <i class="fas fa-download"></i>
        <span class="fab-label">Export</span>
      </button>

      <!-- Search -->
      <button 
        @click="handleAction('search')"
        class="fab-action search"
        title="Search (Ctrl+/)"
        data-action="search"
      >
        <i class="fas fa-search"></i>
        <span class="fab-label">Search</span>
      </button>
    </div>

    <!-- Background Overlay -->
    <div 
      v-if="isExpanded" 
      @click="closeFAB"
      class="fab-overlay"
    ></div>
  </div>
</template>

<script>
import { ref, onMounted, onUnmounted } from 'vue'

export default {
  name: 'QuickActionsFAB',
  emits: ['action'],
  setup(props, { emit }) {
    const isExpanded = ref(false)
    const isMobile = ref(false)

    // Detect mobile
    const detectMobile = () => {
      isMobile.value = window.innerWidth <= 768
    }

    // Toggle FAB
    const toggleFAB = () => {
      isExpanded.value = !isExpanded.value
    }

    const closeFAB = () => {
      isExpanded.value = false
    }

    // Handle action
    const handleAction = (action) => {
      emit('action', action)
      closeFAB()
    }

    // Keyboard shortcuts
    const handleKeyDown = (event) => {
      // Check for modifier keys
      const isCtrl = event.ctrlKey || event.metaKey
      const isShift = event.shiftKey

      if (isCtrl && !isShift) {
        switch (event.key.toLowerCase()) {
          case 'e':
            event.preventDefault()
            handleAction('add-expense')
            break
          case 'c':
            event.preventDefault()
            handleAction('add-category')
            break
          case 'b':
            event.preventDefault()
            handleAction('set-budget')
            break
          case 'a':
            event.preventDefault()
            handleAction('view-analytics')
            break
          case '/':
            event.preventDefault()
            handleAction('search')
            break
        }
      } else if (isCtrl && isShift) {
        switch (event.key.toLowerCase()) {
          case 'e':
            event.preventDefault()
            handleAction('export-data')
            break
        }
      }

      // ESC to close FAB
      if (event.key === 'Escape' && isExpanded.value) {
        closeFAB()
      }
    }

    // Close FAB when clicking outside
    const handleClickOutside = (event) => {
      if (isExpanded.value && !event.target.closest('.quick-actions-fab')) {
        closeFAB()
      }
    }

    // Lifecycle
    onMounted(() => {
      detectMobile()
      document.addEventListener('keydown', handleKeyDown)
      document.addEventListener('click', handleClickOutside)
      window.addEventListener('resize', detectMobile)
    })

    onUnmounted(() => {
      document.removeEventListener('keydown', handleKeyDown)
      document.removeEventListener('click', handleClickOutside)
      window.removeEventListener('resize', detectMobile)
    })

    return {
      isExpanded,
      isMobile,
      toggleFAB,
      closeFAB,
      handleAction
    }
  }
}
</script>

<style scoped>
.quick-actions-fab {
  position: fixed;
  bottom: 2rem;
  right: 2rem;
  z-index: 1000;
  user-select: none;
}

/* Main FAB Button */
.main-fab {
  width: 64px;
  height: 64px;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border: none;
  color: white;
  font-size: 1.5rem;
  cursor: pointer;
  box-shadow: 0 8px 24px rgba(102, 126, 234, 0.4);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  overflow: hidden;
}

.main-fab::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(45deg, transparent 30%, rgba(255, 255, 255, 0.1) 50%, transparent 70%);
  transform: translateX(-100%);
  transition: transform 0.6s;
}

.main-fab:hover::before {
  transform: translateX(100%);
}

.main-fab:hover {
  transform: scale(1.1);
  box-shadow: 0 12px 32px rgba(102, 126, 234, 0.5);
}

.main-fab.active {
  transform: rotate(45deg) scale(1.1);
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
  box-shadow: 0 12px 32px rgba(239, 68, 68, 0.5);
}

.main-fab i {
  transition: all 0.3s ease;
  position: relative;
  z-index: 2;
}

/* FAB Actions Container */
.fab-actions {
  position: absolute;
  bottom: 80px;
  right: 0;
  display: flex;
  flex-direction: column-reverse;
  gap: 1rem;
  animation: fabActionsSlideIn 0.4s ease-out forwards;
}

@keyframes fabActionsSlideIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Individual Action Buttons */
.fab-action {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1rem;
  border: none;
  border-radius: 25px;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  color: #374151;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  white-space: nowrap;
  min-width: 48px;
  justify-content: flex-start;
  position: relative;
  overflow: hidden;
  animation: fabActionSlideIn 0.3s ease-out forwards;
  opacity: 0;
}

.fab-action:nth-child(1) { animation-delay: 0.1s; }
.fab-action:nth-child(2) { animation-delay: 0.15s; }
.fab-action:nth-child(3) { animation-delay: 0.2s; }
.fab-action:nth-child(4) { animation-delay: 0.25s; }
.fab-action:nth-child(5) { animation-delay: 0.3s; }
.fab-action:nth-child(6) { animation-delay: 0.35s; }

@keyframes fabActionSlideIn {
  from {
    opacity: 0;
    transform: translateX(50px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

.fab-action:hover {
  transform: translateX(-8px) scale(1.05);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
  background: rgba(255, 255, 255, 1);
}

.fab-action i {
  width: 20px;
  height: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 6px;
  font-size: 0.875rem;
  transition: all 0.2s ease;
}

/* Action-specific colors */
.fab-action.expense i {
  background: linear-gradient(135deg, #10b981, #059669);
  color: white;
}

.fab-action.category i {
  background: linear-gradient(135deg, #f59e0b, #d97706);
  color: white;
}

.fab-action.budget i {
  background: linear-gradient(135deg, #8b5cf6, #7c3aed);
  color: white;
}

.fab-action.analytics i {
  background: linear-gradient(135deg, #3b82f6, #2563eb);
  color: white;
}

.fab-action.export i {
  background: linear-gradient(135deg, #6b7280, #4b5563);
  color: white;
}

.fab-action.search i {
  background: linear-gradient(135deg, #ef4444, #dc2626);
  color: white;
}

.fab-label {
  font-weight: 600;
  letter-spacing: 0.025em;
}

/* Background Overlay */
.fab-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.2);
  backdrop-filter: blur(2px);
  z-index: -1;
  animation: overlayFadeIn 0.3s ease-out;
}

@keyframes overlayFadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

/* Mobile Styles */
.quick-actions-fab.mobile {
  bottom: 1rem;
  right: 1rem;
}

.quick-actions-fab.mobile .main-fab {
  width: 56px;
  height: 56px;
  font-size: 1.25rem;
}

.quick-actions-fab.mobile .fab-actions {
  bottom: 72px;
}

.quick-actions-fab.mobile .fab-action {
  padding: 0.625rem 0.875rem;
  font-size: 0.8rem;
  border-radius: 20px;
}

.quick-actions-fab.mobile .fab-action i {
  width: 18px;
  height: 18px;
  font-size: 0.75rem;
}

/* Mobile landscape adjustments */
@media (max-height: 500px) and (orientation: landscape) {
  .quick-actions-fab {
    bottom: 1rem;
    right: 1rem;
  }
  
  .fab-actions {
    bottom: 64px;
    max-height: calc(100vh - 120px);
    overflow-y: auto;
  }
}

/* Touch device optimizations */
@media (hover: none) and (pointer: coarse) {
  .main-fab:hover,
  .fab-action:hover {
    transform: none;
  }
  
  .main-fab:active {
    transform: scale(0.95);
  }
  
  .fab-action:active {
    transform: scale(0.95);
    background: rgba(255, 255, 255, 1);
  }
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
  .main-fab,
  .fab-action,
  .fab-actions,
  .fab-overlay {
    animation: none;
    transition: none;
  }
  
  .main-fab:hover,
  .fab-action:hover {
    transform: none;
  }
}

/* High contrast mode */
@media (prefers-contrast: high) {
  .main-fab,
  .fab-action {
    border: 2px solid;
  }
  
  .fab-action {
    background: white;
    border-color: #000;
  }
}

/* Dark mode */
@media (prefers-color-scheme: dark) {
  .fab-action {
    background: rgba(30, 41, 59, 0.95);
    color: #e2e8f0;
    border: 1px solid rgba(71, 85, 105, 0.3);
  }
  
  .fab-action:hover {
    background: rgba(30, 41, 59, 1);
    border-color: rgba(71, 85, 105, 0.5);
  }
  
  .fab-overlay {
    background: rgba(0, 0, 0, 0.4);
  }
}

/* Focus styles for accessibility */
.main-fab:focus,
.fab-action:focus {
  outline: none;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.5);
}

/* Loading state */
.fab-action.loading {
  pointer-events: none;
  opacity: 0.7;
}

.fab-action.loading i {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
</style>