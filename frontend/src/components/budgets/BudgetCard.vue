<template>
  <div class="budget-card">
    <!-- Card Header -->
    <div class="card-header">
      <div class="header-left">
        <h3 class="budget-name">{{ budget.name }}</h3>
        <div class="budget-category">
          <i class="fas fa-tag"></i>
          <span>{{ budget.category?.name || 'Uncategorized' }}</span>
        </div>
      </div>
      <div class="header-right">
        <div class="dropdown">
          <button @click="showDropdown = !showDropdown" class="dropdown-trigger">
            <i class="fas fa-ellipsis-v"></i>
          </button>
          <div v-if="showDropdown" class="dropdown-menu">
            <button @click="$emit('edit', budget)" class="dropdown-item">
              <i class="fas fa-edit"></i>
              Edit
            </button>
            <button @click="$emit('duplicate', budget)" class="dropdown-item">
              <i class="fas fa-copy"></i>
              Duplicate
            </button>
            <button @click="$emit('delete', budget)" class="dropdown-item text-red-600">
              <i class="fas fa-trash"></i>
              Delete
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Budget Amount -->
    <div class="budget-amount">
      <div class="amount-display">
        <span class="spent-amount">${{ formatAmount(budget.spent_amount) }}</span>
        <span class="amount-separator">/</span>
        <span class="total-amount">${{ formatAmount(budget.amount) }}</span>
      </div>
      <div class="remaining-amount">
        <span :class="remainingAmountClass">${{ formatAmount(budget.remaining_amount) }} remaining</span>
      </div>
    </div>

    <!-- Progress Bar -->
    <div class="progress-section">
      <div class="progress-info">
        <span class="progress-text">{{ progressPercentage }}% used</span>
        <span class="period-info">{{ displayPeriodInfo }}</span>
      </div>
      <div class="progress-bar">
        <div
          class="progress-fill"
          :class="progressBarClass"
          :style="{ width: `${Math.min(progressPercentage, 100)}%` }"
        ></div>
      </div>
    </div>

    <!-- Status and Dates -->
    <div class="card-footer">
      <div class="status-section">
        <span :class="statusBadgeClass">
          <i :class="statusIconClass"></i>
          {{ budget.is_active ? 'Active' : 'Inactive' }}
        </span>
      </div>
      <div class="date-info">
        <div class="date-item">
          <i class="fas fa-calendar-alt"></i>
          <span>{{ formatDate(budget.start_date) }} - {{ formatDate(budget.end_date) }}</span>
        </div>
      </div>
    </div>

    <!-- Alert Indicators -->
    <div v-if="hasAlerts" class="alert-indicators">
      <div v-if="isCritical" class="alert-indicator alert-critical" title="Budget exceeded!">
        <i class="fas fa-exclamation-triangle"></i>
        Critical
      </div>
      <div v-else-if="isWarning" class="alert-indicator alert-warning" title="Budget warning">
        <i class="fas fa-exclamation-circle"></i>
        Warning
      </div>
    </div>
  </div>
</template>

<script>
  import { ref, computed, onMounted, onUnmounted } from 'vue'
  import { formatCurrency, formatDate as formatDateUtil } from '@/utils/formatters'

  export default {
    name: 'BudgetCard',
    props: {
      budget: {
        type: Object,
        required: true
      }
    },
    emits: ['edit', 'delete', 'duplicate'],
    setup(props) {
      const showDropdown = ref(false)

      // Computed properties
      const progressPercentage = computed(() => {
        if (props.budget.amount === 0) return 0
        return Math.round((props.budget.spent_amount / props.budget.amount) * 100)
      })

      const remainingAmountClass = computed(() => {
        const remaining = props.budget.remaining_amount
        if (remaining < 0) return 'text-red-600 font-semibold'
        if (remaining < props.budget.amount * 0.2) return 'text-orange-600 font-medium'
        return 'text-green-600'
      })

      const progressBarClass = computed(() => {
        const percentage = progressPercentage.value
        if (percentage >= 100) return 'bg-red-500'
        if (percentage >= 80) return 'bg-orange-500'
        if (percentage >= 60) return 'bg-yellow-500'
        return 'bg-green-500'
      })

      const statusBadgeClass = computed(() => {
        return props.budget.is_active ? 'status-badge status-active' : 'status-badge status-inactive'
      })

      const statusIconClass = computed(() => {
        return props.budget.is_active ? 'fas fa-check-circle' : 'fas fa-pause-circle'
      })

      const isCritical = computed(() => {
        return props.budget.spent_amount > props.budget.amount
      })

      const isWarning = computed(() => {
        const percentage = progressPercentage.value
        return percentage >= 80 && percentage < 100
      })

      const hasAlerts = computed(() => {
        return isCritical.value || isWarning.value
      })

      const displayPeriodInfo = computed(() => {
        // Use the normalized period_label if available, otherwise format the period type
        if (props.budget.period_label) {
          return props.budget.period_label
        }

        // Fallback to formatting the period type
        return formatPeriod(props.budget.period)
      })

      // Methods
      const formatAmount = amount => {
        return formatCurrency(amount)
      }

      const formatDate = date => {
        return formatDateUtil(date, 'MMM DD, YYYY')
      }

      const formatPeriod = period => {
        // Handle period object or string
        if (typeof period === 'object' && period !== null) {
          return period.label || period.type || 'Unknown'
        }

        const periods = {
          weekly: 'Weekly',
          monthly: 'Monthly',
          quarterly: 'Quarterly',
          yearly: 'Yearly'
        }
        return periods[period] || period
      }

      // Close dropdown when clicking outside
      const handleClickOutside = event => {
        if (!event.target.closest('.dropdown')) {
          showDropdown.value = false
        }
      }

      onMounted(() => {
        document.addEventListener('click', handleClickOutside)
      })

      onUnmounted(() => {
        document.removeEventListener('click', handleClickOutside)
      })

      return {
        showDropdown,
        progressPercentage,
        remainingAmountClass,
        progressBarClass,
        statusBadgeClass,
        statusIconClass,
        isCritical,
        isWarning,
        hasAlerts,
        displayPeriodInfo,
        formatAmount,
        formatDate,
        formatPeriod
      }
    }
  }
</script>

<style scoped>
  .budget-card {
    @apply bg-white rounded-lg shadow-sm border hover:shadow-md transition-shadow duration-200 p-4 relative;
  }

  .card-header {
    @apply flex justify-between items-start mb-4;
  }

  .header-left {
    @apply flex-1;
  }

  .budget-name {
    @apply text-lg font-semibold text-gray-800 mb-1;
  }

  .budget-category {
    @apply flex items-center text-sm text-gray-600;
  }

  .budget-category i {
    @apply mr-1;
  }

  .header-right {
    @apply relative;
  }

  .dropdown {
    @apply relative;
  }

  .dropdown-trigger {
    @apply p-2 text-gray-400 hover:text-gray-600 rounded-md hover:bg-gray-50 transition-colors;
  }

  .dropdown-menu {
    @apply absolute right-0 mt-1 w-32 bg-white border border-gray-200 rounded-md shadow-lg z-10;
  }

  .dropdown-item {
    @apply w-full px-3 py-2 text-left text-sm hover:bg-gray-50 flex items-center;
  }

  .dropdown-item i {
    @apply mr-2 text-xs;
  }

  .budget-amount {
    @apply mb-4;
  }

  .amount-display {
    @apply flex items-baseline mb-1;
  }

  .spent-amount {
    @apply text-2xl font-bold text-gray-800;
  }

  .amount-separator {
    @apply mx-2 text-gray-400;
  }

  .total-amount {
    @apply text-lg text-gray-600;
  }

  .remaining-amount {
    @apply text-sm;
  }

  .progress-section {
    @apply mb-4;
  }

  .progress-info {
    @apply flex justify-between items-center mb-2;
  }

  .progress-text {
    @apply text-sm font-medium text-gray-700;
  }

  .period-info {
    @apply text-xs text-gray-500;
  }

  .progress-bar {
    @apply w-full bg-gray-200 rounded-full h-2;
  }

  .progress-fill {
    @apply h-2 rounded-full transition-all duration-300;
  }

  .card-footer {
    @apply flex justify-between items-center;
  }

  .status-section {
    @apply flex items-center;
  }

  .status-badge {
    @apply inline-flex items-center px-2 py-1 rounded-full text-xs font-medium;
  }

  .status-active {
    @apply bg-green-100 text-green-800;
  }

  .status-inactive {
    @apply bg-gray-100 text-gray-800;
  }

  .status-badge i {
    @apply mr-1;
  }

  .date-info {
    @apply text-xs text-gray-500;
  }

  .date-item {
    @apply flex items-center;
  }

  .date-item i {
    @apply mr-1;
  }

  .alert-indicators {
    @apply absolute top-2 right-2;
  }

  .alert-indicator {
    @apply inline-flex items-center px-2 py-1 rounded-full text-xs font-medium;
  }

  .alert-critical {
    @apply bg-red-100 text-red-800;
  }

  .alert-warning {
    @apply bg-orange-100 text-orange-800;
  }

  .alert-indicator i {
    @apply mr-1;
  }
</style>
