<template>
  <div class="summary-cards-grid">
    <!-- Total Expenses Card -->
    <div class="summary-card total-expenses" @click="showDetails('total')">
      <div class="card-background"></div>
      <div class="card-content">
        <div class="card-header">
          <div class="card-icon">
            <i class="fas fa-credit-card"></i>
          </div>
          <div class="card-badge">Total</div>
        </div>
        
        <div class="card-body">
          <div class="card-title">Total Expenses</div>
          <div class="card-value">
            <AnimatedCounter 
              v-if="!loading" 
              :value="parseFloat(totalExpenses)" 
              prefix="$" 
              :duration="2000"
              :decimals="2"
            />
            <div v-else class="loading-skeleton"></div>
          </div>
          <div class="card-subtitle">All time spending</div>
        </div>

        <div class="card-mini-chart">
          <div class="mini-bars">
            <div v-for="i in 8" :key="i" class="mini-bar" :style="{ height: Math.random() * 100 + '%' }"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- This Month Card -->
    <div class="summary-card this-month" @click="showDetails('month')">
      <div class="card-background"></div>
      <div class="card-content">
        <div class="card-header">
          <div class="card-icon">
            <i class="fas fa-calendar-alt"></i>
          </div>
          <div class="card-badge">Monthly</div>
        </div>
        
        <div class="card-body">
          <div class="card-title">This Month</div>
          <div class="card-value">
            <AnimatedCounter 
              v-if="!loading" 
              :value="parseFloat(thisMonthExpenses)" 
              prefix="$" 
              :duration="1800"
              :decimals="2"
            />
            <div v-else class="loading-skeleton"></div>
          </div>
          
          <div v-if="!loading && monthlyChange !== 0" class="trend-indicator">
            <div :class="['trend-badge', trendClass]">
              <i :class="trendIcon"></i>
              <span>{{ Math.abs(monthlyChange).toFixed(1) }}%</span>
            </div>
            <span class="trend-text">vs last month</span>
          </div>
          <div v-else class="card-subtitle">Current month spending</div>
        </div>

        <div class="card-progress">
          <div class="progress-bar">
            <div class="progress-fill" :style="{ width: monthProgressPercentage + '%' }"></div>
          </div>
          <div class="progress-text">{{ dayOfMonth }}/{{ daysInMonth }} days</div>
        </div>
      </div>
    </div>

    <!-- Categories Card -->
    <div class="summary-card categories" @click="showDetails('categories')">
      <div class="card-background"></div>
      <div class="card-content">
        <div class="card-header">
          <div class="card-icon">
            <i class="fas fa-tags"></i>
          </div>
          <div class="card-badge">Active</div>
        </div>
        
        <div class="card-body">
          <div class="card-title">Categories</div>
          <div class="card-value">
            <AnimatedCounter 
              v-if="!loading" 
              :value="totalCategories" 
              :duration="1500"
            />
            <div v-else class="loading-skeleton"></div>
          </div>
          <div class="card-subtitle">Expense categories</div>
        </div>

        <div class="card-category-preview">
          <div class="category-dots">
            <div 
              v-for="(category, index) in topCategories" 
              :key="category.id"
              class="category-dot"
              :style="{ 
                backgroundColor: getCategoryColor(index),
                animationDelay: index * 0.1 + 's'
              }"
            ></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Average Monthly Card -->
    <div class="summary-card average" @click="showDetails('average')">
      <div class="card-background"></div>
      <div class="card-content">
        <div class="card-header">
          <div class="card-icon">
            <i class="fas fa-chart-line"></i>
          </div>
          <div class="card-badge">Avg</div>
        </div>
        
        <div class="card-body">
          <div class="card-title">Avg/Month</div>
          <div class="card-value">
            <AnimatedCounter 
              v-if="!loading" 
              :value="parseFloat(averageMonthly)" 
              prefix="$" 
              :duration="2200"
              :decimals="2"
            />
            <div v-else class="loading-skeleton"></div>
          </div>
          <div class="card-subtitle">Monthly average</div>
        </div>

        <div class="card-trend-line">
          <svg viewBox="0 0 100 40" class="trend-svg">
            <path 
              d="M5,35 Q25,10 45,25 T85,15" 
              fill="none" 
              stroke="url(#gradient)" 
              stroke-width="2"
              class="trend-path"
            />
            <defs>
              <linearGradient id="gradient" x1="0%" y1="0%" x2="100%" y2="0%">
                <stop offset="0%" style="stop-color:#667eea"/>
                <stop offset="100%" style="stop-color:#764ba2"/>
              </linearGradient>
            </defs>
          </svg>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useExpensesStore } from '@/stores/expenses'
import { useCategoriesStore } from '@/stores/categories'
import { format, startOfMonth, endOfMonth, subMonths, getDaysInMonth, getDate } from 'date-fns'
import AnimatedCounter from '@/components/common/AnimatedCounter.vue'

// Emits
const emit = defineEmits(['show-details'])

const expensesStore = useExpensesStore()
const categoriesStore = useCategoriesStore()

const loading = ref(false)

// Category colors for visual variety
const categoryColors = [
  '#667eea', '#764ba2', '#f093fb', '#f5576c',
  '#4facfe', '#00f2fe', '#43e97b', '#38f9d7',
  '#ffecd2', '#fcb69f', '#a8edea', '#fed6e3'
]

const getCategoryColor = (index) => categoryColors[index % categoryColors.length]

// Show details function
const showDetails = (type) => {
  emit('show-details', type)
}

const totalExpenses = computed(() => {
  return expensesStore.expenses
    .reduce((total, expense) => total + parseFloat(expense.amount || 0), 0)
    .toFixed(2)
})

const thisMonthExpenses = computed(() => {
  const currentMonthStart = format(startOfMonth(new Date()), 'yyyy-MM-dd')
  const currentMonthEnd = format(endOfMonth(new Date()), 'yyyy-MM-dd')
  
  return expensesStore.expenses
    .filter(expense => expense.date >= currentMonthStart && expense.date <= currentMonthEnd)
    .reduce((total, expense) => total + parseFloat(expense.amount || 0), 0)
    .toFixed(2)
})

const lastMonthExpenses = computed(() => {
  const lastMonth = subMonths(new Date(), 1)
  const lastMonthStart = format(startOfMonth(lastMonth), 'yyyy-MM-dd')
  const lastMonthEnd = format(endOfMonth(lastMonth), 'yyyy-MM-dd')
  
  return expensesStore.expenses
    .filter(expense => expense.date >= lastMonthStart && expense.date <= lastMonthEnd)
    .reduce((total, expense) => total + parseFloat(expense.amount || 0), 0)
})

const monthlyChange = computed(() => {
  const current = parseFloat(thisMonthExpenses.value)
  const last = lastMonthExpenses.value
  
  if (last === 0) return 0
  return ((current - last) / last) * 100
})

const totalCategories = computed(() => {
  return categoriesStore.categories.length
})

const averageMonthly = computed(() => {
  if (expensesStore.expenses.length === 0) return '0.00'
  
  // Get unique months from expenses
  const months = new Set(
    expensesStore.expenses.map(expense => expense.date.substring(0, 7))
  )
  
  if (months.size === 0) return '0.00'
  
  const total = parseFloat(totalExpenses.value)
  return (total / months.size).toFixed(2)
})

// Enhanced computed properties
const trendClass = computed(() => ({
  'trend-up': monthlyChange.value > 0,
  'trend-down': monthlyChange.value < 0,
  'trend-neutral': monthlyChange.value === 0
}))

const trendIcon = computed(() => {
  if (monthlyChange.value > 0) return 'fas fa-arrow-up'
  if (monthlyChange.value < 0) return 'fas fa-arrow-down'
  return 'fas fa-minus'
})

// Month progress
const dayOfMonth = computed(() => getDate(new Date()))
const daysInMonth = computed(() => getDaysInMonth(new Date()))
const monthProgressPercentage = computed(() => (dayOfMonth.value / daysInMonth.value) * 100)

// Top categories for preview
const topCategories = computed(() => {
  return categoriesStore.categories.slice(0, 5)
})

onMounted(async () => {
  loading.value = true
  try {
    await Promise.all([
      expensesStore.fetchExpenses(),
      categoriesStore.fetchCategories()
    ])
  } catch (error) {
    console.error('Failed to load summary data:', error)
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
/* Summary Cards Grid */
.summary-cards-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

/* Modern Summary Card */
.summary-card {
  position: relative;
  background: white;
  border-radius: 24px;
  overflow: hidden;
  cursor: pointer;
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  backdrop-filter: blur(10px);
  min-height: 200px;
}

.summary-card:hover {
  transform: translateY(-8px) scale(1.02);
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
}

/* Card Backgrounds */
.summary-card.total-expenses {
  background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.05));
  box-shadow: 0 8px 32px rgba(102, 126, 234, 0.15);
}

.summary-card.this-month {
  background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(5, 150, 105, 0.05));
  box-shadow: 0 8px 32px rgba(16, 185, 129, 0.15);
}

.summary-card.categories {
  background: linear-gradient(135deg, rgba(245, 158, 11, 0.1), rgba(217, 119, 6, 0.05));
  box-shadow: 0 8px 32px rgba(245, 158, 11, 0.15);
}

.summary-card.average {
  background: linear-gradient(135deg, rgba(139, 92, 246, 0.1), rgba(124, 58, 237, 0.05));
  box-shadow: 0 8px 32px rgba(139, 92, 246, 0.15);
}

/* Card Background Pattern */
.card-background {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  opacity: 0.03;
  background-image: radial-gradient(circle at 20% 80%, rgba(120, 119, 198, 0.3) 0%, transparent 50%),
                    radial-gradient(circle at 80% 20%, rgba(255, 119, 198, 0.3) 0%, transparent 50%);
}

/* Card Content */
.card-content {
  position: relative;
  padding: 1.5rem;
  height: 100%;
  display: flex;
  flex-direction: column;
}

/* Card Header */
.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.card-icon {
  width: 48px;
  height: 48px;
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
  color: white;
  position: relative;
  overflow: hidden;
}

.total-expenses .card-icon {
  background: linear-gradient(135deg, #667eea, #764ba2);
  box-shadow: 0 8px 16px rgba(102, 126, 234, 0.3);
}

.this-month .card-icon {
  background: linear-gradient(135deg, #10b981, #059669);
  box-shadow: 0 8px 16px rgba(16, 185, 129, 0.3);
}

.categories .card-icon {
  background: linear-gradient(135deg, #f59e0b, #d97706);
  box-shadow: 0 8px 16px rgba(245, 158, 11, 0.3);
}

.average .card-icon {
  background: linear-gradient(135deg, #8b5cf6, #7c3aed);
  box-shadow: 0 8px 16px rgba(139, 92, 246, 0.3);
}

.card-badge {
  background: rgba(255, 255, 255, 0.9);
  color: #374151;
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 600;
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
}

/* Card Body */
.card-body {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.card-title {
  font-size: 0.875rem;
  font-weight: 600;
  color: #6b7280;
  margin-bottom: 0.5rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.card-value {
  font-size: 2rem;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 0.5rem;
  line-height: 1.2;
}

.card-subtitle {
  font-size: 0.875rem;
  color: #9ca3af;
  margin-bottom: 1rem;
}

/* Loading Skeleton */
.loading-skeleton {
  height: 2rem;
  background: linear-gradient(90deg, #f3f4f6 25%, #e5e7eb 50%, #f3f4f6 75%);
  background-size: 200% 100%;
  border-radius: 4px;
  animation: loading-shimmer 2s infinite;
}

@keyframes loading-shimmer {
  0% { background-position: -200% 0; }
  100% { background-position: 200% 0; }
}

/* Trend Indicator */
.trend-indicator {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 1rem;
}

.trend-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 600;
}

.trend-badge.trend-up {
  background: rgba(239, 68, 68, 0.1);
  color: #ef4444;
}

.trend-badge.trend-down {
  background: rgba(16, 185, 129, 0.1);
  color: #10b981;
}

.trend-badge.trend-neutral {
  background: rgba(107, 114, 128, 0.1);
  color: #6b7280;
}

.trend-text {
  font-size: 0.75rem;
  color: #9ca3af;
}

/* Month Progress */
.card-progress {
  margin-top: auto;
}

.progress-bar {
  width: 100%;
  height: 4px;
  background: rgba(0, 0, 0, 0.1);
  border-radius: 2px;
  overflow: hidden;
  margin-bottom: 0.5rem;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #10b981, #059669);
  border-radius: 2px;
  transition: width 1s ease-out;
}

.progress-text {
  font-size: 0.75rem;
  color: #6b7280;
  text-align: center;
}

/* Mini Charts */
.card-mini-chart {
  margin-top: auto;
  height: 40px;
  display: flex;
  align-items: end;
}

.mini-bars {
  display: flex;
  gap: 2px;
  height: 30px;
  width: 100%;
  align-items: end;
}

.mini-bar {
  flex: 1;
  background: linear-gradient(180deg, rgba(102, 126, 234, 0.6), rgba(102, 126, 234, 0.2));
  border-radius: 1px;
  animation: mini-bar-grow 1.5s ease-out;
  animation-fill-mode: both;
}

@keyframes mini-bar-grow {
  from { height: 0; opacity: 0; }
  to { opacity: 1; }
}

/* Category Preview */
.card-category-preview {
  margin-top: auto;
}

.category-dots {
  display: flex;
  gap: 6px;
  flex-wrap: wrap;
}

.category-dot {
  width: 12px;
  height: 12px;
  border-radius: 50%;
  animation: dot-appear 0.8s ease-out;
  animation-fill-mode: both;
}

@keyframes dot-appear {
  from {
    transform: scale(0);
    opacity: 0;
  }
  to {
    transform: scale(1);
    opacity: 1;
  }
}

/* Trend Line */
.card-trend-line {
  margin-top: auto;
  height: 40px;
}

.trend-svg {
  width: 100%;
  height: 100%;
}

.trend-path {
  stroke-dasharray: 200;
  stroke-dashoffset: 200;
  animation: draw-line 2s ease-out forwards;
}

@keyframes draw-line {
  from {
    stroke-dashoffset: 200;
  }
  to {
    stroke-dashoffset: 0;
  }
}

/* Responsive Design */
@media (max-width: 768px) {
  .summary-cards-grid {
    grid-template-columns: 1fr;
    gap: 1rem;
  }
  
  .summary-card {
    min-height: 160px;
  }
  
  .card-content {
    padding: 1.25rem;
  }
  
  .card-value {
    font-size: 1.75rem;
  }
  
  .card-icon {
    width: 40px;
    height: 40px;
    font-size: 1rem;
  }
}
</style>