<template>
  <div class="error-state" :class="[variant, size]">
    <div class="error-container">
      <!-- Error Icon -->
      <div class="error-icon" :class="iconType">
        <div class="icon-wrapper">
          <i :class="iconClass"></i>
          <div class="icon-pulse" v-if="animated"></div>
        </div>
      </div>
      
      <!-- Error Content -->
      <div class="error-content">
        <h3 class="error-title">{{ title || defaultTitle }}</h3>
        <p class="error-message">{{ message || defaultMessage }}</p>
        
        <!-- Error Code -->
        <div class="error-code" v-if="code">
          <span class="code-label">Error Code:</span>
          <span class="code-value">{{ code }}</span>
        </div>
        
        <!-- Error Details -->
        <div class="error-details" v-if="showDetails && details">
          <button 
            @click="toggleDetails" 
            class="details-toggle"
            :class="{ expanded: detailsExpanded }"
          >
            <i class="fas fa-chevron-down"></i>
            {{ detailsExpanded ? 'Hide Details' : 'Show Details' }}
          </button>
          <div class="details-content" v-show="detailsExpanded">
            <pre>{{ details }}</pre>
          </div>
        </div>
      </div>
      
      <!-- Error Actions -->
      <div class="error-actions" v-if="showActions">
        <button 
          v-if="showRetry"
          @click="$emit('retry')" 
          class="action-btn primary"
          :disabled="retrying"
        >
          <i class="fas fa-redo-alt" :class="{ 'fa-spin': retrying }"></i>
          {{ retrying ? 'Retrying...' : 'Try Again' }}
        </button>
        
        <button 
          v-if="showRefresh"
          @click="$emit('refresh')" 
          class="action-btn secondary"
        >
          <i class="fas fa-sync-alt"></i>
          Refresh Page
        </button>
        
        <button 
          v-if="showContact"
          @click="$emit('contact')" 
          class="action-btn tertiary"
        >
          <i class="fas fa-envelope"></i>
          Contact Support
        </button>
        
        <router-link 
          v-if="showHome"
          to="/" 
          class="action-btn secondary"
        >
          <i class="fas fa-home"></i>
          Go Home
        </router-link>
        
        <!-- Custom Actions -->
        <slot name="actions"></slot>
      </div>
    </div>
    
    <!-- Background Pattern -->
    <div class="error-background" v-if="showBackground">
      <div class="bg-pattern"></div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  type: {
    type: String,
    default: 'general',
    validator: (value) => ['general', 'network', 'permission', 'not-found', 'server', 'validation', 'timeout'].includes(value)
  },
  variant: {
    type: String,
    default: 'default',
    validator: (value) => ['default', 'minimal', 'detailed', 'fullscreen'].includes(value)
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg', 'xl'].includes(value)
  },
  title: {
    type: String,
    default: ''
  },
  message: {
    type: String,
    default: ''
  },
  code: {
    type: [String, Number],
    default: null
  },
  details: {
    type: String,
    default: ''
  },
  showDetails: {
    type: Boolean,
    default: false
  },
  showActions: {
    type: Boolean,
    default: true
  },
  showRetry: {
    type: Boolean,
    default: true
  },
  showRefresh: {
    type: Boolean,
    default: false
  },
  showContact: {
    type: Boolean,
    default: false
  },
  showHome: {
    type: Boolean,
    default: false
  },
  showBackground: {
    type: Boolean,
    default: true
  },
  animated: {
    type: Boolean,
    default: true
  },
  retrying: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['retry', 'refresh', 'contact'])

const detailsExpanded = ref(false)

const errorConfig = {
  general: {
    icon: 'fas fa-exclamation-triangle',
    title: 'Something went wrong',
    message: 'An unexpected error occurred. Please try again.',
    color: 'warning'
  },
  network: {
    icon: 'fas fa-wifi',
    title: 'Connection problem',
    message: 'Unable to connect to the server. Please check your internet connection.',
    color: 'danger'
  },
  permission: {
    icon: 'fas fa-lock',
    title: 'Access denied',
    message: 'You don\'t have permission to access this resource.',
    color: 'warning'
  },
  'not-found': {
    icon: 'fas fa-search',
    title: 'Not found',
    message: 'The page or resource you\'re looking for doesn\'t exist.',
    color: 'info'
  },
  server: {
    icon: 'fas fa-server',
    title: 'Server error',
    message: 'The server encountered an error. Our team has been notified.',
    color: 'danger'
  },
  validation: {
    icon: 'fas fa-exclamation-circle',
    title: 'Validation error',
    message: 'Please check your input and try again.',
    color: 'warning'
  },
  timeout: {
    icon: 'fas fa-clock',
    title: 'Request timeout',
    message: 'The request took too long to complete. Please try again.',
    color: 'warning'
  }
}

const currentConfig = computed(() => errorConfig[props.type] || errorConfig.general)

const iconClass = computed(() => currentConfig.value.icon)
const iconType = computed(() => currentConfig.value.color)
const defaultTitle = computed(() => currentConfig.value.title)
const defaultMessage = computed(() => currentConfig.value.message)

const toggleDetails = () => {
  detailsExpanded.value = !detailsExpanded.value
}
</script>

<style scoped>
.error-state {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 200px;
  padding: 2rem;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 20px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

/* Size variants */
.error-state.sm {
  min-height: 150px;
  padding: 1rem;
}

.error-state.md {
  min-height: 200px;
  padding: 2rem;
}

.error-state.lg {
  min-height: 300px;
  padding: 3rem;
}

.error-state.xl {
  min-height: 400px;
  padding: 4rem;
}

/* Variant styles */
.error-state.minimal {
  background: transparent;
  border: none;
  box-shadow: none;
  backdrop-filter: none;
}

.error-state.fullscreen {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 9999;
  border-radius: 0;
  min-height: 100vh;
}

.error-container {
  position: relative;
  z-index: 2;
  text-align: center;
  max-width: 500px;
  width: 100%;
}

/* Error Icon */
.error-icon {
  margin-bottom: 2rem;
  position: relative;
  display: inline-block;
}

.icon-wrapper {
  position: relative;
  width: 80px;
  height: 80px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto;
}

.error-icon.warning .icon-wrapper {
  background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
  color: white;
  box-shadow: 0 8px 24px rgba(245, 158, 11, 0.3);
}

.error-icon.danger .icon-wrapper {
  background: linear-gradient(135deg, #f87171 0%, #ef4444 100%);
  color: white;
  box-shadow: 0 8px 24px rgba(239, 68, 68, 0.3);
}

.error-icon.info .icon-wrapper {
  background: linear-gradient(135deg, #60a5fa 0%, #3b82f6 100%);
  color: white;
  box-shadow: 0 8px 24px rgba(59, 130, 246, 0.3);
}

.icon-wrapper i {
  font-size: 2rem;
  position: relative;
  z-index: 2;
}

.icon-pulse {
  position: absolute;
  top: -10px;
  left: -10px;
  right: -10px;
  bottom: -10px;
  border-radius: 50%;
  background: inherit;
  opacity: 0.3;
  animation: pulse-ring 2s ease-out infinite;
}

/* Error Content */
.error-content {
  margin-bottom: 2rem;
}

.error-title {
  font-size: 1.75rem;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 1rem;
  line-height: 1.3;
}

.error-message {
  font-size: 1.125rem;
  color: #6b7280;
  margin-bottom: 1.5rem;
  line-height: 1.6;
}

.error-code {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background: rgba(107, 114, 128, 0.1);
  border: 1px solid rgba(107, 114, 128, 0.2);
  border-radius: 8px;
  font-family: 'Courier New', monospace;
  font-size: 0.875rem;
  margin-bottom: 1.5rem;
}

.code-label {
  color: #6b7280;
  font-weight: 500;
}

.code-value {
  color: #1f2937;
  font-weight: 700;
}

/* Error Details */
.error-details {
  margin-top: 1rem;
}

.details-toggle {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background: rgba(107, 114, 128, 0.1);
  border: 1px solid rgba(107, 114, 128, 0.2);
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s ease;
  font-size: 0.875rem;
  color: #6b7280;
  margin: 0 auto;
}

.details-toggle:hover {
  background: rgba(107, 114, 128, 0.15);
}

.details-toggle i {
  transition: transform 0.3s ease;
}

.details-toggle.expanded i {
  transform: rotate(180deg);
}

.details-content {
  margin-top: 1rem;
  padding: 1rem;
  background: rgba(0, 0, 0, 0.05);
  border: 1px solid rgba(0, 0, 0, 0.1);
  border-radius: 8px;
  text-align: left;
  max-height: 200px;
  overflow-y: auto;
}

.details-content pre {
  font-family: 'Courier New', monospace;
  font-size: 0.75rem;
  color: #374151;
  white-space: pre-wrap;
  word-wrap: break-word;
  margin: 0;
}

/* Error Actions */
.error-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  justify-content: center;
  align-items: center;
}

.action-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 12px;
  font-weight: 600;
  text-decoration: none;
  cursor: pointer;
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
}

.action-btn::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
  transition: left 0.5s;
}

.action-btn:hover::before {
  left: 100%;
}

.action-btn.primary {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  box-shadow: 0 4px 16px rgba(102, 126, 234, 0.3);
}

.action-btn.primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(102, 126, 234, 0.4);
}

.action-btn.primary:disabled {
  opacity: 0.7;
  cursor: not-allowed;
  transform: none;
}

.action-btn.secondary {
  background: rgba(107, 114, 128, 0.1);
  border: 1px solid rgba(107, 114, 128, 0.3);
  color: #374151;
}

.action-btn.secondary:hover {
  background: rgba(107, 114, 128, 0.15);
  transform: translateY(-1px);
}

.action-btn.tertiary {
  background: transparent;
  color: #6b7280;
  border: 1px solid rgba(107, 114, 128, 0.2);
}

.action-btn.tertiary:hover {
  color: #374151;
  border-color: rgba(107, 114, 128, 0.4);
}

/* Background Pattern */
.error-background {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 1;
  opacity: 0.05;
}

.bg-pattern {
  width: 100%;
  height: 100%;
  background-image: 
    radial-gradient(circle at 20% 50%, #667eea 2px, transparent 2px),
    radial-gradient(circle at 80% 20%, #764ba2 2px, transparent 2px),
    radial-gradient(circle at 40% 80%, #667eea 2px, transparent 2px);
  background-size: 40px 40px, 60px 60px, 50px 50px;
  animation: float 20s ease-in-out infinite;
}

/* Animations */
@keyframes pulse-ring {
  0% {
    transform: scale(1);
    opacity: 0.3;
  }
  100% {
    transform: scale(1.5);
    opacity: 0;
  }
}

@keyframes float {
  0%, 100% {
    transform: translateY(0px) rotate(0deg);
  }
  50% {
    transform: translateY(-10px) rotate(180deg);
  }
}

/* Responsive Design */
@media (max-width: 768px) {
  .error-state {
    padding: 1rem;
  }
  
  .error-title {
    font-size: 1.5rem;
  }
  
  .error-message {
    font-size: 1rem;
  }
  
  .icon-wrapper {
    width: 60px;
    height: 60px;
  }
  
  .icon-wrapper i {
    font-size: 1.5rem;
  }
  
  .error-actions {
    flex-direction: column;
    align-items: stretch;
  }
  
  .action-btn {
    justify-content: center;
  }
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
  .error-state {
    background: rgba(30, 41, 59, 0.95);
    border-color: rgba(71, 85, 105, 0.3);
  }
  
  .error-title {
    color: #f1f5f9;
  }
  
  .error-message {
    color: #cbd5e1;
  }
  
  .error-code {
    background: rgba(71, 85, 105, 0.3);
    border-color: rgba(71, 85, 105, 0.5);
  }
  
  .code-label {
    color: #cbd5e1;
  }
  
  .code-value {
    color: #f1f5f9;
  }
}
</style>