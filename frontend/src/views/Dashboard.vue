<template>
  <!-- Mobile Layout -->
  <div v-if="isMobile" class="dashboard-mobile min-h-screen bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50">
    <!-- Mobile Navigation -->
    <MobileNavigation
      :is-sidebar-open="isSidebarOpen"
      @toggle-sidebar="toggleSidebar"
      @navigate="handleMobileNavigation"
      @quick-action="handleQuickAction"
    />

    <!-- Main Mobile Content -->
    <div class="mobile-content" :class="{ 'content-shifted': isSidebarOpen }">
      <!-- Mobile Dashboard Header -->
      <div class="mobile-header p-4">
        <div class="mobile-greeting">
          <h2 class="text-2xl font-bold text-gray-800">Good {{ timeOfDay }}, {{ user?.name || 'User' }}! ??</h2>
          <p class="mobile-balance text-lg text-gray-600">
            Balance:
            <span class="amount positive text-green-600 font-semibold">
              ${{ formatCurrency(dashboardStore.totalBalance) }}
            </span>
          </p>
        </div>
      </div>

      <!-- Mobile Quick Stats -->
      <div class="mobile-stats-grid p-4 grid grid-cols-2 gap-4">
        <div
          v-for="stat in quickStats"
          :key="stat.label"
          class="stat-card bg-white rounded-2xl p-4 shadow-lg border border-gray-100"
          @click="handleStatTap(stat)"
        >
          <div class="stat-content flex items-center">
            <div class="stat-icon mr-3" :style="{ color: stat.color }">
              <i :class="stat.icon" class="text-2xl"></i>
            </div>
            <div class="stat-info">
              <div class="stat-value font-bold text-lg text-gray-800">{{ stat.value }}</div>
              <div class="stat-label text-sm text-gray-500">{{ stat.label }}</div>
              <div class="stat-change text-xs" :class="stat.changeClass">
                {{ stat.change }}
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Mobile Sections -->
      <div class="mobile-sections px-4 pb-20">
        <!-- Recent Expenses -->
        <div class="mobile-section mb-6">
          <div class="section-header flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Recent Expenses</h3>
            <button @click="$router.push('/expenses')" class="view-all-btn text-blue-600 text-sm font-medium">
              View All
            </button>
          </div>

          <div class="mobile-expenses space-y-3">
            <div
              v-for="expense in recentExpenses"
              :key="expense.id"
              class="expense-item bg-white rounded-xl p-4 shadow-sm border border-gray-100"
            >
              <div class="flex items-center">
                <div
                  class="expense-icon w-10 h-10 rounded-full flex items-center justify-center mr-3"
                  :style="{
                    backgroundColor: getCategoryColor(expense.category) + '20',
                    color: getCategoryColor(expense.category)
                  }"
                >
                  <i :class="getCategoryIcon(expense.category)"></i>
                </div>
                <div class="expense-details flex-1">
                  <div class="expense-description font-medium text-gray-800">{{ expense.description }}</div>
                  <div class="expense-category text-sm text-gray-500">{{ expense.category?.name }}</div>
                  <div class="expense-date text-xs text-gray-400">{{ formatDate(expense.date) }}</div>
                </div>
                <div class="expense-amount font-semibold text-red-600">-${{ formatCurrency(expense.amount) }}</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Budget Overview -->
        <div class="mobile-section mb-6" v-if="budgetStore.currentBudgets.length">
          <div class="section-header flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Budget Overview</h3>
            <button @click="$router.push('/budgets')" class="view-all-btn text-blue-600 text-sm font-medium">
              Manage
            </button>
          </div>

          <div class="mobile-budgets space-y-3">
            <div
              v-for="budget in budgetStore.currentBudgets.slice(0, 3)"
              :key="budget.id"
              class="budget-item bg-white rounded-xl p-4 shadow-sm border border-gray-100"
              @click="$router.push(`/budgets/${budget.id}`)"
            >
              <div class="budget-header flex justify-between items-center mb-3">
                <span class="budget-category font-medium text-gray-800">{{ budget.category.name }}</span>
                <span class="budget-percentage font-semibold" :class="getBudgetStatusClass(budget.percentage)">
                  {{ Math.round(budget.percentage) }}%
                </span>
              </div>
              <div class="budget-progress mb-2">
                <div class="progress-bar w-full bg-gray-200 rounded-full h-2">
                  <div
                    class="progress-fill h-2 rounded-full transition-all duration-300"
                    :class="getBudgetProgressColor(budget.percentage)"
                    :style="{ width: Math.min(budget.percentage, 100) + '%' }"
                  ></div>
                </div>
              </div>
              <div class="budget-amounts flex justify-between text-sm">
                <span class="spent text-gray-600">${{ formatCurrency(budget.spent) }}</span>
                <span class="limit text-gray-500">of ${{ formatCurrency(budget.limit) }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="mobile-actions">
          <div class="grid grid-cols-2 gap-4">
            <button
              v-for="action in mobileQuickActions"
              :key="action.id"
              class="action-card bg-white rounded-xl p-4 shadow-sm border border-gray-100 flex items-center"
              @click="handleQuickAction(action.id)"
            >
              <div
                class="action-icon w-10 h-10 rounded-full flex items-center justify-center mr-3"
                :style="{ backgroundColor: action.color + '20', color: action.color }"
              >
                <i :class="action.icon"></i>
              </div>
              <div class="action-info">
                <div class="action-title font-medium text-gray-800 text-sm">{{ action.title }}</div>
                <div class="action-subtitle text-xs text-gray-500">{{ action.subtitle }}</div>
              </div>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Desktop Layout -->
  <div v-else class="min-h-screen bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50">
    <AppHeader />
    <div class="flex">
      <AppSidebar class="hidden md:block" />
      <main class="flex-1 py-6 px-4">
        <div class="max-w-7xl mx-auto">
          <!-- Modern Header Section -->
          <div class="mb-12 relative overflow-hidden">
            <!-- Background Elements -->
            <div
              class="absolute inset-0 bg-gradient-to-r from-blue-600/10 via-purple-600/10 to-pink-600/10 rounded-3xl"
            ></div>
            <div
              class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-br from-blue-400/20 to-purple-400/20 rounded-full filter blur-3xl animate-pulse"
            ></div>
            <div
              class="absolute bottom-0 left-0 w-48 h-48 bg-gradient-to-tr from-purple-400/20 to-pink-400/20 rounded-full filter blur-2xl animation-delay-1000 animate-pulse"
            ></div>

            <!-- Header Content -->
            <div class="relative z-10 backdrop-blur-sm bg-white/30 border border-white/20 rounded-3xl p-8">
              <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                <!-- Welcome Section -->
                <div class="flex-1">
                  <div class="flex items-center gap-4 mb-4">
                    <div
                      class="w-16 h-16 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl flex items-center justify-center shadow-xl shadow-blue-500/25"
                    >
                      <i class="fas fa-chart-pie text-white text-2xl"></i>
                    </div>
                    <div>
                      <h1
                        class="text-4xl font-bold bg-gradient-to-r from-gray-900 via-blue-800 to-purple-800 bg-clip-text text-transparent leading-tight"
                      >
                        Welcome Back!
                      </h1>
                      <div class="flex items-center gap-2 mt-2">
                        <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
                        <span class="text-sm text-gray-600 font-medium">{{ getCurrentTime() }}</span>
                      </div>
                    </div>
                  </div>

                  <p class="text-gray-700 text-lg leading-relaxed max-w-2xl">
                    {{ getPersonalizedGreeting() }}
                  </p>

                  <!-- Quick Stats -->
                  <div class="flex flex-wrap gap-4 mt-6">
                    <div
                      class="flex items-center gap-2 px-4 py-2 bg-white/50 backdrop-blur-sm rounded-full border border-white/30"
                    >
                      <i class="fas fa-calendar-day text-blue-500"></i>
                      <span class="text-sm font-medium text-gray-700">{{ getCurrentPeriod() }}</span>
                    </div>
                    <div
                      class="flex items-center gap-2 px-4 py-2 bg-white/50 backdrop-blur-sm rounded-full border border-white/30"
                    >
                      <i class="fas fa-chart-line text-green-500"></i>
                      <span class="text-sm font-medium text-gray-700">{{ getTotalExpenses() }}</span>
                    </div>
                    <div
                      class="flex items-center gap-2 px-4 py-2 bg-white/50 backdrop-blur-sm rounded-full border border-white/30"
                    >
                      <i class="fas fa-target text-purple-500"></i>
                      <span class="text-sm font-medium text-gray-700">{{ getBudgetStatus() }}</span>
                    </div>
                  </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col gap-3 lg:flex-row lg:gap-4">
                  <!-- Real-time Data Indicator -->
                  <RealTimeData
                    :refresh-function="refreshAllDashboardData"
                    :refresh-interval="60000"
                    :auto-refresh-enabled="true"
                    @refresh-start="handleRefreshStart"
                    @refresh-complete="handleRefreshComplete"
                    @refresh-error="handleRefreshError"
                    @settings-changed="handleSyncSettingsChanged"
                  />

                  <!-- Quick Add Expense -->
                  <button @click="showQuickAddExpense" class="modern-action-btn primary" title="Add new expense">
                    <i class="fas fa-plus"></i>
                    <span>Add Expense</span>
                  </button>

                  <!-- View Analytics -->
                  <router-link to="/analytics" class="modern-action-btn secondary" title="View detailed analytics">
                    <i class="fas fa-chart-line"></i>
                    <span>Analytics</span>
                  </router-link>

                  <!-- Settings -->
                  <button @click="showSettings" class="modern-action-btn outline" title="Dashboard settings">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Dashboard Content -->
          <template v-if="!isLoading">
            <TransitionWrapper type="fade" :duration="400">
              <!-- Summary Cards -->
              <div class="dashboard-section" data-section="summary">
                <SummaryCards />
              </div>
            </TransitionWrapper>

            <!-- Financial Health Section -->
            <TransitionWrapper type="slide-up" :duration="400" :delay="100">
              <div class="dashboard-section mt-6" data-section="health">
                <div class="section-header">
                  <h2 class="section-title">
                    <i class="fas fa-heartbeat"></i>
                    Financial Health
                  </h2>
                  <p class="section-subtitle">Monitor your overall financial wellbeing</p>
                </div>
                <FinancialHealthCard period="monthly" :auto-refresh="true" />
              </div>
            </TransitionWrapper>

            <!-- Budget Section -->
            <TransitionWrapper type="slide-left" :duration="400" :delay="200">
              <div class="dashboard-section mt-8" data-section="budgets">
                <div class="section-header">
                  <h2 class="section-title">
                    <i class="fas fa-chart-pie"></i>
                    Budget Overview
                  </h2>
                  <p class="section-subtitle">Track your spending against budgets</p>
                </div>
                <div class="modern-grid budget-grid">
                  <TransitionWrapper type="scale" :duration="300">
                    <div class="grid-item">
                      <BudgetSummaryWidget />
                    </div>
                  </TransitionWrapper>
                  <TransitionWrapper type="scale" :duration="300" :delay="100">
                    <div class="grid-item">
                      <BudgetAlertsWidget />
                    </div>
                  </TransitionWrapper>
                </div>
              </div>
            </TransitionWrapper>

            <!-- Analytics Section -->
            <TransitionWrapper type="slide-right" :duration="400" :delay="300">
              <div class="dashboard-section mt-8" data-section="analytics">
                <div class="section-header">
                  <h2 class="section-title">
                    <i class="fas fa-chart-line"></i>
                    Spending Analytics
                  </h2>
                  <p class="section-subtitle">Visualize your spending patterns and trends</p>
                </div>
                <div class="modern-grid analytics-grid">
                  <TransitionWrapper type="flip" :duration="400">
                    <div class="grid-item">
                      <BudgetProgressWidget />
                    </div>
                  </TransitionWrapper>
                  <TransitionWrapper type="flip" :duration="400" :delay="100">
                    <div class="grid-item">
                      <ExpenseChart />
                    </div>
                  </TransitionWrapper>
                </div>
              </div>
            </TransitionWrapper>

            <!-- Recent Activity Section -->
            <TransitionWrapper type="slide-up" :duration="400" :delay="400">
              <div class="dashboard-section mt-8" data-section="activity">
                <div class="section-header">
                  <h2 class="section-title">
                    <i class="fas fa-clock"></i>
                    Recent Activity
                  </h2>
                  <p class="section-subtitle">Your latest expenses and category breakdown</p>
                </div>
                <div class="modern-grid activity-grid">
                  <TransitionWrapper type="bounce" :duration="400">
                    <div class="grid-item featured">
                      <CategoryBreakdown />
                    </div>
                  </TransitionWrapper>
                  <TransitionWrapper type="bounce" :duration="400" :delay="100">
                    <div class="grid-item">
                      <RecentExpenses />
                    </div>
                  </TransitionWrapper>
                </div>
              </div>
            </TransitionWrapper>
          </template>

          <!-- Loading State -->
          <template v-else>
            <TransitionWrapper type="fade" :duration="300">
              <div class="loading-state">
                <div class="loading-grid">
                  <!-- Summary Cards Skeleton -->
                  <div class="skeleton-section">
                    <LoadingSkeleton type="card" variant="shimmer" size="lg" :lines="2" :show-footer="true" />
                  </div>

                  <!-- Financial Health Skeleton -->
                  <div class="skeleton-section">
                    <LoadingSkeleton type="chart" variant="wave" size="xl" />
                  </div>

                  <!-- Budget Widgets Skeleton -->
                  <div class="skeleton-section grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <LoadingSkeleton type="metric" variant="pulse" size="lg" />
                    <LoadingSkeleton type="list" variant="shimmer" size="md" :items="3" />
                  </div>

                  <!-- Analytics Widgets Skeleton -->
                  <div class="skeleton-section grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <LoadingSkeleton type="chart" variant="wave" size="lg" />
                    <LoadingSkeleton type="chart" variant="pulse" size="lg" />
                  </div>

                  <!-- Recent Activity Skeleton -->
                  <div class="skeleton-section grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <LoadingSkeleton type="chart" variant="shimmer" size="md" />
                    <LoadingSkeleton type="list" variant="wave" size="md" :items="5" />
                  </div>
                </div>

                <!-- Loading Spinner Overlay -->
                <div class="loading-overlay">
                  <LoadingSpinner size="lg" variant="primary" :show-icon="true" text="Loading your dashboard..." />
                </div>
              </div>
            </TransitionWrapper>
          </template>
        </div>
      </main>
    </div>

    <!-- Quick Actions FAB -->
    <QuickActionsFAB @action="handleQuickAction" />

    <!-- Notification System -->
    <NotificationSystem ref="notificationSystem" @notification-action="handleNotificationAction" />

    <!-- Quick Add Expense Modal -->
    <QuickAddExpense
      :is-visible="showQuickAddModal"
      @close="showQuickAddModal = false"
      @expense-added="handleExpenseAdded"
    />

    <!-- Export Modal -->
    <ExportModal v-if="showExportModal" @close="showExportModal = false" @success="handleExportSuccess" />
  </div>
</template>

<script setup>
  import { onMounted, onUnmounted, computed, ref } from 'vue'
  import { useRouter } from 'vue-router'
  import AppHeader from '@/components/layout/AppHeader.vue'
  import AppSidebar from '@/components/layout/AppSidebar.vue'
  import SummaryCards from '@/components/dashboard/SummaryCards.vue'
  import ExpenseChart from '@/components/dashboard/ExpenseChart.vue'
  import CategoryBreakdown from '@/components/dashboard/CategoryBreakdown.vue'
  import RecentExpenses from '@/components/dashboard/RecentExpenses.vue'
  import BudgetSummaryWidget from '@/components/budgets/BudgetSummaryWidget.vue'
  import BudgetAlertsWidget from '@/components/budgets/BudgetAlertsWidget.vue'
  import BudgetProgressWidget from '@/components/budgets/BudgetProgressWidget.vue'
  import FinancialHealthCard from '@/components/analytics/FinancialHealthCard.vue'
  import LoadingSkeleton from '@/components/common/LoadingSkeleton.vue'
  import ErrorState from '@/components/common/ErrorState.vue'
  import EmptyState from '@/components/common/EmptyState.vue'
  import TransitionWrapper from '@/components/common/TransitionWrapper.vue'
  import LoadingSpinner from '@/components/common/LoadingSpinner.vue'
  import QuickActionsFAB from '@/components/common/QuickActionsFAB.vue'
  import NotificationSystem from '@/components/common/NotificationSystem.vue'
  import RealTimeData from '@/components/common/RealTimeData.vue'
  import MobileNavigation from '@/components/mobile/MobileNavigation.vue'
  import QuickAddExpense from '@/components/modals/QuickAddExpense.vue'
  import ExportModal from '@/components/common/ExportModal.vue'
  import { useExpensesStore } from '@/stores/expenses'
  import { useCategoriesStore } from '@/stores/categories'
  import { useDashboardStore } from '@/stores/dashboard'
  import { useBudgetStore } from '@/stores/budget'
  import { useAnalyticsStore } from '@/stores/analytics'
  import debug from '@/utils/debug'

  const router = useRouter()
  const expensesStore = useExpensesStore()
  const categoriesStore = useCategoriesStore()
  const dashboardStore = useDashboardStore()
  const budgetStore = useBudgetStore()
  const analyticsStore = useAnalyticsStore()

  // Loading state
  const isLoading = ref(true)

  // Modal states
  const showQuickAddModal = ref(false)
  const showExportModal = ref(false)

  // Mobile detection
  const isMobile = ref(false)
  const isSidebarOpen = ref(false)

  const detectMobile = () => {
    isMobile.value = window.innerWidth <= 768
  }

  // Mobile navigation methods
  const toggleSidebar = () => {
    isSidebarOpen.value = !isSidebarOpen.value
  }

  const handleMobileNavigation = route => {
    isSidebarOpen.value = false
    router.push(route)
  }

  // Mobile computed properties
  const timeOfDay = computed(() => {
    const hour = new Date().getHours()
    if (hour < 12) return 'morning'
    if (hour < 17) return 'afternoon'
    return 'evening'
  })

  const quickStats = computed(() => [
    {
      label: 'Today',
      value: '$' + formatCurrency(dashboardStore.todaysExpenses || 0),
      icon: 'fas fa-calendar-day',
      color: '#3B82F6',
      change: '+12%',
      changeClass: 'text-green-500'
    },
    {
      label: 'This Month',
      value: '$' + formatCurrency(dashboardStore.monthlyExpenses || 0),
      icon: 'fas fa-chart-line',
      color: '#10B981',
      change: '-5%',
      changeClass: 'text-red-500'
    },
    {
      label: 'Budgets',
      value: budgetStore.currentBudgets.length + ' active',
      icon: 'fas fa-bullseye',
      color: '#8B5CF6',
      change: '2 alerts',
      changeClass: 'text-orange-500'
    },
    {
      label: 'Categories',
      value: categoriesStore.categories.length + ' total',
      icon: 'fas fa-tags',
      color: '#F59E0B',
      change: 'View all',
      changeClass: 'text-blue-500'
    }
  ])

  const recentExpenses = computed(() => {
    return expensesStore.expenses.slice(0, 5)
  })

  const mobileQuickActions = computed(() => [
    {
      id: 'add-expense',
      title: 'Add Expense',
      subtitle: 'Quick entry',
      icon: 'fas fa-plus',
      color: '#3B82F6'
    },
    {
      id: 'scan-receipt',
      title: 'Scan Receipt',
      subtitle: 'Photo capture',
      icon: 'fas fa-camera',
      color: '#10B981'
    },
    {
      id: 'view-analytics',
      title: 'Analytics',
      subtitle: 'Insights',
      icon: 'fas fa-chart-pie',
      color: '#8B5CF6'
    },
    {
      id: 'export-data',
      title: 'Export',
      subtitle: 'Download data',
      icon: 'fas fa-download',
      color: '#F59E0B'
    }
  ])

  // Mobile helper methods
  const handleStatTap = stat => {
    if (stat.label === 'Today' || stat.label === 'This Month') {
      router.push('/expenses')
    } else if (stat.label === 'Budgets') {
      router.push('/budgets')
    } else if (stat.label === 'Categories') {
      router.push('/categories')
    }
  }

  const handleQuickAction = actionId => {
    switch (actionId) {
      case 'add-expense':
        showQuickAddExpense()
        break
      case 'add-category':
        showQuickAddCategory()
        break
      case 'set-budget':
        showQuickSetBudget()
        break
      case 'scan-receipt':
        // TODO: Implement receipt scanning
        break
      case 'view-analytics':
        router.push('/analytics')
        break
      case 'export-data':
        showExportModal.value = true
        break
    }
  }

  const getCategoryIcon = category => {
    const iconMap = {
      'Food & Dining': 'fas fa-utensils',
      Transportation: 'fas fa-car',
      Shopping: 'fas fa-shopping-bag',
      Entertainment: 'fas fa-film',
      'Bills & Utilities': 'fas fa-file-invoice-dollar',
      Healthcare: 'fas fa-heartbeat',
      Education: 'fas fa-graduation-cap',
      Travel: 'fas fa-plane',
      'Personal Care': 'fas fa-spa',
      Other: 'fas fa-ellipsis-h'
    }
    return iconMap[category?.name] || 'fas fa-receipt'
  }

  const getCategoryColor = category => {
    const colorMap = {
      'Food & Dining': '#F59E0B',
      Transportation: '#3B82F6',
      Shopping: '#EF4444',
      Entertainment: '#8B5CF6',
      'Bills & Utilities': '#06B6D4',
      Healthcare: '#EC4899',
      Education: '#10B981',
      Travel: '#F97316',
      'Personal Care': '#84CC16',
      Other: '#6B7280'
    }
    return colorMap[category?.name] || '#6B7280'
  }

  const getBudgetStatusClass = percentage => {
    if (percentage >= 90) return 'text-red-600'
    if (percentage >= 75) return 'text-orange-500'
    return 'text-green-600'
  }

  const getBudgetProgressColor = percentage => {
    if (percentage >= 90) return 'bg-red-500'
    if (percentage >= 75) return 'bg-orange-500'
    return 'bg-green-500'
  }

  const formatCurrency = amount => {
    // Ensure amount is a number and handle edge cases
    const numericAmount = parseFloat(amount) || 0
    return numericAmount.toFixed(2)
  }

  const formatDate = date => {
    return new Date(date).toLocaleDateString('en-US', {
      month: 'short',
      day: 'numeric'
    })
  }

  // Navigation handler for mobile
  const handleNavigationChange = () => {
    console.log('Mobile navigation changed')
    // Handle mobile navigation changes
  }

  // Computed properties for dynamic content
  const getCurrentTime = () => {
    const now = new Date()
    const options = {
      weekday: 'long',
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    }
    return now.toLocaleDateString('en-US', options)
  }

  const getPersonalizedGreeting = () => {
    const hour = new Date().getHours()
    const totalExpenses = expensesStore.expenses?.length || 0

    let timeGreeting = ''
    if (hour < 12) timeGreeting = 'Good morning'
    else if (hour < 18) timeGreeting = 'Good afternoon'
    else timeGreeting = 'Good evening'

    if (totalExpenses === 0) {
      return `${timeGreeting}! Ready to start tracking your expenses? Let's begin your financial journey.`
    } else if (totalExpenses < 10) {
      return `${timeGreeting}! You're building great financial habits. Here's your latest expense overview.`
    } else {
      return `${timeGreeting}! Your expense tracking is going strong. Here's your comprehensive financial overview.`
    }
  }

  const getCurrentPeriod = () => {
    const now = new Date()
    return now.toLocaleDateString('en-US', { month: 'long', year: 'numeric' })
  }

  const getTotalExpenses = () => {
    const expenses = expensesStore.expenses || []
    console.log('Expenses for total calculation:', expenses.slice(0, 3))

    const total = expenses.reduce((sum, expense) => {
      // Ensure expense.amount is parsed as a number to avoid string concatenation
      const amount = parseFloat(expense.amount) || 0
      return sum + amount
    }, 0)

    console.log('Calculated total:', total)
    return `$${total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`
  }

  const getBudgetStatus = () => {
    // Mock budget status - you can integrate with actual budget store
    return 'On Track'
  }

  // Action handlers
  const showQuickAddExpense = () => {
    console.log('Opening quick add expense modal')
    showQuickAddModal.value = true
  }

  const handleExpenseAdded = expenseData => {
    console.log('Expense added:', expenseData)

    // Refresh dashboard data to show the new expense
    refreshAllDashboardData()

    // Show success notification if not already shown by modal
    if (window.notify) {
      window.notify.success(
      'Dashboard Updated',
      'Your new expense has been added and the dashboard has been refreshed',
      { duration: 15000 }
    )
    }
  }

  const showSettings = () => {
    console.log('Show dashboard settings')
    // Implement settings panel
  }

  // Quick action implementations
  const showQuickAddCategory = () => {
    router.push({ path: '/categories', query: { action: 'create' } })
  }

  const showQuickSetBudget = () => {
    router.push({ path: '/budgets', query: { action: 'create' } })
  }

  const navigateToAnalytics = () => {
    router.push('/analytics')
  }

  const handleDataExport = () => {
    console.log('Export dashboard data')
    showExportModal.value = true
  }

  const handleExportSuccess = message => {
    if (window.notify) {
      window.notify.success(message || 'Export completed successfully', 'Export Complete', { duration: 15000 })
    }
  }

  const editItem = item => {
    console.log('Edit item:', item)
    // Navigate to edit page or show edit modal
  }

  const viewItemDetails = item => {
    console.log('View item details:', item)
    // Navigate to details page or show details modal
  }

  const navigateToItem = item => {
    console.log('Navigate to item:', item)
    // Handle general navigation based on item type
  }

  // Real-time data refresh methods
  const isRefreshing = ref(false) // Prevent recursive calls

  const refreshAllDashboardData = async () => {
    // Prevent recursive calls
    if (isRefreshing.value) {
      console.log('Refresh already in progress, skipping...')
      return
    }

    console.log('Refreshing all dashboard data...')
    isRefreshing.value = true

    try {
      await Promise.all([
        expensesStore.fetchAllExpenses(),
        categoriesStore.fetchCategories(),
        dashboardStore.fetchDashboardOverview(),
        dashboardStore.fetchTrends(6),
        budgetStore.fetchCurrentBudgets(),
        budgetStore.fetchBudgetSummary(),
        budgetStore.fetchBudgetAlerts(),
        analyticsStore.loadFinancialHealth('monthly')
      ])

      console.log('Dashboard data refreshed successfully')
    } catch (error) {
      console.error('Failed to refresh dashboard data:', error)
      throw error
    } finally {
      isRefreshing.value = false
    }
  }

  const handleRefreshStart = ({ isManual }) => {
    console.log('Refresh started:', { isManual })
    if (isManual) {
      isLoading.value = true
    }
  }

  const handleRefreshComplete = ({ isManual, timestamp }) => {
    console.log('Refresh completed:', { isManual, timestamp })
    isLoading.value = false

    // Show success notification for manual refresh
    if (isManual && window.notify) {
      window.notify.success('Dashboard Updated', 'Your data has been refreshed successfully', { duration: 15000 })
    }
  }

  const handleRefreshError = ({ error, isManual }) => {
    console.error('Refresh error:', error)
    isLoading.value = false

    // Show error notification
    if (window.notify) {
      window.notify.error(
        'Refresh Failed',
        isManual ? 'Failed to refresh dashboard manually' : 'Automatic refresh failed'
      )
    }
  }

  const handleSyncSettingsChanged = settings => {
    console.log('Sync settings changed:', settings)
    // Save sync settings to user preferences
  }

  // Notification action handler
  const handleNotificationAction = action => {
    console.log('Notification action:', action)

    switch (action.id || action) {
      case 'undo':
        handleUndoAction()
        break
      case 'view-budget':
        navigateToBudget()
        break
      default:
        console.log('Unknown notification action:', action)
    }
  }

  const handleUndoAction = () => {
    console.log('Undo last action')
    // Implement undo functionality
  }

  const navigateToBudget = () => {
    console.log('Navigate to budget view')
    // Navigate to budget page
  }

  onMounted(async () => {
    try {
      isLoading.value = true

      // Detect mobile and listen for resize events
      detectMobile()
      window.addEventListener('resize', detectMobile)

      await Promise.all([
        expensesStore.fetchAllExpenses(), // Use non-paginated for dashboard
        categoriesStore.fetchCategories(),
        dashboardStore.fetchDashboardOverview(),
        dashboardStore.fetchTrends(6),
        budgetStore.fetchCurrentBudgets(),
        budgetStore.fetchBudgetSummary(),
        budgetStore.fetchBudgetAlerts(),
        analyticsStore.loadFinancialHealth('monthly') // Load financial health for dashboard
      ])

      // Demo notifications to showcase real-time features
      setTimeout(() => {
        if (window.notify) {
          window.notify.success('Welcome Back!', 'Dashboard loaded successfully with the latest data', {
          duration: 15000
        })

          // Demo budget alert after a delay
          setTimeout(() => {
            if (window.notify && typeof window.notify.showBudgetAlert === 'function') {
              window.notify.showBudgetAlert('Food & Dining', 85)
            }
          }, 3000)

          // Demo expense notification
          setTimeout(() => {
            if (window.notify && typeof window.notify.showExpenseAdded === 'function') {
              window.notify.showExpenseAdded(12.5, 'Coffee')
            }
          }, 6000)
        }
      }, 1000)
    } catch (error) {
      console.error('Failed to load dashboard data:', error)

      // Show error notification
      if (window.notify) {
        window.notify.error('Loading Failed', 'Failed to load dashboard data. Please try refreshing.')
      }
    } finally {
      isLoading.value = false
    }
  })

  // Cleanup on unmount
  onUnmounted(() => {
    window.removeEventListener('resize', detectMobile)
  })
</script>

<style scoped>
  /* Modern Dashboard Styles */

  /* Header animations */
  @keyframes fadeInUp {
    from {
      opacity: 0;
      transform: translateY(30px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  @keyframes slideInRight {
    from {
      opacity: 0;
      transform: translateX(30px);
    }
    to {
      opacity: 1;
      transform: translateX(0);
    }
  }

  .mb-12 {
    animation: fadeInUp 0.8s ease-out;
  }

  /* Modern Action Buttons */
  .modern-action-btn {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.875rem 1.5rem;
    border-radius: 16px;
    font-weight: 600;
    font-size: 0.875rem;
    text-decoration: none;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
    border: none;
    cursor: pointer;
    min-width: 140px;
    justify-content: center;
  }

  .modern-action-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
  }

  .modern-action-btn:hover::before {
    left: 100%;
  }

  .modern-action-btn.primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    box-shadow: 0 8px 24px rgba(102, 126, 234, 0.3);
  }

  .modern-action-btn.primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 32px rgba(102, 126, 234, 0.4);
  }

  .modern-action-btn.secondary {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    color: white;
    box-shadow: 0 8px 24px rgba(79, 172, 254, 0.3);
  }

  .modern-action-btn.secondary:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 32px rgba(79, 172, 254, 0.4);
  }

  .modern-action-btn.outline {
    background: rgba(255, 255, 255, 0.8);
    backdrop-filter: blur(10px);
    color: #6b7280;
    border: 1px solid rgba(255, 255, 255, 0.3);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
  }

  .modern-action-btn.outline:hover {
    background: rgba(255, 255, 255, 0.95);
    color: #374151;
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
  }

  /* Background gradient animation */
  .min-h-screen {
    animation: gradientShift 10s ease-in-out infinite;
  }

  @keyframes gradientShift {
    0%,
    100% {
      background: linear-gradient(135deg, #f0f9ff 0%, #e0e7ff 50%, #faf5ff 100%);
    }
    50% {
      background: linear-gradient(135deg, #eff6ff 0%, #ddd6fe 50%, #fdf4ff 100%);
    }
  }

  /* Floating animations */
  @keyframes float {
    0%,
    100% {
      transform: translateY(0px);
    }
    50% {
      transform: translateY(-10px);
    }
  }

  .w-16.h-16 {
    animation: float 3s ease-in-out infinite;
    animation-delay: 0.5s;
  }

  /* Glassmorphism effects enhancement */
  .backdrop-blur-sm {
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
  }

  /* Responsive adjustments */
  @media (max-width: 1024px) {
    .mb-12 .flex {
      flex-direction: column;
    }

    .flex-1 {
      order: 1;
    }

    .flex.flex-col.gap-3 {
      order: 2;
      flex-direction: row;
      justify-content: center;
      flex-wrap: wrap;
    }
  }

  @media (max-width: 768px) {
    .mb-12 {
      margin-bottom: 2rem;
    }

    .p-8 {
      padding: 1.5rem;
    }

    .text-4xl {
      font-size: 2rem;
      line-height: 1.2;
    }

    .w-16.h-16 {
      width: 3.5rem;
      height: 3.5rem;
    }

    .modern-action-btn {
      padding: 0.75rem 1.25rem;
      min-width: 120px;
      font-size: 0.8rem;
    }

    .flex.flex-wrap.gap-4 {
      gap: 0.5rem;
    }

    .px-4.py-2 {
      padding: 0.5rem 0.75rem;
      font-size: 0.8rem;
    }
  }

  @media (max-width: 480px) {
    .text-4xl {
      font-size: 1.75rem;
    }

    .w-16.h-16 {
      width: 3rem;
      height: 3rem;
    }

    .modern-action-btn {
      flex: 1;
      min-width: auto;
    }

    .flex.flex-col.gap-3 {
      width: 100%;
    }
  }

  /* Animation delays for staggered loading */
  .flex.items-center.gap-4 {
    animation: slideInRight 0.6s ease-out;
    animation-delay: 0.2s;
    animation-fill-mode: both;
  }

  .flex.flex-wrap.gap-4 {
    animation: fadeInUp 0.6s ease-out;
    animation-delay: 0.4s;
    animation-fill-mode: both;
  }

  .flex.flex-col.gap-3 {
    animation: slideInRight 0.6s ease-out;
    animation-delay: 0.6s;
    animation-fill-mode: both;
  }

  /* Dark mode support */
  @media (prefers-color-scheme: dark) {
    .min-h-screen {
      background: linear-gradient(135deg, #1e293b 0%, #334155 50%, #475569 100%);
    }

    .backdrop-blur-sm {
      background: rgba(30, 41, 59, 0.4);
      border-color: rgba(71, 85, 105, 0.3);
    }

    .bg-gradient-to-r {
      background: linear-gradient(90deg, rgba(59, 130, 246, 0.1), rgba(147, 51, 234, 0.1), rgba(236, 72, 153, 0.1));
    }

    .text-gray-700 {
      color: #cbd5e1;
    }

    .text-gray-600 {
      color: #94a3b8;
    }

    .section-title {
      color: #f1f5f9;
    }

    .section-subtitle {
      color: #cbd5e1;
    }
  }

  /* Dashboard Section Styles */
  .dashboard-section {
    position: relative;
    margin-bottom: 2rem;
  }

  .section-header {
    margin-bottom: 1.5rem;
    text-align: center;
  }

  .section-title {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    font-size: 1.75rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 0.5rem;
  }

  .section-title i {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.125rem;
    box-shadow: 0 4px 16px rgba(102, 126, 234, 0.3);
  }

  .section-subtitle {
    color: #6b7280;
    font-size: 1rem;
    font-weight: 500;
  }

  /* Loading State Styles */
  .loading-state {
    position: relative;
    min-height: 80vh;
  }

  .loading-grid {
    display: flex;
    flex-direction: column;
    gap: 3rem;
    opacity: 0.7;
  }

  .skeleton-section {
    margin-bottom: 2rem;
  }

  .loading-overlay {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 24px;
    padding: 3rem;
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
    text-align: center;
    z-index: 1000;
  }

  /* Enhanced Grid Animations */
  .grid {
    animation: gridFadeIn 0.6s ease-out forwards;
    opacity: 0;
  }

  @keyframes gridFadeIn {
    to {
      opacity: 1;
    }
  }

  .grid.grid-cols-1.lg\\:grid-cols-2:nth-of-type(1) {
    animation-delay: 0.1s;
  }

  .grid.grid-cols-1.lg\\:grid-cols-2:nth-of-type(2) {
    animation-delay: 0.2s;
  }

  .grid.grid-cols-1.lg\\:grid-cols-2:nth-of-type(3) {
    animation-delay: 0.3s;
  }

  /* Section animations */
  .dashboard-section {
    animation: sectionSlideIn 0.8s ease-out forwards;
    opacity: 0;
    transform: translateY(20px);
  }

  @keyframes sectionSlideIn {
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  .dashboard-section:nth-child(1) {
    animation-delay: 0.1s;
  }
  .dashboard-section:nth-child(2) {
    animation-delay: 0.2s;
  }
  .dashboard-section:nth-child(3) {
    animation-delay: 0.3s;
  }
  .dashboard-section:nth-child(4) {
    animation-delay: 0.4s;
  }
  .dashboard-section:nth-child(5) {
    animation-delay: 0.5s;
  }

  /* Mobile Section Headers */
  @media (max-width: 768px) {
    .section-header {
      margin-bottom: 1rem;
    }

    .section-title {
      font-size: 1.5rem;
      flex-direction: column;
      gap: 0.5rem;
    }

    .section-title i {
      width: 36px;
      height: 36px;
      font-size: 1rem;
    }

    .section-subtitle {
      font-size: 0.9rem;
    }

    .dashboard-section {
      margin-bottom: 1.5rem;
    }

    .mt-8 {
      margin-top: 1.5rem;
    }

    .loading-overlay {
      padding: 2rem;
      margin: 0 1rem;
      max-width: calc(100vw - 2rem);
      box-sizing: border-box;
    }
  }

  @media (max-width: 480px) {
    .section-title {
      font-size: 1.25rem;
    }

    .section-title i {
      width: 32px;
      height: 32px;
      font-size: 0.875rem;
    }

    .section-subtitle {
      font-size: 0.85rem;
    }

    .loading-overlay {
      padding: 1.5rem;
    }
  }

  /* Accessibility enhancements */
  @media (prefers-reduced-motion: reduce) {
    .dashboard-section,
    .grid,
    .mb-12,
    .w-16.h-16,
    .flex.items-center.gap-4,
    .flex.flex-wrap.gap-4,
    .flex.flex-col.gap-3 {
      animation: none;
      opacity: 1;
      transform: none;
    }

    .modern-action-btn:hover {
      transform: none;
    }
  }

  /* High contrast mode support */
  @media (prefers-contrast: high) {
    .dashboard-section,
    .loading-overlay,
    .backdrop-blur-sm {
      border: 2px solid;
    }

    .modern-action-btn {
      border: 2px solid;
    }

    .section-title i {
      border: 2px solid white;
    }
  }

  /* Modern Grid System */
  .modern-grid {
    display: grid;
    gap: 1.5rem;
    width: 100%;
    position: relative;
  }

  /* Budget Grid - Adaptive Layout */
  .budget-grid {
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    grid-auto-rows: minmax(280px, auto);
  }

  /* Analytics Grid - Equal Height Cards */
  .analytics-grid {
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    grid-auto-rows: minmax(320px, auto);
  }

  /* Activity Grid - Featured Layout */
  .activity-grid {
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    grid-auto-rows: minmax(300px, auto);
  }

  /* Grid Items */
  .grid-item {
    position: relative;
    width: 100%;
    height: 100%;
    border-radius: 20px;
    overflow: hidden;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.06);
  }

  .grid-item::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 2px;
    background: linear-gradient(90deg, #667eea, #764ba2, #667eea);
    animation: shimmer 3s ease-in-out infinite;
    z-index: 1;
  }

  .grid-item:hover {
    transform: translateY(-8px) scale(1.02);
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.12);
    border-color: rgba(255, 255, 255, 0.4);
  }

  /* Featured grid item */
  .grid-item.featured {
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
    border: 2px solid rgba(102, 126, 234, 0.2);
  }

  .grid-item.featured::before {
    height: 3px;
    background: linear-gradient(90deg, #667eea, #764ba2, #a8edea, #fed6e3);
  }

  /* Responsive Grid Behavior */

  /* Large Desktop (1440px+) */
  @media (min-width: 1440px) {
    .modern-grid {
      gap: 2rem;
    }

    .budget-grid {
      grid-template-columns: repeat(2, 1fr);
    }

    .analytics-grid {
      grid-template-columns: repeat(2, 1fr);
    }

    .activity-grid {
      grid-template-columns: 1.2fr 0.8fr; /* Featured layout */
    }
  }

  /* Desktop (1024px - 1439px) */
  @media (max-width: 1439px) and (min-width: 1024px) {
    .modern-grid {
      gap: 1.5rem;
    }

    .budget-grid,
    .analytics-grid,
    .activity-grid {
      grid-template-columns: repeat(2, 1fr);
    }
  }

  /* Tablet (769px - 1023px) */
  @media (max-width: 1023px) and (min-width: 769px) {
    .modern-grid {
      gap: 1.25rem;
    }

    .budget-grid {
      grid-template-columns: repeat(2, 1fr);
      grid-auto-rows: minmax(250px, auto);
    }

    .analytics-grid {
      grid-template-columns: 1fr;
      gap: 1rem;
    }

    .activity-grid {
      grid-template-columns: 1fr;
      gap: 1rem;
    }

    .grid-item {
      border-radius: 16px;
    }
  }

  /* Mobile (= 768px) */
  @media (max-width: 768px) {
    .modern-grid {
      gap: 1rem;
    }

    .budget-grid,
    .analytics-grid,
    .activity-grid {
      grid-template-columns: 1fr;
      grid-auto-rows: minmax(220px, auto);
    }

    .grid-item {
      border-radius: 12px;
      padding: 0;
    }

    .grid-item:hover {
      transform: translateY(-4px);
      box-shadow: 0 12px 40px rgba(0, 0, 0, 0.1);
    }

    .grid-item::before {
      height: 2px;
    }

    .grid-item.featured::before {
      height: 2px;
    }
  }

  /* Small Mobile (= 480px) */
  @media (max-width: 480px) {
    .modern-grid {
      gap: 0.75rem;
    }

    .grid-item {
      border-radius: 10px;
      grid-auto-rows: minmax(200px, auto);
    }

    .grid-item:hover {
      transform: translateY(-2px);
    }
  }

  /* Grid Animation System */
  .modern-grid {
    animation: gridSlideIn 0.8s ease-out forwards;
    opacity: 0;
    transform: translateY(20px);
  }

  @keyframes gridSlideIn {
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  /* Staggered grid animations */
  .budget-grid {
    animation-delay: 0.1s;
  }

  .analytics-grid {
    animation-delay: 0.2s;
  }

  .activity-grid {
    animation-delay: 0.3s;
  }

  /* Grid item hover effects */
  .grid-item {
    position: relative;
    overflow: hidden;
  }

  .grid-item::after {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: linear-gradient(45deg, transparent 30%, rgba(255, 255, 255, 0.1) 50%, transparent 70%);
    transform: rotate(45deg);
    transition: all 0.6s;
    opacity: 0;
  }

  .grid-item:hover::after {
    animation: shine 0.6s ease-out;
  }

  @keyframes shine {
    0% {
      transform: translateX(-100%) translateY(-100%) rotate(45deg);
      opacity: 0;
    }
    50% {
      opacity: 1;
    }
    100% {
      transform: translateX(100%) translateY(100%) rotate(45deg);
      opacity: 0;
    }
  }

  /* Performance optimizations */
  .grid-item {
    will-change: transform;
    backface-visibility: hidden;
    perspective: 1000px;
  }

  /* Dark mode grid styles */
  @media (prefers-color-scheme: dark) {
    .grid-item {
      background: rgba(30, 41, 59, 0.8);
      border-color: rgba(71, 85, 105, 0.3);
    }

    .grid-item.featured {
      background: linear-gradient(135deg, rgba(102, 126, 234, 0.2) 0%, rgba(118, 75, 162, 0.2) 100%);
      border-color: rgba(102, 126, 234, 0.4);
    }

    .grid-item:hover {
      background: rgba(30, 41, 59, 0.9);
      border-color: rgba(71, 85, 105, 0.5);
    }
  }

  /* Mobile-specific styles */
  .dashboard-mobile {
    position: relative;
    overflow-x: hidden;
  }

  .mobile-content {
    transition: transform 0.3s ease;
    min-height: 100vh;
  }

  .mobile-content.content-shifted {
    transform: translateX(280px);
  }

  .mobile-header {
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.8) 0%, rgba(248, 250, 252, 0.9) 100%);
    backdrop-filter: blur(10px);
    border-bottom: 1px solid rgba(226, 232, 240, 0.5);
  }

  .mobile-greeting h2 {
    background: linear-gradient(135deg, #1e40af 0%, #7c3aed 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
  }

  .stat-card {
    transition: all 0.2s ease;
    cursor: pointer;
  }

  .stat-card:active {
    transform: scale(0.98);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  }

  .expense-item {
    transition: all 0.2s ease;
    cursor: pointer;
  }

  .expense-item:active {
    transform: scale(0.98);
    background-color: #f8fafc;
  }

  .budget-item {
    transition: all 0.2s ease;
    cursor: pointer;
  }

  .budget-item:active {
    transform: scale(0.98);
    background-color: #f8fafc;
  }

  .action-card {
    transition: all 0.2s ease;
    cursor: pointer;
  }

  .action-card:active {
    transform: scale(0.98);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  }

  .view-all-btn {
    transition: all 0.2s ease;
  }

  .view-all-btn:active {
    transform: scale(0.95);
  }

  /* Mobile safe area handling */
  @supports (padding: max(0px)) {
    .mobile-content {
      padding-left: max(16px, env(safe-area-inset-left));
      padding-right: max(16px, env(safe-area-inset-right));
      padding-bottom: max(80px, env(safe-area-inset-bottom));
    }
  }

  /* Mobile landscape optimizations */
  @media screen and (max-height: 500px) and (orientation: landscape) {
    .mobile-header {
      padding: 8px 16px;
    }

    .mobile-header h2 {
      font-size: 1.25rem;
    }

    .mobile-stats-grid {
      padding: 8px 16px;
    }

    .mobile-sections {
      padding: 0 16px 60px;
    }
  }

  /* High contrast mode support */
  @media (prefers-contrast: high) {
    .stat-card,
    .expense-item,
    .budget-item,
    .action-card {
      border: 2px solid #000;
    }

    .mobile-header {
      border-bottom: 2px solid #000;
    }
  }

  /* Reduced motion support */
  @media (prefers-reduced-motion: reduce) {
    .mobile-content,
    .stat-card,
    .expense-item,
    .budget-item,
    .action-card,
    .view-all-btn {
      transition: none;
    }

    .stat-card:active,
    .expense-item:active,
    .budget-item:active,
    .action-card:active,
    .view-all-btn:active {
      transform: none;
    }
  }
</style>
