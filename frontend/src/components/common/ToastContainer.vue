<template>
  <teleport to="body">
    <div class="toast-container" :class="`toast-container-${position}`">
      <transition-group name="toast" tag="div" class="toast-list">
        <div
          v-for="toast in toasts"
          :key="toast.id"
          :class="[
            'toast',
            `toast-${toast.type}`,
            { 'toast-closable': toast.closable }
          ]"
          @click="handleToastClick(toast)"
        >
          <!-- Toast Icon -->
          <div class="toast-icon">
            <i :class="getToastIcon(toast.type)"></i>
          </div>
          
          <!-- Toast Content -->
          <div class="toast-content">
            <div v-if="toast.title" class="toast-title">
              {{ toast.title }}
            </div>
            <div class="toast-message">
              {{ toast.message }}
            </div>
          </div>
          
          <!-- Toast Action -->
          <div v-if="toast.action" class="toast-action">
            <button 
              class="toast-action-btn"
              @click.stop="handleActionClick(toast)"
            >
              {{ toast.action.label }}
            </button>
          </div>
          
          <!-- Close Button -->
          <button
            v-if="toast.closable"
            class="toast-close"
            @click.stop="removeToast(toast.id)"
            aria-label="Close notification"
          >
            <i class="fas fa-times"></i>
          </button>
          
          <!-- Progress Bar -->
          <div
            v-if="toast.duration > 0"
            class="toast-progress"
            :style="{ animationDuration: `${toast.duration}ms` }"
          ></div>
        </div>
      </transition-group>
    </div>
  </teleport>
</template>

<script setup>
import { computed } from 'vue'
import { useToast, TOAST_TYPES } from '@/composables/useToast'

const props = defineProps({
  position: {
    type: String,
    default: 'top-right',
    validator: (value) => [
      'top-left', 'top-center', 'top-right',
      'bottom-left', 'bottom-center', 'bottom-right'
    ].includes(value)
  },
  maxToasts: {
    type: Number,
    default: 5
  }
})

const { toasts, removeToast } = useToast()

// Limit number of toasts displayed
const visibleToasts = computed(() => {
  return toasts.value.slice(-props.maxToasts)
})

// Get icon for toast type
const getToastIcon = (type) => {
  const icons = {
    [TOAST_TYPES.SUCCESS]: 'fas fa-check-circle',
    [TOAST_TYPES.ERROR]: 'fas fa-exclamation-circle',
    [TOAST_TYPES.WARNING]: 'fas fa-exclamation-triangle',
    [TOAST_TYPES.INFO]: 'fas fa-info-circle'
  }
  return icons[type] || icons[TOAST_TYPES.INFO]
}

// Handle toast click
const handleToastClick = (toast) => {
  if (toast.onClick) {
    toast.onClick(toast)
  }
}

// Handle action click
const handleActionClick = (toast) => {
  if (toast.action?.onClick) {
    toast.action.onClick(toast)
  }
}
</script>

<style scoped>
/* Toast Container */
.toast-container {
  position: fixed;
  z-index: 9999;
  pointer-events: none;
  max-width: 420px;
  width: 100%;
}

/* Position variants */
.toast-container-top-left {
  top: 1rem;
  left: 1rem;
}

.toast-container-top-center {
  top: 1rem;
  left: 50%;
  transform: translateX(-50%);
}

.toast-container-top-right {
  top: 1rem;
  right: 1rem;
}

.toast-container-bottom-left {
  bottom: 1rem;
  left: 1rem;
}

.toast-container-bottom-center {
  bottom: 1rem;
  left: 50%;
  transform: translateX(-50%);
}

.toast-container-bottom-right {
  bottom: 1rem;
  right: 1rem;
}

/* Toast List */
.toast-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

/* Toast */
.toast {
  position: relative;
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  padding: 1rem 1.25rem;
  background: white;
  border-radius: 12px;
  box-shadow: 
    0 10px 40px rgba(0, 0, 0, 0.1),
    0 4px 16px rgba(0, 0, 0, 0.08),
    0 0 0 1px rgba(0, 0, 0, 0.04);
  border-left: 4px solid transparent;
  pointer-events: auto;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  overflow: hidden;
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.toast:hover {
  transform: translateY(-2px);
  box-shadow: 
    0 20px 60px rgba(0, 0, 0, 0.15),
    0 8px 24px rgba(0, 0, 0, 0.12),
    0 0 0 1px rgba(0, 0, 0, 0.06);
}

/* Toast Types */
.toast-success {
  border-left-color: #10b981;
  background: linear-gradient(135deg, rgba(16, 185, 129, 0.05), rgba(5, 150, 105, 0.02));
}

.toast-error {
  border-left-color: #ef4444;
  background: linear-gradient(135deg, rgba(239, 68, 68, 0.05), rgba(220, 38, 38, 0.02));
}

.toast-warning {
  border-left-color: #f59e0b;
  background: linear-gradient(135deg, rgba(245, 158, 11, 0.05), rgba(217, 119, 6, 0.02));
}

.toast-info {
  border-left-color: #3b82f6;
  background: linear-gradient(135deg, rgba(59, 130, 246, 0.05), rgba(37, 99, 235, 0.02));
}

/* Toast Icon */
.toast-icon {
  width: 20px;
  height: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  margin-top: 2px;
}

.toast-success .toast-icon {
  color: #10b981;
}

.toast-error .toast-icon {
  color: #ef4444;
}

.toast-warning .toast-icon {
  color: #f59e0b;
}

.toast-info .toast-icon {
  color: #3b82f6;
}

/* Toast Content */
.toast-content {
  flex: 1;
  min-width: 0;
}

.toast-title {
  font-weight: 600;
  color: #1f2937;
  font-size: 0.875rem;
  margin-bottom: 0.25rem;
  line-height: 1.25;
}

.toast-message {
  color: #6b7280;
  font-size: 0.8125rem;
  line-height: 1.4;
}

/* Toast Action */
.toast-action {
  flex-shrink: 0;
  margin-left: 0.5rem;
}

.toast-action-btn {
  padding: 0.375rem 0.75rem;
  background: rgba(59, 130, 246, 0.1);
  color: #3b82f6;
  border: 1px solid rgba(59, 130, 246, 0.2);
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.toast-action-btn:hover {
  background: rgba(59, 130, 246, 0.15);
  border-color: rgba(59, 130, 246, 0.3);
}

/* Toast Close */
.toast-close {
  position: absolute;
  top: 0.75rem;
  right: 0.75rem;
  width: 24px;
  height: 24px;
  background: none;
  border: none;
  color: #9ca3af;
  cursor: pointer;
  border-radius: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
  font-size: 0.75rem;
}

.toast-close:hover {
  background: rgba(107, 114, 128, 0.1);
  color: #6b7280;
}

/* Toast Progress */
.toast-progress {
  position: absolute;
  bottom: 0;
  left: 0;
  height: 3px;
  background: linear-gradient(90deg, 
    rgba(59, 130, 246, 0.8) 0%, 
    rgba(59, 130, 246, 0.4) 100%);
  border-radius: 0 0 12px 12px;
  animation: toast-progress linear forwards;
  transform-origin: left;
}

.toast-success .toast-progress {
  background: linear-gradient(90deg, 
    rgba(16, 185, 129, 0.8) 0%, 
    rgba(16, 185, 129, 0.4) 100%);
}

.toast-error .toast-progress {
  background: linear-gradient(90deg, 
    rgba(239, 68, 68, 0.8) 0%, 
    rgba(239, 68, 68, 0.4) 100%);
}

.toast-warning .toast-progress {
  background: linear-gradient(90deg, 
    rgba(245, 158, 11, 0.8) 0%, 
    rgba(245, 158, 11, 0.4) 100%);
}

@keyframes toast-progress {
  from {
    width: 100%;
  }
  to {
    width: 0%;
  }
}

/* Toast Transitions */
.toast-enter-active {
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.toast-leave-active {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.toast-enter-from {
  opacity: 0;
  transform: translateX(100%) scale(0.95);
}

.toast-leave-to {
  opacity: 0;
  transform: translateX(100%) scale(0.95);
}

.toast-move {
  transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Bottom position animations */
.toast-container-bottom-left .toast-enter-from,
.toast-container-bottom-center .toast-enter-from,
.toast-container-bottom-right .toast-enter-from {
  transform: translateY(100%) scale(0.95);
}

.toast-container-bottom-left .toast-leave-to,
.toast-container-bottom-center .toast-leave-to,
.toast-container-bottom-right .toast-leave-to {
  transform: translateY(100%) scale(0.95);
}

/* Responsive Design */
@media (max-width: 640px) {
  .toast-container {
    left: 1rem !important;
    right: 1rem !important;
    max-width: none;
    transform: none !important;
  }
  
  .toast {
    padding: 0.875rem 1rem;
    border-radius: 10px;
  }
  
  .toast-title {
    font-size: 0.8125rem;
  }
  
  .toast-message {
    font-size: 0.75rem;
  }
  
  .toast-close {
    top: 0.5rem;
    right: 0.5rem;
    width: 20px;
    height: 20px;
  }
}

/* High contrast mode */
@media (prefers-contrast: high) {
  .toast {
    border: 2px solid;
    background: white;
  }
  
  .toast-success {
    border-color: #10b981;
  }
  
  .toast-error {
    border-color: #ef4444;
  }
  
  .toast-warning {
    border-color: #f59e0b;
  }
  
  .toast-info {
    border-color: #3b82f6;
  }
}

/* Reduced motion */
@media (prefers-reduced-motion: reduce) {
  .toast-enter-active,
  .toast-leave-active,
  .toast-move {
    transition: none;
  }
  
  .toast:hover {
    transform: none;
  }
  
  .toast-progress {
    animation: none;
    display: none;
  }
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
  .toast {
    background: rgba(31, 41, 55, 0.95);
    border: 1px solid rgba(75, 85, 99, 0.3);
  }
  
  .toast-title {
    color: #f9fafb;
  }
  
  .toast-message {
    color: #d1d5db;
  }
  
  .toast-close {
    color: #9ca3af;
  }
  
  .toast-close:hover {
    background: rgba(75, 85, 99, 0.2);
    color: #d1d5db;
  }
}

/* Focus styles for accessibility */
.toast-close:focus-visible {
  outline: 2px solid #3b82f6;
  outline-offset: 2px;
}

.toast-action-btn:focus-visible {
  outline: 2px solid #3b82f6;
  outline-offset: 2px;
}
</style>