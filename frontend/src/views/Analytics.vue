<template>
  <div class="min-h-screen bg-gray-50">
    <AppHeader />
    <div class="flex">
      <AppSidebar class="hidden md:block" />
      <main class="flex-1 py-6 px-4">
        <!-- Mobile Navigation -->
        <MobileAnalyticsNav
          v-if="isMobile"
          :selected-period="selectedPeriod"
          :loading="loading"
          :categories="[]"
          :view-mode="isMobile ? 'compact' : 'detailed'"
          @refresh="refreshAnalytics"
          @export="exportAnalytics"
          @share="shareAnalytics"
          @period-change="updatePeriod"
          @filter-change="updateFilter"
          @view-change="updateViewMode"
        />

        <div class="max-w-7xl mx-auto">
          <div class="mb-12">
            <div class="flex justify-between items-start">
              <div class="flex-1">
                <div class="flex items-center gap-4 mb-3">
                  <div
                    class="w-14 h-14 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl flex items-center justify-center shadow-lg"
                  >
                    <i class="fas fa-chart-line text-white text-xl"></i>
                  </div>
                  <div>
                    <h1 class="text-3xl font-bold text-gray-900 leading-tight tracking-tight">Advanced Analytics</h1>
                    <div class="flex items-center gap-2 mt-1">
                      <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
                      <span class="text-sm text-gray-500 font-medium">Real-time insights</span>
                    </div>
                  </div>
                </div>
                <p class="text-gray-600 text-lg leading-relaxed max-w-2xl">
                  Get comprehensive insights into your spending patterns, financial health, and personalized
                  recommendations to improve your financial wellbeing.
                </p>
              </div>

              <div class="flex items-center gap-4">
                <!-- Back to Dashboard Button -->
                <router-link to="/" class="modern-back-btn" title="Back to Dashboard">
                  <i class="fas fa-arrow-left"></i>
                  <span>Dashboard</span>
                </router-link>

                <!-- Period Selector -->
                <div class="modern-select-wrapper">
                  <select v-model="selectedPeriod" @change="onPeriodChange" class="modern-select">
                    <option value="weekly">Weekly</option>
                    <option value="monthly">Monthly</option>
                    <option value="quarterly">Quarterly</option>
                    <option value="yearly">Yearly</option>
                  </select>
                  <i class="fas fa-chevron-down select-arrow"></i>

                  <!-- Mobile swipe indicator -->
                  <div v-if="isMobile" class="swipe-indicator">
                    <div class="swipe-hint">
                      <i class="fas fa-hand-paper"></i>
                      <span>Swipe to change period</span>
                    </div>
                    <div class="swipe-dots">
                      <span
                        v-for="period in ['weekly', 'monthly', 'quarterly', 'yearly']"
                        :key="period"
                        :class="['dot', { active: selectedPeriod === period }]"
                      ></span>
                    </div>
                  </div>
                </div>

                <!-- Refresh Button -->
                <button
                  @click="refreshAnalytics"
                  :disabled="loading"
                  class="modern-refresh-btn"
                  :class="{ loading: loading }"
                >
                  <i class="fas fa-sync-alt" :class="{ 'fa-spin': loading }"></i>
                  <span>Refresh</span>
                </button>
              </div>
            </div>
          </div>

          <!-- Loading State -->
          <template v-if="loading && !dashboardData">
            <TransitionWrapper type="fade" :duration="300">
              <div class="loading-state">
                <div class="loading-grid">
                  <!-- Metrics Skeletons -->
                  <div class="metrics-skeleton">
                    <div v-for="i in 4" :key="i" class="metric-skeleton">
                      <LoadingSkeleton type="metric" variant="shimmer" size="lg" />
                    </div>
                  </div>

                  <!-- Charts Skeletons -->
                  <div class="charts-skeleton">
                    <div v-for="i in 2" :key="i" class="chart-skeleton">
                      <LoadingSkeleton type="chart" variant="wave" size="xl" />
                    </div>
                  </div>

                  <!-- Insights Skeleton -->
                  <div class="insights-skeleton">
                    <div v-for="i in 4" :key="i" class="insight-skeleton">
                      <LoadingSkeleton type="card" variant="pulse" size="md" :lines="3" :show-footer="true" />
                    </div>
                  </div>
                </div>

                <!-- Loading Spinner Overlay -->
                <div class="loading-overlay">
                  <LoadingSpinner
                    size="lg"
                    variant="primary"
                    :show-icon="true"
                    text="Analyzing your financial data..."
                  />
                </div>
              </div>
            </TransitionWrapper>
          </template>

          <!-- Error State -->
          <template v-else-if="error">
            <TransitionWrapper type="bounce" :duration="400">
              <ErrorState
                type="general"
                variant="detailed"
                :title="error.title || 'Analytics Error'"
                :message="error.message || 'Unable to load analytics data'"
                :code="error.code"
                :details="error.details"
                :show-details="!!error.details"
                :show-retry="true"
                :show-refresh="true"
                :show-contact="true"
                :retrying="loading"
                @retry="loadAnalytics"
                @refresh="refreshAnalytics"
                @contact="contactSupport"
              />
            </TransitionWrapper>
          </template>

          <!-- Dashboard Content -->
          <template v-else-if="dashboardData">
            <TransitionWrapper type="slide-up" :duration="500">
              <!-- Key Metrics Overview -->
              <div class="metrics-overview" data-section="metrics">
                <TransitionWrapper type="fade" :duration="400" :delay="100">
                  <div class="metrics-grid">
                    <TransitionWrapper
                      v-for="(metric, index) in metricsData"
                      :key="index"
                      type="zoom"
                      :duration="300"
                      :delay="index * 100"
                    >
                      <div class="metric-card" :class="metric.type">
                        <div class="metric-icon">{{ metric.icon }}</div>
                        <div class="metric-content">
                          <h3 class="metric-value">{{ metric.value }}</h3>
                          <p class="metric-label">{{ metric.label }}</p>
                          <div class="metric-trend" :class="metric.trend.direction">
                            <span class="trend-icon">{{ metric.trend.icon }}</span>
                            <span class="trend-value">{{ metric.trend.percentage }}%</span>
                          </div>
                        </div>
                      </div>
                    </TransitionWrapper>
                  </div>
                </TransitionWrapper>
              </div>

              <!-- Advanced Charts Section -->
              <TransitionWrapper type="slide-left" :duration="400" :delay="300">
                <div class="advanced-charts" data-section="charts">
                  <div class="charts-grid">
                    <!-- Spending Trends Analysis -->
                    <TransitionWrapper type="scale" :duration="400">
                      <div class="chart-container trend-analysis">
                        <div class="chart-header">
                          <h3 class="chart-title">
                            <i class="fas fa-chart-line"></i>
                            Spending Trends Analysis
                          </h3>
                          <div class="chart-controls">
                            <select
                              v-model="trendViewType"
                              class="view-selector"
                              :class="{ 'mobile-optimized': isMobile }"
                            >
                              <option value="daily">Daily View</option>
                              <option value="weekly">Weekly View</option>
                              <option value="monthly">Monthly View</option>
                            </select>
                          </div>
                        </div>
                        <div class="chart-content">
                          <Suspense>
                            <template #default>
                              <MobileChartWrapper
                                v-if="isMobile"
                                title="Spending Trends"
                                subtitle="Daily, weekly, and monthly patterns"
                                :allow-zoom="true"
                                :show-share="true"
                                :show-download="true"
                              >
                                <TrendsWidget
                                  :trends="trends"
                                  :period="selectedPeriod"
                                  :view-type="trendViewType"
                                  :loading="loading"
                                />
                              </MobileChartWrapper>
                              <TrendsWidget
                                v-else
                                :trends="trends"
                                :period="selectedPeriod"
                                :view-type="trendViewType"
                                :loading="loading"
                              />
                            </template>
                            <template #fallback>
                              <LoadingSkeleton type="chart" variant="wave" size="lg" />
                            </template>
                          </Suspense>
                        </div>
                      </div>
                    </TransitionWrapper>

                    <!-- Category Deep Dive -->
                    <TransitionWrapper type="scale" :duration="400" :delay="100">
                      <div class="chart-container category-analysis">
                        <div class="chart-header">
                          <h3 class="chart-title">
                            <i class="fas fa-chart-pie"></i>
                            Category Deep Dive
                          </h3>
                          <div class="chart-controls">
                            <button
                              v-for="view in categoryViews"
                              :key="view.value"
                              :class="['view-btn', 'touch-optimized', { active: categoryViewType === view.value }]"
                              @click="categoryViewType = view.value"
                              :aria-label="`Switch to ${view.label} view`"
                            >
                              {{ view.label }}
                            </button>
                          </div>
                        </div>
                        <div class="chart-content">
                          <Suspense>
                            <template #default>
                              <MobileChartWrapper
                                v-if="isMobile"
                                title="Category Breakdown"
                                subtitle="Spending distribution by category"
                                :allow-zoom="true"
                                :show-share="true"
                                :show-download="true"
                              >
                                <SpendingPatternsWidget
                                  :patterns="patterns"
                                  :view-type="categoryViewType"
                                  :loading="loading"
                                />
                              </MobileChartWrapper>
                              <SpendingPatternsWidget
                                v-else
                                :patterns="patterns"
                                :view-type="categoryViewType"
                                :loading="loading"
                              />
                            </template>
                            <template #fallback>
                              <LoadingSkeleton type="chart" variant="shimmer" size="lg" />
                            </template>
                          </Suspense>
                        </div>
                      </div>
                    </TransitionWrapper>
                  </div>
                </div>
              </TransitionWrapper>

              <!-- Smart Insights Section -->
              <TransitionWrapper type="slide-right" :duration="400" :delay="400">
                <div class="insights-section" data-section="insights">
                  <div class="insights-header">
                    <h3 class="section-title">?? Smart Insights</h3>
                    <p class="section-subtitle">AI-powered analysis of your spending patterns</p>
                  </div>

                  <div class="insights-grid">
                    <TransitionWrapper
                      v-for="(insight, index) in smartInsights"
                      :key="insight.id"
                      type="elastic"
                      :duration="300"
                      :delay="index * 150"
                    >
                      <div class="insight-card" :class="insight.type">
                        <div class="insight-icon">{{ insight.icon }}</div>
                        <div class="insight-content">
                          <h4 class="insight-title">{{ insight.title }}</h4>
                          <p class="insight-description">{{ insight.description }}</p>
                          <div class="insight-action" v-if="insight.action">
                            <button class="action-btn" @click="handleInsightAction(insight)">
                              {{ insight.action }}
                            </button>
                          </div>
                        </div>
                      </div>
                    </TransitionWrapper>
                  </div>
                </div>
              </TransitionWrapper>

              <!-- Comparative Analysis -->
              <TransitionWrapper type="slide-up" :duration="400" :delay="500">
                <div class="comparison-section" data-section="comparison">
                  <div class="comparison-header">
                    <h3 class="section-title">
                      <i class="fas fa-chart-bar"></i>
                      Comparative Analysis
                    </h3>
                    <div class="comparison-controls">
                      <select
                        v-model="comparisonType"
                        class="comparison-selector"
                        :class="{ 'mobile-optimized': isMobile }"
                      >
                        <option value="previous-month">vs Previous Month</option>
                        <option value="previous-quarter">vs Previous Quarter</option>
                        <option value="previous-year">vs Previous Year</option>
                        <option value="budget">vs Budget</option>
                      </select>
                    </div>
                  </div>

                  <div class="comparison-content">
                    <div class="dashboard-card large">
                      <div class="card-body">
                        <Suspense>
                          <template #default>
                            <ForecastsWidget
                              :forecasts="forecasts"
                              :comparison-type="comparisonType"
                              :loading="loading"
                            />
                          </template>
                          <template #fallback>
                            <LoadingSkeleton type="chart" variant="pulse" size="xl" />
                          </template>
                        </Suspense>
                      </div>
                    </div>
                  </div>
                </div>
              </TransitionWrapper>

              <!-- Predictive Analytics -->
              <TransitionWrapper type="fade" :duration="400" :delay="600">
                <div class="predictions-section" data-section="predictions">
                  <div class="predictions-header">
                    <h3 class="section-title">
                      <i class="fas fa-crystal-ball"></i>
                      Predictive Analytics
                    </h3>
                    <p class="section-subtitle">Future spending projections based on historical data</p>
                  </div>

                  <div class="predictions-content">
                    <div class="prediction-chart">
                      <div class="dashboard-card large">
                        <div class="card-header">
                          <h5 class="card-title">
                            <i class="fas fa-chart-area"></i>
                            Future Projections
                          </h5>
                        </div>
                        <div class="card-body">
                          <Suspense>
                            <template #default>
                              <FinancialHealthCard
                                :period="selectedPeriod"
                                :auto-refresh="false"
                                :show-predictions="true"
                              />
                            </template>
                            <template #fallback>
                              <LoadingSkeleton type="chart" variant="wave" size="xl" />
                            </template>
                          </Suspense>
                        </div>
                      </div>
                    </div>

                    <div class="prediction-summary">
                      <TransitionWrapper
                        v-for="(card, index) in predictionCards"
                        :key="index"
                        type="bounce"
                        :duration="300"
                        :delay="index * 100"
                      >
                        <div class="prediction-card">
                          <h4>{{ card.title }}</h4>
                          <div :class="card.valueClass">{{ card.value }}</div>
                          <div class="projection-confidence">{{ card.subtitle }}</div>
                        </div>
                      </TransitionWrapper>
                    </div>
                  </div>
                </div>
              </TransitionWrapper>

              <!-- Legacy Widgets -->
              <TransitionWrapper type="slide-down" :duration="400" :delay="700">
                <div class="legacy-widgets">
                  <div class="dashboard-grid">
                    <!-- Insights Widget -->
                    <TransitionWrapper type="flip" :duration="400">
                      <div class="dashboard-card">
                        <div class="card-header">
                          <h5 class="card-title">
                            <i class="fas fa-lightbulb"></i>
                            Additional Insights
                          </h5>
                        </div>
                        <div class="card-body">
                          <Suspense>
                            <template #default>
                              <InsightsWidget :insights="insights" :loading="loading" />
                            </template>
                            <template #fallback>
                              <LoadingSkeleton type="list" variant="pulse" size="md" :items="3" />
                            </template>
                          </Suspense>
                        </div>
                      </div>
                    </TransitionWrapper>

                    <!-- Recommendations Widget -->
                    <TransitionWrapper type="flip" :duration="400" :delay="100">
                      <div class="dashboard-card">
                        <div class="card-header">
                          <h5 class="card-title">
                            <i class="fas fa-magic"></i>
                            Recommendations
                          </h5>
                        </div>
                        <div class="card-body">
                          <Suspense>
                            <template #default>
                              <RecommendationsWidget
                                :recommendations="recommendations"
                                :loading="loading"
                                @dismiss-recommendation="dismissRecommendation"
                              />
                            </template>
                            <template #fallback>
                              <LoadingSkeleton type="list" variant="shimmer" size="md" :items="4" />
                            </template>
                          </Suspense>
                        </div>
                      </div>
                    </TransitionWrapper>
                  </div>
                </div>
              </TransitionWrapper>
            </TransitionWrapper>
          </template>

          <!-- Empty State -->
          <template v-else>
            <TransitionWrapper type="scale" :duration="400">
              <EmptyState
                type="analytics"
                variant="detailed"
                size="lg"
                title="No Analytics Available"
                message="Start tracking your expenses to see comprehensive analytics and insights about your spending patterns."
                :steps="[
                  'Add your first expense to get started',
                  'Create categories to organize your spending',
                  'Set up budgets to track your goals',
                  'Check back here for detailed analytics'
                ]"
                steps-title="Getting started is easy:"
                :show-primary="true"
                :show-secondary="true"
                :show-link="true"
                primary-text="Add First Expense"
                primary-icon="fas fa-plus"
                secondary-text="View Tutorial"
                secondary-icon="fas fa-play"
                link-text="Go to Dashboard"
                link-to="/"
                link-icon="fas fa-home"
                @primary-action="addFirstExpense"
                @secondary-action="showTutorial"
              />
            </TransitionWrapper>
          </template>
        </div>
      </main>
    </div>
  </div>
</template>

<script>
  import { ref, computed, onMounted, onUnmounted } from 'vue'
  import { useAnalyticsStore } from '@/stores/analytics'
  import AppHeader from '@/components/layout/AppHeader.vue'
  import AppSidebar from '@/components/layout/AppSidebar.vue'
  import FinancialHealthCard from '@/components/analytics/FinancialHealthCard.vue'
  import SpendingPatternsWidget from '@/components/analytics/SpendingPatternsWidget.vue'
  import InsightsWidget from '@/components/analytics/InsightsWidget.vue'
  import RecommendationsWidget from '@/components/analytics/RecommendationsWidget.vue'
  import ForecastsWidget from '@/components/analytics/ForecastsWidget.vue'
  import TrendsWidget from '@/components/analytics/TrendsWidget.vue'
  import LoadingSkeleton from '@/components/common/LoadingSkeleton.vue'
  import ErrorState from '@/components/common/ErrorState.vue'
  import EmptyState from '@/components/common/EmptyState.vue'
  import TransitionWrapper from '@/components/common/TransitionWrapper.vue'
  import LoadingSpinner from '@/components/common/LoadingSpinner.vue'
  import MobileChartWrapper from '@/components/common/MobileChartWrapper.vue'
  import MobileAnalyticsNav from '@/components/common/MobileAnalyticsNav.vue'

  export default {
    name: 'AnalyticsDashboard',
    components: {
      AppHeader,
      AppSidebar,
      FinancialHealthCard,
      SpendingPatternsWidget,
      InsightsWidget,
      RecommendationsWidget,
      ForecastsWidget,
      TrendsWidget,
      LoadingSkeleton,
      ErrorState,
      EmptyState,
      TransitionWrapper,
      LoadingSpinner,
      MobileChartWrapper,
      MobileAnalyticsNav
    },
    setup() {
      const analyticsStore = useAnalyticsStore()
      const selectedPeriod = ref('monthly')
      const trendViewType = ref('daily')
      const categoryViewType = ref('amount')
      const comparisonType = ref('previous-month')

      // Mobile interaction states
      const isMobile = ref(false)
      const isTouch = ref(false)
      const touchStartX = ref(0)
      const touchStartY = ref(0)
      const swipeThreshold = 50

      // Category view options
      const categoryViews = ref([
        { label: 'Amount', value: 'amount' },
        { label: 'Count', value: 'count' },
        { label: 'Trend', value: 'trend' }
      ])

      // Mobile detection
      const detectMobile = () => {
        isMobile.value = window.innerWidth <= 768
        isTouch.value = 'ontouchstart' in window || navigator.maxTouchPoints > 0
      }

      // Touch event handlers for swipe gestures
      const handleTouchStart = event => {
        if (!isTouch.value) return
        touchStartX.value = event.touches[0].clientX
        touchStartY.value = event.touches[0].clientY
      }

      const handleTouchEnd = event => {
        if (!isTouch.value) return

        const touchEndX = event.changedTouches[0].clientX
        const touchEndY = event.changedTouches[0].clientY
        const deltaX = touchEndX - touchStartX.value
        const deltaY = touchEndY - touchStartY.value

        // Only handle horizontal swipes with sufficient distance
        if (Math.abs(deltaX) > Math.abs(deltaY) && Math.abs(deltaX) > swipeThreshold) {
          if (deltaX > 0) {
            // Swipe right - go to previous period
            handleSwipeNavigation('previous')
          } else {
            // Swipe left - go to next period
            handleSwipeNavigation('next')
          }
        }
      }

      const handleSwipeNavigation = direction => {
        const periods = ['weekly', 'monthly', 'quarterly', 'yearly']
        const currentIndex = periods.indexOf(selectedPeriod.value)

        let newIndex
        if (direction === 'next') {
          newIndex = currentIndex + 1 >= periods.length ? 0 : currentIndex + 1
        } else {
          newIndex = currentIndex - 1 < 0 ? periods.length - 1 : currentIndex - 1
        }

        selectedPeriod.value = periods[newIndex]
        onPeriodChange()
      }

      // Keyboard navigation
      const handleKeyDown = event => {
        if (event.key === 'ArrowLeft') {
          handleSwipeNavigation('previous')
        } else if (event.key === 'ArrowRight') {
          handleSwipeNavigation('next')
        }
      }

      // Resize handler
      const handleResize = () => {
        detectMobile()
      }

      // Computed properties
      const dashboardData = computed(() => analyticsStore.dashboardData)
      const patterns = computed(() => analyticsStore.patterns)
      const financialHealth = computed(() => analyticsStore.financialHealth)
      const insights = computed(() => analyticsStore.insights)
      const forecasts = computed(() => analyticsStore.forecasts)
      const recommendations = computed(() => analyticsStore.recommendations)
      const trends = computed(() => analyticsStore.trends)
      const loading = computed(() => analyticsStore.loading)
      const error = computed(() => analyticsStore.error)
      const currentPeriod = computed(() => analyticsStore.currentPeriod)

      // Advanced analytics computed properties
      const totalSpending = computed(() => 3247.85)
      const averageDaily = computed(() => 108.26)
      const monthlyForecast = computed(() => 3590.5)
      const nextMonthProjection = computed(() => 3420.75)
      const projectionConfidence = computed(() => 87)

      const spendingTrend = computed(() => ({
        direction: 'increase',
        icon: '📈',
        percentage: 12.5
      }))

      const averageTrend = computed(() => ({
        direction: 'decrease',
        icon: '📉',
        percentage: 8.3
      }))

      const topCategory = computed(() => ({
        name: 'Food & Dining',
        amount: 1247.5,
        percentage: 38.4
      }))

      const spendingVelocity = computed(() => ({
        value: '1.2x',
        label: 'Above Average',
        trend: 'high'
      }))

      const budgetHealth = computed(() => ({
        score: 78,
        label: 'Good',
        status: 'good'
      }))

      // Smart insights
      const smartInsights = computed(() => [
        {
          id: 1,
          type: 'warning',
          icon: '⚠️',
          title: 'Weekend Spending Spike',
          description: 'Your weekend spending is 40% higher than weekdays. Consider setting weekend budgets.',
          action: 'Set Weekend Budget'
        },
        {
          id: 2,
          type: 'success',
          icon: '✓',
          title: 'Great Food Budget Control',
          description: "You're 15% under your food budget this month. Keep up the good work!",
          action: null
        },
        {
          id: 3,
          type: 'info',
          icon: '💡',
          title: 'Subscription Optimization',
          description: 'You have 3 similar subscriptions. Consolidating could save $45/month.',
          action: 'Review Subscriptions'
        },
        {
          id: 4,
          type: 'tip',
          icon: '📊',
          title: 'Seasonal Pattern Detected',
          description: 'Your entertainment spending typically increases by 25% in winter months.',
          action: 'Plan Ahead'
        }
      ])

      // Metrics data for enhanced display
      const metricsData = computed(() => [
        {
          type: 'spending',
          icon: '💰',
          value: formatCurrency(totalSpending.value),
          label: 'Total Spending',
          trend: spendingTrend.value
        },
        {
          type: 'average',
          icon: '📈',
          value: formatCurrency(averageDaily.value),
          label: 'Daily Average',
          trend: averageTrend.value
        },
        {
          type: 'categories',
          icon: '🏆',
          value: topCategory.value.name,
          label: 'Top Category',
          trend: {
            direction: 'neutral',
            icon: '➡️',
            percentage: topCategory.value.percentage
          }
        },
        {
          type: 'forecast',
          icon: '🔮',
          value: formatCurrency(monthlyForecast.value),
          label: 'Monthly Forecast',
          trend: {
            direction: 'positive',
            icon: '📈',
            percentage: 8.5
          }
        }
      ])

      // Prediction cards data
      const predictionCards = computed(() => [
        {
          title: 'Next Month Projection',
          value: formatCurrency(nextMonthProjection.value),
          subtitle: `Confidence: ${projectionConfidence.value}%`,
          valueClass: 'projection-value'
        },
        {
          title: 'Spending Velocity',
          value: spendingVelocity.value.value,
          subtitle: spendingVelocity.value.label,
          valueClass: `velocity-value ${spendingVelocity.value.trend}`
        },
        {
          title: 'Budget Health',
          value: `${budgetHealth.value.score}/100`,
          subtitle: budgetHealth.value.label,
          valueClass: `health-score ${budgetHealth.value.status}`
        }
      ])

      // Utility functions
      const formatCurrency = amount => {
        return new Intl.NumberFormat('en-US', {
          style: 'currency',
          currency: 'USD'
        }).format(amount)
      }

      const handleInsightAction = insight => {
        console.log('Handling insight action:', insight.action)
        // Implement specific actions based on insight type
      }

      const contactSupport = () => {
        console.log('Contact support')
        // Implement contact support functionality
      }

      const addFirstExpense = () => {
        console.log('Add first expense')
        // Navigate to expense creation or show modal
      }

      const showTutorial = () => {
        console.log('Show tutorial')
        // Show tutorial or guide
      }

      // Mobile-specific methods
      const updatePeriod = newPeriod => {
        selectedPeriod.value = newPeriod
        onPeriodChange()
      }

      const updateFilter = (filterType, filterValue) => {
        console.log('Filter updated:', filterType, filterValue)
        // Implement filtering logic
      }

      const updateViewMode = mode => {
        console.log('View mode updated:', mode)
        // Implement view mode changes
      }

      const exportAnalytics = () => {
        console.log('Export analytics')
        // Implement export functionality
      }

      const shareAnalytics = () => {
        console.log('Share analytics')
        // Implement share functionality
      }

      // Methods
      const onPeriodChange = () => {
        analyticsStore.setPeriod(selectedPeriod.value)
        loadAnalytics()
      }

      const refreshAnalytics = () => {
        analyticsStore.refreshAnalytics(selectedPeriod.value)
      }

      const loadAnalytics = () => {
        analyticsStore.loadAllAnalytics(selectedPeriod.value)
      }

      const dismissRecommendation = index => {
        // Remove recommendation from local array
        const updatedRecommendations = [...recommendations.value]
        updatedRecommendations.splice(index, 1)
        // You would typically call an API here to persist this change
      }

      // Lifecycle
      onMounted(() => {
        detectMobile()
        loadAnalytics()

        // Add event listeners
        window.addEventListener('resize', handleResize)
        window.addEventListener('keydown', handleKeyDown)
        document.addEventListener('touchstart', handleTouchStart, { passive: true })
        document.addEventListener('touchend', handleTouchEnd, { passive: true })
      })

      onUnmounted(() => {
        // Remove event listeners
        window.removeEventListener('resize', handleResize)
        window.removeEventListener('keydown', handleKeyDown)
        document.removeEventListener('touchstart', handleTouchStart)
        document.removeEventListener('touchend', handleTouchEnd)
      })

      return {
        // Data
        selectedPeriod,
        trendViewType,
        categoryViewType,
        comparisonType,
        categoryViews,

        // Mobile states
        isMobile,
        isTouch,

        // Computed
        dashboardData,
        patterns,
        financialHealth,
        insights,
        forecasts,
        recommendations,
        trends,
        loading,
        error,
        currentPeriod,

        // Advanced Analytics
        totalSpending,
        averageDaily,
        monthlyForecast,
        nextMonthProjection,
        projectionConfidence,
        spendingTrend,
        averageTrend,
        topCategory,
        spendingVelocity,
        budgetHealth,
        smartInsights,
        metricsData,
        predictionCards,

        // Methods
        onPeriodChange,
        refreshAnalytics,
        loadAnalytics,
        dismissRecommendation,
        formatCurrency,
        handleInsightAction,
        contactSupport,
        addFirstExpense,
        showTutorial,

        // Mobile interaction methods
        handleTouchStart,
        handleTouchEnd,
        handleSwipeNavigation,
        handleKeyDown,

        // Mobile-specific methods
        updatePeriod,
        updateFilter,
        updateViewMode,
        exportAnalytics,
        shareAnalytics
      }
    }
  }
</script>

<style scoped>
  /* Advanced Analytics Styles */

  /* Loading State */
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

  .metrics-skeleton {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
  }

  .charts-skeleton {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
    gap: 2rem;
  }

  .insights-skeleton {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 1.5rem;
  }

  .loading-overlay {
    position: absolute;
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
    z-index: 10;
  }

  /* Enhanced Transitions */
  .dashboard-content {
    animation: slideInUp 0.6s ease-out;
  }

  @keyframes slideInUp {
    from {
      opacity: 0;
      transform: translateY(40px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  /* Metric Card Enhancements */
  .metric-card {
    transform: translateY(0);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  }

  .metric-card:hover {
    transform: translateY(-8px) scale(1.02);
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
  }

  .metric-card:nth-child(1) {
    animation-delay: 0.1s;
  }
  .metric-card:nth-child(2) {
    animation-delay: 0.2s;
  }
  .metric-card:nth-child(3) {
    animation-delay: 0.3s;
  }
  .metric-card:nth-child(4) {
    animation-delay: 0.4s;
  }

  /* Chart Container Enhancements */
  .chart-container {
    animation: fadeInScale 0.5s ease-out forwards;
    opacity: 0;
    transform: scale(0.95);
  }

  @keyframes fadeInScale {
    to {
      opacity: 1;
      transform: scale(1);
    }
  }

  .trend-analysis {
    animation-delay: 0.1s;
  }

  .category-analysis {
    animation-delay: 0.2s;
  }

  /* Insight Card Animations */
  .insight-card {
    animation: bounceIn 0.6s ease-out forwards;
    opacity: 0;
    transform: scale(0.8);
  }

  @keyframes bounceIn {
    0% {
      opacity: 0;
      transform: scale(0.8);
    }
    60% {
      opacity: 1;
      transform: scale(1.05);
    }
    100% {
      opacity: 1;
      transform: scale(1);
    }
  }

  .insight-card:nth-child(1) {
    animation-delay: 0.1s;
  }
  .insight-card:nth-child(2) {
    animation-delay: 0.2s;
  }
  .insight-card:nth-child(3) {
    animation-delay: 0.3s;
  }
  .insight-card:nth-child(4) {
    animation-delay: 0.4s;
  }

  /* Micro-interactions */
  .action-btn {
    position: relative;
    overflow: hidden;
  }

  .action-btn::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.3);
    transform: translate(-50%, -50%);
    transition:
      width 0.6s,
      height 0.6s;
  }

  .action-btn:active::after {
    width: 300px;
    height: 300px;
  }

  /* Loading Skeleton Customizations */
  .metric-skeleton,
  .chart-skeleton,
  .insight-skeleton {
    animation: skeletonPulse 1.5s ease-in-out infinite;
  }

  @keyframes skeletonPulse {
    0%,
    100% {
      opacity: 1;
    }
    50% {
      opacity: 0.7;
    }
  }

  /* Error State Customizations */
  .error-state {
    animation: errorShake 0.5s ease-in-out;
  }

  @keyframes errorShake {
    0%,
    100% {
      transform: translateX(0);
    }
    25% {
      transform: translateX(-5px);
    }
    75% {
      transform: translateX(5px);
    }
  }

  /* Empty State Enhancements */
  .empty-state {
    animation: emptyFloat 3s ease-in-out infinite;
  }

  @keyframes emptyFloat {
    0%,
    100% {
      transform: translateY(0px);
    }
    50% {
      transform: translateY(-10px);
    }
  }

  /* Prediction Cards Animations */
  .prediction-card {
    animation: slideInRight 0.5s ease-out forwards;
    opacity: 0;
    transform: translateX(30px);
  }

  .prediction-card:nth-child(1) {
    animation-delay: 0.1s;
  }
  .prediction-card:nth-child(2) {
    animation-delay: 0.2s;
  }
  .prediction-card:nth-child(3) {
    animation-delay: 0.3s;
  }

  @keyframes slideInRight {
    to {
      opacity: 1;
      transform: translateX(0);
    }
  }

  /* Enhanced Focus States */
  .view-btn:focus,
  .action-btn:focus,
  .view-selector:focus,
  .comparison-selector:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.3);
    transform: translateY(-1px);
  }

  /* Accessibility Enhancements */
  @media (prefers-reduced-motion: reduce) {
    .metric-card,
    .chart-container,
    .insight-card,
    .prediction-card,
    .loading-state,
    .error-state,
    .empty-state {
      animation: none;
      transition: none;
    }

    .metric-card:hover {
      transform: none;
    }
  }

  /* High contrast mode support */
  @media (prefers-contrast: high) {
    .metric-card,
    .chart-container,
    .insight-card,
    .comparison-section,
    .predictions-section {
      border: 2px solid;
    }

    .action-btn {
      border: 2px solid;
    }
  }

  /* Metrics Overview */
  .metrics-overview {
    margin-bottom: 3rem;
  }

  .metrics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
  }

  .metric-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex;
    align-items: center;
    gap: 1.5rem;
    position: relative;
    overflow: hidden;
  }

  .metric-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
    transition: left 0.6s;
  }

  .metric-card:hover::before {
    left: 100%;
  }

  .metric-card:hover {
    transform: translateY(-8px) scale(1.02);
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
  }

  .metric-icon {
    width: 64px;
    height: 64px;
    border-radius: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    box-shadow: 0 8px 24px rgba(102, 126, 234, 0.3);
    position: relative;
  }

  .metric-icon::after {
    content: '';
    position: absolute;
    inset: 0;
    border-radius: 18px;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.2) 0%, transparent 50%);
  }

  .metric-content {
    flex: 1;
  }

  .metric-value {
    font-size: 2.25rem;
    font-weight: 800;
    color: #1f2937;
    margin-bottom: 0.5rem;
    background: linear-gradient(135deg, #1f2937 0%, #374151 100%);
    background-clip: text;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
  }

  .metric-label {
    color: #6b7280;
    font-size: 0.875rem;
    font-weight: 600;
    margin-bottom: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
  }

  .metric-trend {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    font-weight: 700;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    width: fit-content;
  }

  .metric-trend.increase {
    color: #059669;
    background: rgba(16, 185, 129, 0.1);
    border: 1px solid rgba(16, 185, 129, 0.2);
  }

  .metric-trend.decrease {
    color: #dc2626;
    background: rgba(239, 68, 68, 0.1);
    border: 1px solid rgba(239, 68, 68, 0.2);
  }

  .metric-detail {
    color: #6b7280;
    font-size: 0.875rem;
    font-weight: 500;
  }

  /* Advanced Charts */
  .advanced-charts {
    margin-bottom: 3rem;
  }

  .charts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
    gap: 2rem;
  }

  .chart-container {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    border-radius: 24px;
    padding: 2rem;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
  }

  .chart-container::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 2px;
    background: linear-gradient(90deg, #667eea, #764ba2, #667eea);
    animation: shimmer 3s ease-in-out infinite;
  }

  @keyframes shimmer {
    0%,
    100% {
      opacity: 0.3;
    }
    50% {
      opacity: 0.8;
    }
  }

  .chart-container:hover {
    transform: translateY(-4px);
    box-shadow: 0 16px 48px rgba(0, 0, 0, 0.15);
  }

  .chart-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    flex-wrap: wrap;
    gap: 1rem;
  }

  .chart-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1f2937;
    display: flex;
    align-items: center;
    gap: 0.75rem;
  }

  .chart-title i {
    width: 36px;
    height: 36px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1rem;
    box-shadow: 0 4px 16px rgba(102, 126, 234, 0.3);
  }

  .chart-controls {
    display: flex;
    gap: 0.5rem;
    align-items: center;
  }

  .view-selector {
    padding: 0.75rem 1rem;
    border: 1px solid rgba(102, 126, 234, 0.3);
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.9);
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
  }

  .view-selector:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
  }

  .view-btn {
    padding: 0.5rem 1rem;
    border: 1px solid rgba(102, 126, 234, 0.3);
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.9);
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
    color: #6b7280;
  }

  .view-btn.active {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    border-color: transparent;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
  }

  .view-btn:hover:not(.active) {
    background: rgba(102, 126, 234, 0.1);
    color: #667eea;
  }

  /* Insights Section */
  .insights-section {
    margin-bottom: 3rem;
  }

  .insights-header {
    text-align: center;
    margin-bottom: 2rem;
  }

  .section-title {
    font-size: 2.25rem;
    font-weight: 800;
    color: white;
    margin-bottom: 0.5rem;
    text-shadow: 0 4px 16px rgba(0, 0, 0, 0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1rem;
  }

  .section-subtitle {
    color: rgba(255, 255, 255, 0.9);
    font-size: 1.125rem;
    font-weight: 500;
  }

  .insights-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 1.5rem;
  }

  .insight-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    display: flex;
    gap: 1.5rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
  }

  .insight-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: linear-gradient(135deg, #667eea, #764ba2);
    transition: width 0.3s ease;
  }

  .insight-card:hover::before {
    width: 100%;
    opacity: 0.05;
  }

  .insight-card:hover {
    transform: translateY(-4px) scale(1.02);
    box-shadow: 0 16px 48px rgba(0, 0, 0, 0.15);
  }

  .insight-card.warning::before {
    background: linear-gradient(135deg, #f59e0b, #ef4444);
  }

  .insight-card.success::before {
    background: linear-gradient(135deg, #10b981, #059669);
  }

  .insight-card.info::before {
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
  }

  .insight-card.tip::before {
    background: linear-gradient(135deg, #8b5cf6, #7c3aed);
  }

  .insight-icon {
    font-size: 2.5rem;
    flex-shrink: 0;
    width: 64px;
    height: 64px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 16px;
    background: rgba(102, 126, 234, 0.1);
  }

  .insight-content {
    flex: 1;
  }

  .insight-title {
    font-size: 1.25rem;
    font-weight: 700;
    margin-bottom: 0.75rem;
    color: #1f2937;
  }

  .insight-description {
    color: #6b7280;
    margin-bottom: 1.5rem;
    line-height: 1.6;
    font-size: 0.95rem;
  }

  .action-btn {
    padding: 0.75rem 1.5rem;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 0.875rem;
    font-weight: 600;
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

  .action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(102, 126, 234, 0.4);
  }

  /* Comparison Section */
  .comparison-section {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    border-radius: 24px;
    padding: 2rem;
    margin-bottom: 3rem;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
  }

  .comparison-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    flex-wrap: wrap;
    gap: 1rem;
  }

  .comparison-selector {
    padding: 0.75rem 1.25rem;
    border: 1px solid rgba(102, 126, 234, 0.3);
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.9);
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
    min-width: 200px;
  }

  .comparison-selector:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
  }

  /* Predictions Section */
  .predictions-section {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    border-radius: 24px;
    padding: 2rem;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    margin-bottom: 2rem;
  }

  .predictions-header {
    text-align: center;
    margin-bottom: 2rem;
  }

  .predictions-content {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 2rem;
    align-items: start;
  }

  .prediction-summary {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
  }

  .prediction-card {
    background: rgba(102, 126, 234, 0.05);
    border: 1px solid rgba(102, 126, 234, 0.2);
    border-radius: 16px;
    padding: 2rem;
    text-align: center;
    transition: all 0.3s ease;
  }

  .prediction-card:hover {
    background: rgba(102, 126, 234, 0.08);
    transform: translateY(-2px);
  }

  .prediction-card h4 {
    color: #1f2937;
    margin-bottom: 1rem;
    font-weight: 700;
    font-size: 1.1rem;
  }

  .projection-value {
    font-size: 2rem;
    font-weight: 800;
    color: #667eea;
    margin-bottom: 0.5rem;
  }

  .projection-confidence {
    color: #6b7280;
    font-size: 0.875rem;
    font-weight: 500;
  }

  .velocity-indicator,
  .health-indicator {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    align-items: center;
  }

  .velocity-value,
  .health-score {
    font-size: 2rem;
    font-weight: 800;
  }

  .velocity-indicator.high .velocity-value {
    color: #ef4444;
  }

  .health-indicator.good .health-score {
    color: #10b981;
  }

  .velocity-label,
  .health-status {
    color: #6b7280;
    font-size: 0.875rem;
    font-weight: 500;
  }

  /* Legacy Widgets Enhancement */
  .legacy-widgets {
    margin-top: 2rem;
  }

  /* Modern Header Elements */
  .modern-back-btn {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1.25rem;
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 16px;
    color: #64748b;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
  }

  .modern-back-btn:hover {
    background: rgba(255, 255, 255, 1);
    color: #334155;
    transform: translateY(-2px);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
    border-color: rgba(255, 255, 255, 0.4);
  }

  .modern-select-wrapper {
    position: relative;
    display: inline-block;
  }

  .modern-select {
    appearance: none;
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 16px;
    padding: 0.75rem 3rem 0.75rem 1.25rem;
    font-size: 0.875rem;
    font-weight: 500;
    color: #334155;
    min-width: 160px;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
  }

  .modern-select:hover,
  .modern-select:focus {
    background: rgba(255, 255, 255, 1);
    border-color: rgba(255, 255, 255, 0.4);
    outline: none;
    transform: translateY(-2px);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
  }

  .select-arrow {
    position: absolute;
    right: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #64748b;
    font-size: 0.75rem;
    pointer-events: none;
    transition: transform 0.3s ease;
  }

  .modern-select:focus + .select-arrow {
    transform: translateY(-50%) rotate(180deg);
  }

  .modern-refresh-btn {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1.25rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    border-radius: 16px;
    color: white;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 16px rgba(102, 126, 234, 0.3);
    position: relative;
    overflow: hidden;
  }

  .modern-refresh-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
  }

  .modern-refresh-btn:hover::before {
    left: 100%;
  }

  .modern-refresh-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 32px rgba(102, 126, 234, 0.4);
  }

  .modern-refresh-btn:disabled {
    opacity: 0.7;
    cursor: not-allowed;
    transform: none;
  }

  .modern-refresh-btn.loading {
    background: linear-gradient(135deg, #94a3b8 0%, #64748b 100%);
  }

  .btn {
    display: inline-flex;
    align-items: center;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.2s ease;
    border: 1px solid transparent;
  }

  .btn-outline-primary {
    color: #3b82f6;
    border-color: #3b82f6;
    background: white;
  }

  .btn-outline-primary:hover {
    background: #3b82f6;
    color: white;
  }

  .btn-outline-primary:disabled {
    opacity: 0.5;
    cursor: not-allowed;
  }

  /* Legacy button styles - keeping for compatibility */
  .back-btn {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    color: #6c5ce7 !important;
    border: 2px solid #6c5ce7 !important;
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(10px);
    border-radius: 16px;
    padding: 0.75rem 1.25rem;
    position: relative;
    overflow: hidden;
    box-shadow: 0 4px 16px rgba(108, 92, 231, 0.15);
  }

  .back-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
  }

  .back-btn:hover::before {
    left: 100%;
  }

  .back-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 32px rgba(108, 92, 231, 0.3);
    background: linear-gradient(135deg, #6c5ce7 0%, #a29bfe 100%) !important;
    color: white !important;
    border-color: #6c5ce7 !important;
  }

  .loading-container {
    text-align: center;
    padding: 4rem 2rem;
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(20px);
    border-radius: 24px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.06);
  }

  .loading-container .spinner-border {
    width: 3rem;
    height: 3rem;
    border-width: 4px;
    border-color: #667eea;
    border-right-color: transparent;
    animation: spin 1s linear infinite;
  }

  @keyframes spin {
    0% {
      transform: rotate(0deg);
    }
    100% {
      transform: rotate(360deg);
    }
  }

  .loading-container p {
    margin-top: 1.5rem;
    color: #64748b;
    font-weight: 500;
    font-size: 1.1rem;
  }

  .dashboard-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 2rem;
    margin-bottom: 2rem;
  }

  .dashboard-card {
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 24px;
    box-shadow:
      0 8px 32px rgba(0, 0, 0, 0.06),
      0 1px 0 rgba(255, 255, 255, 0.8);
    overflow: hidden;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
  }

  .dashboard-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 1px;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.8), transparent);
  }

  .dashboard-card:hover {
    transform: translateY(-8px) scale(1.02);
    box-shadow:
      0 20px 60px rgba(0, 0, 0, 0.12),
      0 8px 32px rgba(0, 0, 0, 0.08),
      0 1px 0 rgba(255, 255, 255, 0.9);
    border-color: rgba(255, 255, 255, 0.4);
  }

  .dashboard-card.large {
    grid-column: span 2;
  }

  .card-header {
    padding: 2rem 2rem 1rem;
    border-bottom: none;
    background: transparent;
    position: relative;
  }

  .card-header::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 2rem;
    right: 2rem;
    height: 1px;
    background: linear-gradient(
      90deg,
      transparent,
      rgba(102, 126, 234, 0.3) 20%,
      rgba(118, 75, 162, 0.3) 80%,
      transparent
    );
  }

  .card-title {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 700;
    color: #1a202c;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    letter-spacing: -0.025em;
  }

  .card-title i {
    width: 32px;
    height: 32px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 0.9rem;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
  }

  .card-body {
    padding: 1rem 2rem 2rem;
    min-height: 300px;
    position: relative;
  }

  .dashboard-card.large .card-body {
    min-height: 400px;
  }

  .card-body::before {
    content: '';
    position: absolute;
    top: 0;
    left: 50%;
    width: 60%;
    height: 1px;
    background: linear-gradient(90deg, transparent, rgba(0, 0, 0, 0.05), transparent);
    transform: translateX(-50%);
  }

  .empty-state {
    text-align: center;
    padding: 4rem 2rem;
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 24px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.06);
    position: relative;
    overflow: hidden;
  }

  .empty-state::before {
    content: '';
    position: absolute;
    top: -2px;
    left: -2px;
    right: -2px;
    bottom: -2px;
    background: linear-gradient(45deg, #667eea, #764ba2, #667eea);
    border-radius: 24px;
    z-index: -1;
    animation: borderGlow 3s ease-in-out infinite;
  }

  @keyframes borderGlow {
    0%,
    100% {
      opacity: 0.3;
    }
    50% {
      opacity: 0.6;
    }
  }

  .empty-icon {
    font-size: 5rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 2rem;
    display: inline-block;
    animation: float 3s ease-in-out infinite;
  }

  @keyframes float {
    0%,
    100% {
      transform: translateY(0px);
    }
    50% {
      transform: translateY(-10px);
    }
  }

  .empty-state h3 {
    color: #1a202c;
    margin-bottom: 1rem;
    font-weight: 700;
    font-size: 1.5rem;
    letter-spacing: -0.025em;
  }

  .empty-state p {
    color: #64748b;
    font-size: 1.1rem;
    margin-bottom: 2rem;
    line-height: 1.6;
  }

  /* Modern Animations */
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

  .dashboard-card {
    animation: fadeInUp 0.6s ease-out forwards;
  }

  .dashboard-card:nth-child(1) {
    animation-delay: 0.1s;
  }
  .dashboard-card:nth-child(2) {
    animation-delay: 0.2s;
  }
  .dashboard-card:nth-child(3) {
    animation-delay: 0.3s;
  }
  .dashboard-card:nth-child(4) {
    animation-delay: 0.4s;
  }
  .dashboard-card:nth-child(5) {
    animation-delay: 0.5s;
  }
  .dashboard-card:nth-child(6) {
    animation-delay: 0.6s;
  }

  /* Mobile-specific enhancements */

  /* Touch-optimized elements */
  .touch-optimized {
    -webkit-tap-highlight-color: rgba(102, 126, 234, 0.3);
  }

  .mobile-optimized {
    font-size: 1rem;
    padding: 1rem 1.25rem;
    border-radius: 12px;
    min-height: 48px;
  }

  /* Swipe indicator styles */
  .swipe-indicator {
    margin-top: 0.75rem;
    text-align: center;
    animation: fadeInUp 0.5s ease-out 2s both;
  }

  .swipe-hint {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    color: rgba(255, 255, 255, 0.8);
    font-size: 0.75rem;
    font-weight: 500;
    margin-bottom: 0.5rem;
  }

  .swipe-hint i {
    animation: wiggle 2s ease-in-out infinite;
  }

  @keyframes wiggle {
    0%,
    100% {
      transform: rotate(0deg);
    }
    25% {
      transform: rotate(-10deg);
    }
    75% {
      transform: rotate(10deg);
    }
  }

  .swipe-dots {
    display: flex;
    justify-content: center;
    gap: 0.5rem;
  }

  .dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.4);
    transition: all 0.3s ease;
  }

  .dot.active {
    background: white;
    transform: scale(1.2);
    box-shadow: 0 0 8px rgba(255, 255, 255, 0.5);
  }

  /* Touch feedback animations */
  .view-btn.touch-optimized:active,
  .action-btn:active,
  .modern-refresh-btn:active,
  .modern-back-btn:active {
    transform: scale(0.95);
    transition: transform 0.1s ease;
  }

  /* Mobile loading optimizations */
  .loading-state {
    padding: 1rem;
  }

  .loading-grid {
    gap: 1.5rem;
  }

  .loading-overlay {
    padding: 2rem;
    border-radius: 20px;
  }

  /* Pull-to-refresh indicator (visual only) */
  .refresh-indicator {
    position: absolute;
    top: -60px;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    border-radius: 30px;
    padding: 1rem 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 0.875rem;
    font-weight: 500;
    color: #667eea;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    z-index: 1000;
  }

  .refresh-indicator.active {
    top: 20px;
  }

  .refresh-indicator i {
    animation: spin 1s linear infinite;
  }

  /* Progressive Web App optimizations */
  @media (display-mode: standalone) {
    .min-h-screen {
      min-height: 100vh;
      min-height: -webkit-fill-available;
    }

    /* Account for notches and safe areas */
    .py-6 {
      padding-top: max(1.5rem, env(safe-area-inset-top));
      padding-bottom: max(1.5rem, env(safe-area-inset-bottom));
    }

    .px-4 {
      padding-left: max(1rem, env(safe-area-inset-left));
      padding-right: max(1rem, env(safe-area-inset-right));
    }
  }

  /* Landscape orientation optimizations */
  @media (max-height: 500px) and (orientation: landscape) {
    .py-6 {
      padding-top: 0.75rem;
      padding-bottom: 0.75rem;
    }

    .mb-12 {
      margin-bottom: 1rem;
    }

    .w-14.h-14 {
      width: 2.5rem;
      height: 2.5rem;
    }

    .mb-12 .flex-1 h1 {
      font-size: 1.5rem;
    }

    .mb-12 .flex-1 p {
      font-size: 0.875rem;
    }

    .metrics-grid {
      grid-template-columns: repeat(4, 1fr);
      gap: 0.75rem;
    }

    .metric-card {
      padding: 0.75rem;
    }

    .metric-value {
      font-size: 1.25rem;
    }

    .metric-label {
      font-size: 0.7rem;
    }

    .chart-container {
      padding: 1rem;
    }

    .card-body {
      min-height: 150px;
    }
  }

  /* High DPI display optimizations */
  @media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
    .metric-icon,
    .insight-icon,
    .chart-title i,
    .card-title i {
      image-rendering: -webkit-optimize-contrast;
      image-rendering: crisp-edges;
    }
  }

  /* Accessibility enhancements for mobile */
  @media (max-width: 768px) {
    /* Larger focus indicators for touch */
    .view-btn:focus,
    .action-btn:focus,
    .modern-refresh-btn:focus,
    .modern-back-btn:focus,
    .modern-select:focus,
    .comparison-selector:focus {
      outline: 3px solid rgba(102, 126, 234, 0.6);
      outline-offset: 3px;
    }

    /* Better text contrast for small screens */
    .metric-label,
    .insight-description,
    .section-subtitle,
    .projection-confidence {
      color: #4b5563;
    }

    /* Skip links for mobile screen readers */
    .skip-link {
      position: absolute;
      top: -40px;
      left: 6px;
      background: #667eea;
      color: white;
      padding: 8px;
      text-decoration: none;
      border-radius: 4px;
      z-index: 1000;
      transition: top 0.3s;
    }

    .skip-link:focus {
      top: 6px;
    }
  }

  /* Performance optimizations for mobile */
  @media (max-width: 768px) {
    /* Reduce animations on slower devices */
    .metric-card,
    .chart-container,
    .insight-card,
    .prediction-card {
      animation-duration: 0.3s;
    }

    /* Optimize transforms for mobile GPUs */
    .metric-card,
    .chart-container,
    .insight-card,
    .dashboard-card {
      transform: translateZ(0);
      backface-visibility: hidden;
      perspective: 1000;
    }

    /* Reduce blur effects on mobile for performance */
    .dashboard-card,
    .metric-card,
    .insight-card,
    .chart-container {
      backdrop-filter: blur(10px);
    }
  }

  /* Dark mode mobile optimizations */
  @media (prefers-color-scheme: dark) and (max-width: 768px) {
    .swipe-hint {
      color: rgba(255, 255, 255, 0.9);
    }

    .dot {
      background: rgba(255, 255, 255, 0.5);
    }

    .dot.active {
      background: #667eea;
    }

    .refresh-indicator {
      background: rgba(30, 41, 59, 0.95);
      color: #94a3b8;
    }
  }

  /* Mobile-First Responsive Design */

  /* Touch Optimizations */
  @media (hover: none) and (pointer: coarse) {
    /* Touch-friendly hover states */
    .metric-card:hover,
    .insight-card:hover,
    .chart-container:hover,
    .dashboard-card:hover {
      transform: none;
      box-shadow: inherit;
    }

    /* Larger touch targets */
    .action-btn,
    .view-btn,
    .modern-refresh-btn,
    .modern-back-btn {
      min-height: 48px;
      min-width: 48px;
      padding: 0.875rem 1.5rem;
    }

    /* Remove hover animations for touch devices */
    .metric-card::before,
    .insight-card::before,
    .chart-container::before,
    .modern-refresh-btn::before,
    .modern-back-btn::before {
      display: none;
    }

    /* Focus states for accessibility */
    .action-btn:focus,
    .view-btn:focus,
    .modern-refresh-btn:focus,
    .modern-back-btn:focus {
      outline: 3px solid rgba(102, 126, 234, 0.5);
      outline-offset: 2px;
    }
  }

  /* Large Desktop (1440px+) */
  @media (min-width: 1440px) {
    .max-w-7xl {
      max-width: 1400px;
    }

    .metrics-grid {
      grid-template-columns: repeat(4, 1fr);
      gap: 2rem;
    }

    .charts-grid {
      grid-template-columns: repeat(2, 1fr);
      gap: 2.5rem;
    }

    .insights-grid {
      grid-template-columns: repeat(4, 1fr);
      gap: 2rem;
    }

    .predictions-content {
      grid-template-columns: 2.5fr 1fr;
      gap: 3rem;
    }
  }

  /* Desktop (1200px - 1439px) */
  @media (max-width: 1439px) and (min-width: 1200px) {
    .metrics-grid {
      grid-template-columns: repeat(4, 1fr);
      gap: 1.5rem;
    }

    .charts-grid {
      grid-template-columns: repeat(2, 1fr);
      gap: 2rem;
    }

    .insights-grid {
      grid-template-columns: repeat(3, 1fr);
      gap: 1.5rem;
    }
  }

  /* Medium Desktop/Laptop (1024px - 1199px) */
  @media (max-width: 1199px) and (min-width: 1024px) {
    .metrics-grid {
      grid-template-columns: repeat(4, 1fr);
      gap: 1.25rem;
    }

    .charts-grid {
      grid-template-columns: repeat(2, 1fr);
      gap: 1.5rem;
    }

    .insights-grid {
      grid-template-columns: repeat(2, 1fr);
      gap: 1.5rem;
    }

    .predictions-content {
      grid-template-columns: 1fr;
      gap: 2rem;
    }

    .prediction-summary {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 1rem;
    }
  }

  /* Small Laptop/Large Tablet (769px - 1023px) */
  @media (max-width: 1023px) and (min-width: 769px) {
    .metrics-grid {
      grid-template-columns: repeat(2, 1fr);
      gap: 1.25rem;
    }

    .charts-grid {
      grid-template-columns: 1fr;
      gap: 1.5rem;
    }

    .insights-grid {
      grid-template-columns: repeat(2, 1fr);
      gap: 1.25rem;
    }

    .chart-header {
      flex-direction: column;
      gap: 1rem;
      align-items: stretch;
    }

    .chart-controls {
      flex-direction: column;
      gap: 0.75rem;
    }

    .view-btn {
      flex: 1;
      text-align: center;
    }

    .comparison-header {
      flex-direction: column;
      gap: 1rem;
    }

    .comparison-selector {
      width: 100%;
    }
  }

  /* Tablet Portrait (481px - 768px) */
  @media (max-width: 768px) and (min-width: 481px) {
    /* Main layout adjustments */
    .py-6 {
      padding-top: 1.5rem;
      padding-bottom: 1.5rem;
    }

    .px-4 {
      padding-left: 1rem;
      padding-right: 1rem;
    }

    /* Header adjustments */
    .mb-12 {
      margin-bottom: 2rem;
    }

    .mb-12 .flex {
      flex-direction: column;
      align-items: flex-start;
      gap: 1.5rem;
    }

    .w-14.h-14 {
      width: 3.5rem;
      height: 3.5rem;
    }

    .mb-12 .flex-1 h1 {
      font-size: 2.25rem;
      line-height: 1.2;
    }

    .mb-12 .flex-1 p {
      font-size: 1rem;
      line-height: 1.5;
    }

    /* Controls layout */
    .flex.items-center.gap-4:last-child {
      flex-direction: row;
      flex-wrap: wrap;
      gap: 0.75rem;
      width: 100%;
    }

    .modern-back-btn {
      flex: 1;
      min-width: 140px;
    }

    .modern-select-wrapper {
      flex: 1;
      min-width: 160px;
    }

    .modern-select {
      width: 100%;
    }

    .modern-refresh-btn {
      flex: 1;
      min-width: 120px;
    }

    /* Metrics adjustments */
    .metrics-grid {
      grid-template-columns: repeat(2, 1fr);
      gap: 1rem;
    }

    .metric-card {
      padding: 1.5rem;
      flex-direction: row;
      text-align: left;
      gap: 1rem;
    }

    .metric-icon {
      width: 48px;
      height: 48px;
      border-radius: 14px;
      font-size: 1.5rem;
    }

    .metric-value {
      font-size: 1.75rem;
      margin-bottom: 0.25rem;
    }

    .metric-label {
      font-size: 0.8rem;
      margin-bottom: 0.5rem;
    }

    /* Charts adjustments */
    .charts-grid {
      grid-template-columns: 1fr;
      gap: 1.5rem;
    }

    .chart-container {
      padding: 1.5rem;
    }

    .chart-header {
      flex-direction: column;
      gap: 1rem;
      margin-bottom: 1.5rem;
    }

    .chart-title {
      font-size: 1.25rem;
    }

    .chart-title i {
      width: 28px;
      height: 28px;
      font-size: 0.85rem;
    }

    .chart-controls {
      flex-direction: column;
      gap: 0.5rem;
    }

    .view-btn {
      padding: 0.5rem 1rem;
      font-size: 0.8rem;
    }

    /* Insights adjustments */
    .insights-grid {
      grid-template-columns: 1fr;
      gap: 1rem;
    }

    .insight-card {
      padding: 1.5rem;
      flex-direction: column;
      text-align: center;
      gap: 1rem;
    }

    .insight-icon {
      width: 48px;
      height: 48px;
      font-size: 1.5rem;
      align-self: center;
    }

    .insight-title {
      font-size: 1.1rem;
    }

    .insight-description {
      font-size: 0.9rem;
    }

    /* Section adjustments */
    .section-title {
      font-size: 1.875rem;
      flex-direction: row;
      gap: 0.75rem;
      justify-content: center;
    }

    .section-subtitle {
      font-size: 1rem;
      text-align: center;
    }

    /* Comparison section */
    .comparison-header {
      flex-direction: column;
      gap: 1rem;
    }

    .comparison-selector {
      width: 100%;
      padding: 0.875rem 1.25rem;
    }

    /* Predictions adjustments */
    .predictions-content {
      grid-template-columns: 1fr;
      gap: 1.5rem;
    }

    .prediction-summary {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 1rem;
    }

    .prediction-card {
      padding: 1.25rem;
    }

    .prediction-card h4 {
      font-size: 0.9rem;
      margin-bottom: 0.75rem;
    }

    .projection-value,
    .velocity-value,
    .health-score {
      font-size: 1.5rem;
    }

    /* Dashboard grid */
    .dashboard-grid {
      grid-template-columns: 1fr;
      gap: 1.5rem;
    }

    .dashboard-card.large {
      grid-column: span 1;
    }

    .card-header {
      padding: 1.5rem 1.5rem 1rem;
    }

    .card-body {
      padding: 1rem 1.5rem 1.5rem;
      min-height: 280px;
    }

    .dashboard-card.large .card-body {
      min-height: 350px;
    }
  }

  /* Mobile Landscape & Large Mobile (376px - 480px) */
  @media (max-width: 480px) and (min-width: 376px) {
    /* Reduce spacing */
    .py-6 {
      padding-top: 1rem;
      padding-bottom: 1rem;
    }

    .px-4 {
      padding-left: 0.75rem;
      padding-right: 0.75rem;
    }

    /* Header mobile optimization */
    .mb-12 {
      margin-bottom: 1.5rem;
    }

    .w-14.h-14 {
      width: 3rem;
      height: 3rem;
    }

    .w-14.h-14 i {
      font-size: 1rem;
    }

    .mb-12 .flex-1 h1 {
      font-size: 1.875rem;
      line-height: 1.1;
    }

    .mb-12 .flex-1 p {
      font-size: 0.9rem;
      line-height: 1.4;
    }

    /* Controls stacking */
    .flex.items-center.gap-4:last-child {
      flex-direction: column;
      gap: 0.75rem;
      width: 100%;
    }

    .modern-back-btn,
    .modern-select,
    .modern-refresh-btn {
      width: 100%;
      justify-content: center;
      padding: 0.75rem 1rem;
    }

    /* Single column metrics */
    .metrics-grid {
      grid-template-columns: 1fr;
      gap: 0.75rem;
    }

    .metric-card {
      padding: 1.25rem;
      flex-direction: row;
      gap: 1rem;
    }

    .metric-icon {
      width: 44px;
      height: 44px;
      font-size: 1.25rem;
      border-radius: 12px;
    }

    .metric-value {
      font-size: 1.5rem;
    }

    .metric-label {
      font-size: 0.75rem;
    }

    .metric-trend {
      font-size: 0.75rem;
      padding: 0.25rem 0.5rem;
    }

    /* Chart optimizations */
    .chart-container {
      padding: 1.25rem;
    }

    .chart-title {
      font-size: 1.1rem;
      flex-direction: column;
      gap: 0.5rem;
      text-align: center;
    }

    .chart-title i {
      width: 32px;
      height: 32px;
      font-size: 0.8rem;
      align-self: center;
    }

    /* Insights mobile */
    .insight-card {
      padding: 1.25rem;
    }

    .insight-icon {
      width: 44px;
      height: 44px;
      font-size: 1.25rem;
    }

    .insight-title {
      font-size: 1rem;
    }

    .insight-description {
      font-size: 0.85rem;
    }

    .action-btn {
      padding: 0.75rem 1.25rem;
      font-size: 0.8rem;
    }

    /* Section titles */
    .section-title {
      font-size: 1.625rem;
      flex-direction: column;
      gap: 0.5rem;
      text-align: center;
    }

    .section-subtitle {
      font-size: 0.9rem;
    }

    /* Predictions mobile */
    .prediction-summary {
      grid-template-columns: 1fr;
      gap: 0.75rem;
    }

    .prediction-card {
      padding: 1rem;
    }

    .prediction-card h4 {
      font-size: 0.85rem;
    }

    .projection-value,
    .velocity-value,
    .health-score {
      font-size: 1.25rem;
    }

    .projection-confidence {
      font-size: 0.75rem;
    }

    /* Card adjustments */
    .card-header {
      padding: 1.25rem 1.25rem 0.75rem;
    }

    .card-body {
      padding: 0.75rem 1.25rem 1.25rem;
      min-height: 250px;
    }

    .dashboard-card.large .card-body {
      min-height: 300px;
    }

    .card-title {
      font-size: 1rem;
      flex-direction: column;
      gap: 0.5rem;
      text-align: center;
    }

    .card-title i {
      width: 28px;
      height: 28px;
      font-size: 0.75rem;
      align-self: center;
    }
  }

  /* Small Mobile (= 375px) */
  @media (max-width: 375px) {
    /* Minimal spacing */
    .py-6 {
      padding-top: 0.75rem;
      padding-bottom: 0.75rem;
    }

    .px-4 {
      padding-left: 0.5rem;
      padding-right: 0.5rem;
    }

    /* Compact header */
    .mb-12 {
      margin-bottom: 1rem;
    }

    .w-14.h-14 {
      width: 2.5rem;
      height: 2.5rem;
    }

    .w-14.h-14 i {
      font-size: 0.875rem;
    }

    .mb-12 .flex-1 h1 {
      font-size: 1.5rem;
      line-height: 1.1;
    }

    .mb-12 .flex-1 p {
      font-size: 0.8rem;
      line-height: 1.3;
    }

    /* Compact controls */
    .modern-back-btn,
    .modern-select,
    .modern-refresh-btn {
      padding: 0.625rem 0.875rem;
      font-size: 0.8rem;
    }

    /* Ultra compact metrics */
    .metrics-grid {
      gap: 0.5rem;
    }

    .metric-card {
      padding: 1rem;
      flex-direction: column;
      text-align: center;
      gap: 0.75rem;
    }

    .metric-icon {
      width: 40px;
      height: 40px;
      font-size: 1rem;
      align-self: center;
    }

    .metric-value {
      font-size: 1.25rem;
      margin-bottom: 0.25rem;
    }

    .metric-label {
      font-size: 0.7rem;
      margin-bottom: 0.25rem;
    }

    .metric-trend {
      font-size: 0.7rem;
      padding: 0.125rem 0.375rem;
      align-self: center;
    }

    /* Compact charts */
    .charts-grid {
      gap: 1rem;
    }

    .chart-container {
      padding: 1rem;
    }

    .chart-header {
      margin-bottom: 1rem;
    }

    .chart-title {
      font-size: 1rem;
    }

    .chart-title i {
      width: 24px;
      height: 24px;
      font-size: 0.7rem;
    }

    .view-btn {
      padding: 0.375rem 0.75rem;
      font-size: 0.75rem;
    }

    /* Compact insights */
    .insights-grid {
      gap: 0.75rem;
    }

    .insight-card {
      padding: 1rem;
    }

    .insight-icon {
      width: 36px;
      height: 36px;
      font-size: 1rem;
    }

    .insight-title {
      font-size: 0.9rem;
      margin-bottom: 0.5rem;
    }

    .insight-description {
      font-size: 0.8rem;
      margin-bottom: 1rem;
    }

    .action-btn {
      padding: 0.625rem 1rem;
      font-size: 0.75rem;
    }

    /* Compact sections */
    .section-title {
      font-size: 1.375rem;
    }

    .section-subtitle {
      font-size: 0.8rem;
    }

    /* Compact predictions */
    .prediction-card {
      padding: 0.875rem;
    }

    .prediction-card h4 {
      font-size: 0.8rem;
      margin-bottom: 0.5rem;
    }

    .projection-value,
    .velocity-value,
    .health-score {
      font-size: 1.125rem;
      margin-bottom: 0.25rem;
    }

    .projection-confidence {
      font-size: 0.7rem;
    }

    /* Ultra compact cards */
    .card-header {
      padding: 1rem 1rem 0.5rem;
    }

    .card-body {
      padding: 0.5rem 1rem 1rem;
      min-height: 200px;
    }

    .dashboard-card.large .card-body {
      min-height: 250px;
    }

    .card-title {
      font-size: 0.9rem;
    }

    .card-title i {
      width: 24px;
      height: 24px;
      font-size: 0.7rem;
    }
  }

  @media (max-width: 480px) {
    .w-14.h-14 {
      width: 3rem;
      height: 3rem;
    }

    .w-14.h-14 i {
      font-size: 1rem;
    }

    .mb-12 .flex-1 h1 {
      font-size: 1.75rem;
    }

    .card-header {
      padding: 1.5rem 1.5rem 0.75rem;
    }

    .card-body {
      padding: 0.75rem 1.5rem 1.5rem;
      min-height: 250px;
    }

    .dashboard-card.large .card-body {
      min-height: 300px;
    }

    .card-title {
      font-size: 1.1rem;
    }

    .card-title i {
      width: 28px;
      height: 28px;
      font-size: 0.8rem;
    }
  }

  /* Dark mode support (future enhancement) */
  @media (prefers-color-scheme: dark) {
    .dashboard-card {
      background: rgba(30, 41, 59, 0.8);
      border-color: rgba(71, 85, 105, 0.3);
    }

    .card-title {
      color: #f1f5f9;
    }

    .modern-back-btn,
    .modern-select {
      background: rgba(30, 41, 59, 0.9);
      color: #cbd5e1;
      border-color: rgba(71, 85, 105, 0.3);
    }
  }
</style>
