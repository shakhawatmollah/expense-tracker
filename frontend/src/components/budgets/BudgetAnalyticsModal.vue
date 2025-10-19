<template>
  <div class="modal-overlay" @click="handleOverlayClick">
    <div class="modal-container" @click.stop>
      <!-- Modal Header -->
      <div class="modal-header">
        <h2 class="modal-title">
          <i class="fas fa-chart-bar"></i>
          Budget Analytics
        </h2>
        <button @click="$emit('close')" class="modal-close">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <!-- Modal Body -->
      <div class="modal-body">
        <div class="analytics-container">
          <!-- Period Selector -->
          <div class="period-selector">
            <label class="selector-label">Analysis Period:</label>
            <select v-model="selectedPeriod" @change="onPeriodChange" class="form-select">
              <option value="monthly">Monthly</option>
              <option value="quarterly">Quarterly</option>
              <option value="yearly">Yearly</option>
            </select>
          </div>

          <!-- Loading State -->
          <div v-if="loading" class="loading-container">
            <div class="loading-spinner"></div>
            <p>Analyzing budget data...</p>
          </div>

          <!-- Analytics Content -->
          <div v-else class="analytics-content">
            <!-- Overview Stats -->
            <div class="stats-section">
              <h3 class="section-title">Budget Performance Overview</h3>
              <div class="overview-stats">
                <div class="stat-card">
                  <div class="stat-icon stat-icon-primary">
                    <i class="fas fa-bullseye"></i>
                  </div>
                  <div class="stat-content">
                    <div class="stat-value">{{ overallAccuracy }}%</div>
                    <div class="stat-label">Budget Accuracy</div>
                    <div class="stat-description">How close you stay to budgets</div>
                  </div>
                </div>
                
                <div class="stat-card">
                  <div class="stat-icon stat-icon-success">
                    <i class="fas fa-trophy"></i>
                  </div>
                  <div class="stat-content">
                    <div class="stat-value">{{ budgetsOnTrack }}</div>
                    <div class="stat-label">Budgets On Track</div>
                    <div class="stat-description">Out of {{ totalBudgets }} total</div>
                  </div>
                </div>
                
                <div class="stat-card">
                  <div class="stat-icon stat-icon-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                  </div>
                  <div class="stat-content">
                    <div class="stat-value">{{ budgetsOverSpent }}</div>
                    <div class="stat-label">Over Budget</div>
                    <div class="stat-description">Need attention</div>
                  </div>
                </div>
                
                <div class="stat-card">
                  <div class="stat-icon stat-icon-info">
                    <i class="fas fa-clock"></i>
                  </div>
                  <div class="stat-content">
                    <div class="stat-value">{{ averageDaysLeft }}</div>
                    <div class="stat-label">Avg Days Left</div>
                    <div class="stat-description">In current period</div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Budget Trends Chart -->
            <div class="chart-section">
              <h3 class="section-title">Spending Trends</h3>
              <div class="chart-container">
                <canvas ref="trendsChart" class="trends-chart"></canvas>
              </div>
            </div>

            <!-- Category Performance -->
            <div class="category-section">
              <h3 class="section-title">Category Performance</h3>
              <div class="category-performance">
                <div 
                  v-for="category in categoryPerformance" 
                  :key="category.name"
                  class="category-item"
                >
                  <div class="category-header">
                    <div class="category-info">
                      <span class="category-name">{{ category.name }}</span>
                      <span class="category-budgets">{{ category.budgetCount }} budget(s)</span>
                    </div>
                    <div class="category-status">
                      <span :class="getCategoryStatusClass(category.performance)">
                        {{ getCategoryStatusText(category.performance) }}
                      </span>
                    </div>
                  </div>
                  
                  <div class="category-progress">
                    <div class="progress-bar">
                      <div 
                        class="progress-fill"
                        :class="getCategoryProgressClass(category.performance)"
                        :style="{ width: `${Math.min(category.performance, 100)}%` }"
                      ></div>
                    </div>
                    <div class="progress-details">
                      <span>${{ formatAmount(category.spent) }} / ${{ formatAmount(category.budget) }}</span>
                      <span>{{ category.performance }}%</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Recommendations -->
            <div class="recommendations-section">
              <h3 class="section-title">Smart Recommendations</h3>
              <div class="recommendations">
                <div 
                  v-for="recommendation in recommendations" 
                  :key="recommendation.id"
                  :class="['recommendation-card', `recommendation-${recommendation.type}`]"
                >
                  <div class="recommendation-icon">
                    <i :class="getRecommendationIcon(recommendation.type)"></i>
                  </div>
                  <div class="recommendation-content">
                    <h4 class="recommendation-title">{{ recommendation.title }}</h4>
                    <p class="recommendation-description">{{ recommendation.description }}</p>
                    <div v-if="recommendation.action" class="recommendation-action">
                      <button class="btn btn-sm btn-outline-primary">
                        {{ recommendation.action }}
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal Footer -->
      <div class="modal-footer">
        <button @click="exportAnalytics" class="btn btn-outline-primary">
          <i class="fas fa-download"></i>
          Export Report
        </button>
        <button @click="$emit('close')" class="btn btn-secondary">
          Close
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useBudgetStore } from '@/stores/budget'
import { Chart, registerables } from 'chart.js'
import { formatCurrency } from '@/utils/formatters'

Chart.register(...registerables)

export default {
  name: 'BudgetAnalyticsModal',
  emits: ['close'],
  setup(props, { emit }) {
    const budgetStore = useBudgetStore()
    
    // State
    const loading = ref(false)
    const selectedPeriod = ref('monthly')
    const trendsChart = ref(null)
    let chartInstance = null
    
    // Mock analytics data (in real app, this would come from API)
    const analyticsData = ref({
      overallAccuracy: 78,
      budgetsOnTrack: 6,
      totalBudgets: 8,
      budgetsOverSpent: 2,
      averageDaysLeft: 12,
      categoryPerformance: [
        { name: 'Food & Dining', spent: 450, budget: 500, performance: 90, budgetCount: 2 },
        { name: 'Transportation', spent: 200, budget: 300, performance: 67, budgetCount: 1 },
        { name: 'Entertainment', spent: 180, budget: 150, performance: 120, budgetCount: 1 },
        { name: 'Utilities', spent: 140, budget: 200, performance: 70, budgetCount: 1 },
        { name: 'Shopping', spent: 320, budget: 250, performance: 128, budgetCount: 2 },
        { name: 'Healthcare', spent: 80, budget: 150, performance: 53, budgetCount: 1 }
      ],
      recommendations: [
        {
          id: 1,
          type: 'warning',
          title: 'Entertainment Budget Exceeded',
          description: 'You\'ve spent 20% more than budgeted this month. Consider adjusting your entertainment spending.',
          action: 'View Details'
        },
        {
          id: 2,
          type: 'success',
          title: 'Great Job on Utilities!',
          description: 'You\'re 30% under budget for utilities. This saved money could be allocated elsewhere.',
          action: 'Reallocate Funds'
        },
        {
          id: 3,
          type: 'info',
          title: 'Set Up Healthcare Budget',
          description: 'Consider creating a separate budget for medical expenses to better track health spending.',
          action: 'Create Budget'
        }
      ]
    })
    
    // Computed properties
    const overallAccuracy = computed(() => analyticsData.value.overallAccuracy)
    const budgetsOnTrack = computed(() => analyticsData.value.budgetsOnTrack)
    const totalBudgets = computed(() => analyticsData.value.totalBudgets)
    const budgetsOverSpent = computed(() => analyticsData.value.budgetsOverSpent)
    const averageDaysLeft = computed(() => analyticsData.value.averageDaysLeft)
    const categoryPerformance = computed(() => analyticsData.value.categoryPerformance)
    const recommendations = computed(() => analyticsData.value.recommendations)
    
    // Methods
    const formatAmount = (amount) => {
      return formatCurrency(amount)
    }
    
    const getCategoryStatusClass = (performance) => {
      if (performance > 100) return 'status-over'
      if (performance > 80) return 'status-warning'
      return 'status-good'
    }
    
    const getCategoryStatusText = (performance) => {
      if (performance > 100) return 'Over Budget'
      if (performance > 80) return 'Near Limit'
      return 'On Track'
    }
    
    const getCategoryProgressClass = (performance) => {
      if (performance > 100) return 'progress-over'
      if (performance > 80) return 'progress-warning'
      return 'progress-good'
    }
    
    const getRecommendationIcon = (type) => {
      const icons = {
        warning: 'fas fa-exclamation-triangle',
        success: 'fas fa-check-circle',
        info: 'fas fa-info-circle',
        danger: 'fas fa-times-circle'
      }
      return icons[type] || 'fas fa-lightbulb'
    }
    
    const createTrendsChart = () => {
      if (!trendsChart.value) return
      
      if (chartInstance) {
        chartInstance.destroy()
      }
      
      const ctx = trendsChart.value.getContext('2d')
      
      // Mock trend data
      const trendData = {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        datasets: [
          {
            label: 'Budgeted',
            data: [2000, 2100, 2000, 2200, 2150, 2000],
            borderColor: '#3B82F6',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            fill: true,
            tension: 0.4
          },
          {
            label: 'Actual Spending',
            data: [1800, 2300, 1900, 2100, 2400, 1850],
            borderColor: '#EF4444',
            backgroundColor: 'rgba(239, 68, 68, 0.1)',
            fill: true,
            tension: 0.4
          }
        ]
      }
      
      chartInstance = new Chart(ctx, {
        type: 'line',
        data: trendData,
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: 'top'
            }
          },
          scales: {
            y: {
              beginAtZero: true,
              ticks: {
                callback: (value) => `$${value}`
              }
            }
          }
        }
      })
    }
    
    const onPeriodChange = () => {
      loading.value = true
      // Simulate API call
      setTimeout(() => {
        loading.value = false
        createTrendsChart()
      }, 1000)
    }
    
    const exportAnalytics = () => {
      // TODO: Implement analytics export
      console.log('Export analytics report')
    }
    
    const handleOverlayClick = () => {
      emit('close')
    }
    
    // Lifecycle
    onMounted(() => {
      createTrendsChart()
    })
    
    onUnmounted(() => {
      if (chartInstance) {
        chartInstance.destroy()
      }
    })
    
    return {
      loading,
      selectedPeriod,
      trendsChart,
      overallAccuracy,
      budgetsOnTrack,
      totalBudgets,
      budgetsOverSpent,
      averageDaysLeft,
      categoryPerformance,
      recommendations,
      formatAmount,
      getCategoryStatusClass,
      getCategoryStatusText,
      getCategoryProgressClass,
      getRecommendationIcon,
      onPeriodChange,
      exportAnalytics,
      handleOverlayClick
    }
  }
}
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 20px;
}

.modal-container {
  background: white;
  border-radius: 12px;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
  max-width: 1200px;
  width: 100%;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 24px;
  border-bottom: 1px solid #E5E7EB;
}

.modal-title {
  font-size: 24px;
  font-weight: 600;
  color: #1F2937;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 12px;
}

.modal-title i {
  color: #3B82F6;
}

.modal-close {
  padding: 8px;
  border: none;
  background: none;
  color: #6B7280;
  cursor: pointer;
  border-radius: 6px;
  transition: all 0.2s;
}

.modal-close:hover {
  background: #F3F4F6;
  color: #1F2937;
}

.modal-body {
  flex: 1;
  overflow-y: auto;
  padding: 24px;
}

.analytics-container {
  max-width: 100%;
}

.period-selector {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 24px;
}

.selector-label {
  font-weight: 500;
  color: #374151;
}

.form-select {
  padding: 8px 12px;
  border: 1px solid #D1D5DB;
  border-radius: 6px;
  background: white;
  color: #1F2937;
}

.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 20px;
}

.loading-spinner {
  width: 40px;
  height: 40px;
  border: 4px solid #E5E7EB;
  border-top-color: #3B82F6;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 16px;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.analytics-content {
  display: flex;
  flex-direction: column;
  gap: 32px;
}

.section-title {
  font-size: 18px;
  font-weight: 600;
  color: #1F2937;
  margin-bottom: 16px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.overview-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 16px;
}

.stat-card {
  background: #F9FAFB;
  border-radius: 8px;
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 16px;
}

.stat-icon {
  width: 48px;
  height: 48px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
  color: white;
}

.stat-icon-primary { background: #3B82F6; }
.stat-icon-success { background: #10B981; }
.stat-icon-warning { background: #F59E0B; }
.stat-icon-info { background: #8B5CF6; }

.stat-content {
  flex: 1;
}

.stat-value {
  font-size: 24px;
  font-weight: 700;
  color: #1F2937;
  margin-bottom: 4px;
}

.stat-label {
  font-weight: 500;
  color: #374151;
  margin-bottom: 2px;
}

.stat-description {
  font-size: 12px;
  color: #6B7280;
}

.chart-container {
  background: #F9FAFB;
  border-radius: 8px;
  padding: 20px;
  height: 300px;
  position: relative;
}

.trends-chart {
  width: 100% !important;
  height: 100% !important;
}

.category-performance {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.category-item {
  background: #F9FAFB;
  border-radius: 8px;
  padding: 16px;
}

.category-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}

.category-name {
  font-weight: 500;
  color: #1F2937;
}

.category-budgets {
  font-size: 12px;
  color: #6B7280;
}

.status-good { color: #10B981; font-weight: 500; }
.status-warning { color: #F59E0B; font-weight: 500; }
.status-over { color: #EF4444; font-weight: 500; }

.category-progress {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.progress-bar {
  width: 100%;
  height: 8px;
  background: #E5E7EB;
  border-radius: 4px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  border-radius: 4px;
  transition: width 0.3s ease;
}

.progress-good { background: #10B981; }
.progress-warning { background: #F59E0B; }
.progress-over { background: #EF4444; }

.progress-details {
  display: flex;
  justify-content: space-between;
  font-size: 12px;
  color: #6B7280;
}

.recommendations {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.recommendation-card {
  display: flex;
  gap: 16px;
  padding: 16px;
  border-radius: 8px;
  border-left: 4px solid;
}

.recommendation-warning { border-left-color: #F59E0B; background: #FFFBEB; }
.recommendation-success { border-left-color: #10B981; background: #ECFDF5; }
.recommendation-info { border-left-color: #3B82F6; background: #EFF6FF; }

.recommendation-icon {
  font-size: 20px;
  margin-top: 2px;
}

.recommendation-warning .recommendation-icon { color: #F59E0B; }
.recommendation-success .recommendation-icon { color: #10B981; }
.recommendation-info .recommendation-icon { color: #3B82F6; }

.recommendation-title {
  font-weight: 600;
  color: #1F2937;
  margin-bottom: 4px;
}

.recommendation-description {
  color: #6B7280;
  margin-bottom: 8px;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  padding: 24px;
  border-top: 1px solid #E5E7EB;
}

.btn {
  padding: 8px 16px;
  border-radius: 6px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
  border: 1px solid;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.btn-primary {
  background: #3B82F6;
  border-color: #3B82F6;
  color: white;
}

.btn-outline-primary {
  background: transparent;
  border-color: #3B82F6;
  color: #3B82F6;
}

.btn-secondary {
  background: #6B7280;
  border-color: #6B7280;
  color: white;
}

.btn-sm {
  padding: 6px 12px;
  font-size: 14px;
}

@media (max-width: 768px) {
  .modal-container {
    margin: 10px;
    max-height: 95vh;
  }
  
  .overview-stats {
    grid-template-columns: 1fr;
  }
  
  .category-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 4px;
  }
}
</style>