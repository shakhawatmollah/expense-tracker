<template>
  <div class="empty-state" :class="[variant, size]">
    <div class="empty-container">
      <!-- Illustration/Icon -->
      <div class="empty-illustration" :class="illustrationType">
        <div class="illustration-wrapper">
          <!-- Custom SVG Illustration -->
          <div v-if="illustration === 'custom'" class="custom-illustration">
            <slot name="illustration"></slot>
          </div>
          
          <!-- Predefined Illustrations -->
          <div v-else class="predefined-illustration">
            <i :class="iconClass"></i>
            <div class="illustration-effects" v-if="animated">
              <div class="effect-1"></div>
              <div class="effect-2"></div>
              <div class="effect-3"></div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Content -->
      <div class="empty-content">
        <h3 class="empty-title">{{ title || defaultTitle }}</h3>
        <p class="empty-message">{{ message || defaultMessage }}</p>
        
        <!-- Additional Info -->
        <div class="empty-info" v-if="info">
          <p class="info-text">{{ info }}</p>
        </div>
        
        <!-- Steps/Tips -->
        <div class="empty-steps" v-if="steps && steps.length > 0">
          <h4 class="steps-title">{{ stepsTitle || 'Get started:' }}</h4>
          <ul class="steps-list">
            <li v-for="(step, index) in steps" :key="index" class="step-item">
              <span class="step-number">{{ index + 1 }}</span>
              <span class="step-text">{{ step }}</span>
            </li>
          </ul>
        </div>
      </div>
      
      <!-- Actions -->
      <div class="empty-actions" v-if="showActions">
        <button 
          v-if="showPrimary"
          @click="$emit('primary-action')" 
          class="action-btn primary"
          :disabled="loading"
        >
          <i :class="primaryIcon" v-if="primaryIcon"></i>
          {{ loading ? loadingText : (primaryText || defaultPrimaryText) }}
        </button>
        
        <button 
          v-if="showSecondary"
          @click="$emit('secondary-action')" 
          class="action-btn secondary"
        >
          <i :class="secondaryIcon" v-if="secondaryIcon"></i>
          {{ secondaryText || defaultSecondaryText }}
        </button>
        
        <router-link 
          v-if="showLink && linkTo"
          :to="linkTo" 
          class="action-btn link"
        >
          <i :class="linkIcon" v-if="linkIcon"></i>
          {{ linkText }}
        </router-link>
        
        <!-- Custom Actions -->
        <slot name="actions"></slot>
      </div>
    </div>
    
    <!-- Background Elements -->
    <div class="empty-background" v-if="showBackground">
      <div class="bg-elements">
        <div class="bg-element element-1"></div>
        <div class="bg-element element-2"></div>
        <div class="bg-element element-3"></div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  type: {
    type: String,
    default: 'general',
    validator: (value) => ['general', 'expenses', 'categories', 'budgets', 'analytics', 'search', 'filter', 'upload'].includes(value)
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
  illustration: {
    type: String,
    default: 'icon',
    validator: (value) => ['icon', 'svg', 'custom'].includes(value)
  },
  title: {
    type: String,
    default: ''
  },
  message: {
    type: String,
    default: ''
  },
  info: {
    type: String,
    default: ''
  },
  steps: {
    type: Array,
    default: () => []
  },
  stepsTitle: {
    type: String,
    default: ''
  },
  showActions: {
    type: Boolean,
    default: true
  },
  showPrimary: {
    type: Boolean,
    default: true
  },
  showSecondary: {
    type: Boolean,
    default: false
  },
  showLink: {
    type: Boolean,
    default: false
  },
  primaryText: {
    type: String,
    default: ''
  },
  secondaryText: {
    type: String,
    default: ''
  },
  linkText: {
    type: String,
    default: ''
  },
  linkTo: {
    type: String,
    default: ''
  },
  primaryIcon: {
    type: String,
    default: ''
  },
  secondaryIcon: {
    type: String,
    default: ''
  },
  linkIcon: {
    type: String,
    default: ''
  },
  showBackground: {
    type: Boolean,
    default: true
  },
  animated: {
    type: Boolean,
    default: true
  },
  loading: {
    type: Boolean,
    default: false
  },
  loadingText: {
    type: String,
    default: 'Loading...'
  }
})

const emit = defineEmits(['primary-action', 'secondary-action'])

const emptyConfig = {
  general: {
    icon: 'fas fa-inbox',
    title: 'Nothing here yet',
    message: 'Get started by adding your first item.',
    primaryText: 'Add Item',
    secondaryText: 'Learn More',
    color: 'primary'
  },
  expenses: {
    icon: 'fas fa-receipt',
    title: 'No expenses found',
    message: 'Start tracking your expenses to see them here.',
    primaryText: 'Add Expense',
    secondaryText: 'Import Data',
    color: 'expense'
  },
  categories: {
    icon: 'fas fa-tags',
    title: 'No categories yet',
    message: 'Create categories to organize your expenses better.',
    primaryText: 'Create Category',
    secondaryText: 'Use Templates',
    color: 'category'
  },
  budgets: {
    icon: 'fas fa-piggy-bank',
    title: 'No budgets set',
    message: 'Set up budgets to keep track of your spending goals.',
    primaryText: 'Create Budget',
    secondaryText: 'Learn About Budgets',
    color: 'budget'
  },
  analytics: {
    icon: 'fas fa-chart-line',
    title: 'No data to analyze',
    message: 'Add some expenses to see analytics and insights.',
    primaryText: 'Add Expenses',
    secondaryText: 'View Tutorial',
    color: 'analytics'
  },
  search: {
    icon: 'fas fa-search',
    title: 'No results found',
    message: 'Try adjusting your search criteria or filters.',
    primaryText: 'Clear Filters',
    secondaryText: 'Reset Search',
    color: 'search'
  },
  filter: {
    icon: 'fas fa-filter',
    title: 'No matches found',
    message: 'Try adjusting your filters to see more results.',
    primaryText: 'Clear Filters',
    secondaryText: 'Reset All',
    color: 'filter'
  },
  upload: {
    icon: 'fas fa-cloud-upload-alt',
    title: 'Upload your data',
    message: 'Drag and drop files here or click to browse.',
    primaryText: 'Browse Files',
    secondaryText: 'Download Template',
    color: 'upload'
  }
}

const currentConfig = computed(() => emptyConfig[props.type] || emptyConfig.general)

const iconClass = computed(() => currentConfig.value.icon)
const illustrationType = computed(() => currentConfig.value.color)
const defaultTitle = computed(() => currentConfig.value.title)
const defaultMessage = computed(() => currentConfig.value.message)
const defaultPrimaryText = computed(() => currentConfig.value.primaryText)
const defaultSecondaryText = computed(() => currentConfig.value.secondaryText)
</script>

<style scoped>
.empty-state {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 300px;
  padding: 3rem 2rem;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 24px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

/* Size variants */
.empty-state.sm {
  min-height: 200px;
  padding: 2rem 1rem;
}

.empty-state.md {
  min-height: 300px;
  padding: 3rem 2rem;
}

.empty-state.lg {
  min-height: 400px;
  padding: 4rem 3rem;
}

.empty-state.xl {
  min-height: 500px;
  padding: 5rem 4rem;
}

/* Variant styles */
.empty-state.minimal {
  background: transparent;
  border: none;
  box-shadow: none;
  backdrop-filter: none;
}

.empty-state.fullscreen {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 9999;
  border-radius: 0;
  min-height: 100vh;
}

.empty-container {
  position: relative;
  z-index: 2;
  text-align: center;
  max-width: 600px;
  width: 100%;
}

/* Illustration */
.empty-illustration {
  margin-bottom: 3rem;
  position: relative;
  display: inline-block;
}

.illustration-wrapper {
  position: relative;
  width: 120px;
  height: 120px;
  margin: 0 auto;
}

.predefined-illustration {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  overflow: hidden;
}

.predefined-illustration i {
  font-size: 3rem;
  color: white;
  position: relative;
  z-index: 2;
}

/* Illustration types */
.empty-illustration.primary .predefined-illustration {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  box-shadow: 0 12px 40px rgba(102, 126, 234, 0.3);
}

.empty-illustration.expense .predefined-illustration {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  box-shadow: 0 12px 40px rgba(16, 185, 129, 0.3);
}

.empty-illustration.category .predefined-illustration {
  background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
  box-shadow: 0 12px 40px rgba(139, 92, 246, 0.3);
}

.empty-illustration.budget .predefined-illustration {
  background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
  box-shadow: 0 12px 40px rgba(245, 158, 11, 0.3);
}

.empty-illustration.analytics .predefined-illustration {
  background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
  box-shadow: 0 12px 40px rgba(59, 130, 246, 0.3);
}

.empty-illustration.search .predefined-illustration {
  background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
  box-shadow: 0 12px 40px rgba(107, 114, 128, 0.3);
}

.empty-illustration.filter .predefined-illustration {
  background: linear-gradient(135deg, #ec4899 0%, #be185d 100%);
  box-shadow: 0 12px 40px rgba(236, 72, 153, 0.3);
}

.empty-illustration.upload .predefined-illustration {
  background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
  box-shadow: 0 12px 40px rgba(6, 182, 212, 0.3);
}

/* Illustration Effects */
.illustration-effects {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  pointer-events: none;
}

.illustration-effects > div {
  position: absolute;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.1);
  animation: ripple 3s ease-out infinite;
}

.effect-1 {
  top: -20%;
  left: -20%;
  right: -20%;
  bottom: -20%;
  animation-delay: 0s;
}

.effect-2 {
  top: -40%;
  left: -40%;
  right: -40%;
  bottom: -40%;
  animation-delay: 1s;
}

.effect-3 {
  top: -60%;
  left: -60%;
  right: -60%;
  bottom: -60%;
  animation-delay: 2s;
}

/* Content */
.empty-content {
  margin-bottom: 3rem;
}

.empty-title {
  font-size: 2rem;
  font-weight: 800;
  color: #1f2937;
  margin-bottom: 1rem;
  line-height: 1.3;
  background: linear-gradient(135deg, #1f2937 0%, #374151 100%);
  background-clip: text;
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.empty-message {
  font-size: 1.125rem;
  color: #6b7280;
  margin-bottom: 2rem;
  line-height: 1.6;
  max-width: 400px;
  margin-left: auto;
  margin-right: auto;
}

.empty-info {
  margin-bottom: 2rem;
}

.info-text {
  font-size: 0.875rem;
  color: #9ca3af;
  background: rgba(156, 163, 175, 0.1);
  border: 1px solid rgba(156, 163, 175, 0.2);
  border-radius: 8px;
  padding: 0.75rem 1rem;
  display: inline-block;
}

/* Steps */
.empty-steps {
  margin-bottom: 2rem;
  text-align: left;
  max-width: 400px;
  margin-left: auto;
  margin-right: auto;
}

.steps-title {
  font-size: 1rem;
  font-weight: 600;
  color: #374151;
  margin-bottom: 1rem;
  text-align: center;
}

.steps-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.step-item {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  margin-bottom: 0.75rem;
  padding: 0.75rem;
  background: rgba(255, 255, 255, 0.5);
  border: 1px solid rgba(255, 255, 255, 0.3);
  border-radius: 12px;
  transition: all 0.3s ease;
}

.step-item:hover {
  background: rgba(255, 255, 255, 0.7);
  transform: translateX(4px);
}

.step-number {
  flex-shrink: 0;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  font-size: 0.75rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  justify-content: center;
}

.step-text {
  color: #374151;
  font-size: 0.875rem;
  line-height: 1.5;
}

/* Actions */
.empty-actions {
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
  padding: 0.875rem 2rem;
  border: none;
  border-radius: 16px;
  font-weight: 600;
  text-decoration: none;
  cursor: pointer;
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
  font-size: 0.875rem;
}

.action-btn::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
  transition: left 0.6s;
}

.action-btn:hover::before {
  left: 100%;
}

.action-btn.primary {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  box-shadow: 0 8px 24px rgba(102, 126, 234, 0.3);
}

.action-btn.primary:hover {
  transform: translateY(-3px);
  box-shadow: 0 12px 32px rgba(102, 126, 234, 0.4);
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
  transform: translateY(-2px);
}

.action-btn.link {
  background: transparent;
  color: #667eea;
  border: 1px solid rgba(102, 126, 234, 0.3);
}

.action-btn.link:hover {
  background: rgba(102, 126, 234, 0.1);
  border-color: rgba(102, 126, 234, 0.5);
  transform: translateY(-1px);
}

/* Background Elements */
.empty-background {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 1;
  opacity: 0.05;
}

.bg-elements {
  width: 100%;
  height: 100%;
  position: relative;
}

.bg-element {
  position: absolute;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea, #764ba2);
}

.element-1 {
  width: 200px;
  height: 200px;
  top: 20%;
  left: 10%;
  animation: float 15s ease-in-out infinite;
}

.element-2 {
  width: 150px;
  height: 150px;
  top: 60%;
  right: 15%;
  animation: float 20s ease-in-out infinite reverse;
}

.element-3 {
  width: 100px;
  height: 100px;
  bottom: 30%;
  left: 60%;
  animation: float 25s ease-in-out infinite;
}

/* Animations */
@keyframes ripple {
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
  33% {
    transform: translateY(-20px) rotate(120deg);
  }
  66% {
    transform: translateY(10px) rotate(240deg);
  }
}

/* Responsive Design */
@media (max-width: 768px) {
  .empty-state {
    padding: 2rem 1rem;
  }
  
  .empty-title {
    font-size: 1.5rem;
  }
  
  .empty-message {
    font-size: 1rem;
  }
  
  .illustration-wrapper {
    width: 80px;
    height: 80px;
  }
  
  .predefined-illustration i {
    font-size: 2rem;
  }
  
  .empty-actions {
    flex-direction: column;
    align-items: stretch;
  }
  
  .action-btn {
    justify-content: center;
    padding: 1rem 2rem;
  }
  
  .empty-steps {
    margin-left: 0;
    margin-right: 0;
  }
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
  .empty-state {
    background: rgba(30, 41, 59, 0.95);
    border-color: rgba(71, 85, 105, 0.3);
  }
  
  .empty-title {
    color: #f1f5f9;
    background: linear-gradient(135deg, #f1f5f9 0%, #cbd5e1 100%);
    background-clip: text;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
  }
  
  .empty-message {
    color: #cbd5e1;
  }
  
  .step-item {
    background: rgba(71, 85, 105, 0.3);
    border-color: rgba(71, 85, 105, 0.5);
  }
  
  .step-text {
    color: #cbd5e1;
  }
}
</style>