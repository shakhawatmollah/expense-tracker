<template>
  <div class="analytics-dashboard">
    <!-- Header -->
    <div class="dashboard-header">
      <div class="header-content">
        <h1 class="page-title">
          <i class="fas fa-chart-line"></i>
          Advanced Analytics
        </h1>
        <p class="page-subtitle">
          Get insights into your spending patterns and financial health
        </p>
      </div>
      
      <div class="header-actions">
        <!-- Period Selector -->
        <div class="period-selector">
          <select v-model="selectedPeriod" @change="onPeriodChange" class="form-select">
            <option value="weekly">Weekly</option>
            <option value="monthly">Monthly</option>
            <option value="quarterly">Quarterly</option>
            <option value="yearly">Yearly</option>
          </select>
        </div>
        
        <!-- Refresh Button -->
        <button 
          @click="refreshAnalytics" 
          :disabled="loading" 
          class="btn btn-outline-primary"
        >
          <i class="fas fa-sync-alt" :class="{ 'fa-spin': loading }"></i>
          Refresh
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <template v-if="loading && !dashboardData">
      <div class="loading-container">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading analytics...</span>
        </div>
        <p class="mt-3">Analyzing your financial data...</p>
      </div>
    </template>

    <!-- Error State -->
    <template v-else-if="error">
      <div class="alert alert-danger" role="alert">
        <i class="fas fa-exclamation-triangle"></i>
        <strong>Error:</strong> {{ error }}
        <button @click="loadAnalytics" class="btn btn-sm btn-outline-danger ms-2">
          Try Again
        </button>
      </div>
    </template>

    <!-- Dashboard Content -->
    <template v-else>
      <div class="dashboard-grid">
        <!-- Financial Health Widget -->
        <div class="dashboard-card">
          <div class="card-header">
            <h5 class="card-title">
              <i class="fas fa-heartbeat"></i>
              Financial Health
            </h5>
          </div>
          <div class="card-body">
            <FinancialHealthWidget 
              :health-data="financialHealth" 
              :loading="loading"
            />
          </div>
        </div>

        <!-- Spending Patterns Widget -->
        <div class="dashboard-card">
          <div class="card-header">
            <h5 class="card-title">
              <i class="fas fa-chart-pie"></i>
              Spending Patterns
            </h5>
          </div>
          <div class="card-body">
            <SpendingPatternsWidget 
              :patterns="patterns" 
              :loading="loading"
            />
          </div>
        </div>

        <!-- Insights Widget -->
        <div class="dashboard-card">
          <div class="card-header">
            <h5 class="card-title">
              <i class="fas fa-lightbulb"></i>
              Insights
            </h5>
          </div>
          <div class="card-body">
            <InsightsWidget 
              :insights="insights" 
              :loading="loading"
            />
          </div>
        </div>

        <!-- Recommendations Widget -->
        <div class="dashboard-card">
          <div class="card-header">
            <h5 class="card-title">
              <i class="fas fa-magic"></i>
              Recommendations
            </h5>
          </div>
          <div class="card-body">
            <RecommendationsWidget 
              :recommendations="recommendations" 
              :loading="loading"
              @dismiss-recommendation="dismissRecommendation"
            />
          </div>
        </div>

        <!-- Forecasts Widget -->
        <div class="dashboard-card large">
          <div class="card-header">
            <h5 class="card-title">
              <i class="fas fa-crystal-ball"></i>
              Spending Forecasts
            </h5>
          </div>
          <div class="card-body">
            <ForecastsWidget 
              :forecasts="forecasts" 
              :loading="loading"
            />
          </div>
        </div>

        <!-- Trends Widget -->
        <div class="dashboard-card large">
          <div class="card-header">
            <h5 class="card-title">
              <i class="fas fa-chart-line"></i>
              Spending Trends
            </h5>
          </div>
          <div class="card-body">
            <TrendsWidget 
              :trends="trends" 
              :period="selectedPeriod"
              :loading="loading"
            />
          </div>
        </div>
      </div>
    </template>

    <!-- Empty State -->
    <template v-if="!loading && !error && !dashboardData">
      <div class="empty-state">
        <i class="fas fa-chart-bar empty-icon"></i>
        <h3>No Analytics Available</h3>
        <p>Start tracking your expenses to see analytics and insights.</p>
        <router-link to="/expenses" class="btn btn-primary">
          Add First Expense
        </router-link>
      </div>
    </template>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import { useAnalyticsStore } from '@/stores/analytics'
import FinancialHealthWidget from '@/components/analytics/FinancialHealthWidget.vue'
import SpendingPatternsWidget from '@/components/analytics/SpendingPatternsWidget.vue'
import InsightsWidget from '@/components/analytics/InsightsWidget.vue'
import RecommendationsWidget from '@/components/analytics/RecommendationsWidget.vue'
import ForecastsWidget from '@/components/analytics/ForecastsWidget.vue'
import TrendsWidget from '@/components/analytics/TrendsWidget.vue'

export default {
  name: 'AnalyticsDashboard',
  components: {
    FinancialHealthWidget,
    SpendingPatternsWidget,
    InsightsWidget,
    RecommendationsWidget,
    ForecastsWidget,
    TrendsWidget
  },
  setup() {
    const analyticsStore = useAnalyticsStore()
    const selectedPeriod = ref('monthly')

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

    const dismissRecommendation = (index) => {
      // Remove recommendation from local array
      const updatedRecommendations = [...recommendations.value]
      updatedRecommendations.splice(index, 1)
      // You would typically call an API here to persist this change
    }

    // Lifecycle
    onMounted(() => {
      loadAnalytics()
    })

    return {
      // Data
      selectedPeriod,
      
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
      
      // Methods
      onPeriodChange,
      refreshAnalytics,
      loadAnalytics,
      dismissRecommendation
    }
  }
}
</script>

<style scoped>
.analytics-dashboard {
  padding: 2rem;
  background: #f8f9fa;
  min-height: 100vh;
}

.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 2rem;
  background: white;
  padding: 1.5rem;
  border-radius: 12px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
}

.header-content {
  flex: 1;
}

.page-title {
  font-size: 2rem;
  font-weight: 700;
  color: #212529;
  margin-bottom: 0.5rem;
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.page-title i {
  color: #007bff;
}

.page-subtitle {
  color: #6c757d;
  font-size: 1.1rem;
  margin: 0;
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.period-selector .form-select {
  min-width: 140px;
}

.loading-container {
  text-align: center;
  padding: 4rem 0;
}

.dashboard-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.dashboard-card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  overflow: hidden;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.dashboard-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
}

.dashboard-card.large {
  grid-column: span 2;
}

.card-header {
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid #e9ecef;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.card-title {
  margin: 0;
  font-size: 1.1rem;
  font-weight: 600;
  color: white;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.card-body {
  padding: 1.5rem;
  height: 400px;
  overflow-y: auto;
}

.dashboard-card.large .card-body {
  height: 500px;
}

.empty-state {
  text-align: center;
  padding: 4rem 2rem;
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

.empty-icon {
  font-size: 4rem;
  color: #dee2e6;
  margin-bottom: 1.5rem;
}

.empty-state h3 {
  color: #495057;
  margin-bottom: 1rem;
}

.empty-state p {
  color: #6c757d;
  font-size: 1.1rem;
  margin-bottom: 2rem;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .dashboard-header {
    flex-direction: column;
    text-align: center;
    gap: 1.5rem;
  }
  
  .header-actions {
    justify-content: center;
  }
  
  .dashboard-grid {
    grid-template-columns: 1fr;
  }
  
  .dashboard-card.large {
    grid-column: span 1;
  }
  
  .page-title {
    font-size: 1.75rem;
    justify-content: center;
  }

  .analytics-dashboard {
    padding: 1rem;
  }
}

@media (max-width: 480px) {
  .page-title {
    font-size: 1.5rem;
  }
  
  .page-subtitle {
    font-size: 1rem;
  }
  
  .card-body {
    padding: 1rem;
    height: 350px;
  }
  
  .dashboard-card.large .card-body {
    height: 400px;
  }
}
</style>