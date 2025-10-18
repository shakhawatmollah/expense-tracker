<template>
  <div class="insights-widget">
    <template v-if="loading">
      <div class="text-center py-4">
        <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
        <p class="mt-2 mb-0">Generating insights...</p>
      </div>
    </template>
    
    <template v-else-if="insights && insights.length > 0">
      <div class="insights-list">
        <div 
          v-for="insight in insights.slice(0, 5)" 
          :key="insight.id"
          class="insight-item"
        >
          <div class="insight-header">
            <div class="insight-icon">
              <i :class="getInsightIcon(insight.insight_type)"></i>
            </div>
            <div class="insight-meta">
              <span class="insight-type">{{ formatInsightType(insight.insight_type) }}</span>
              <span class="insight-date">{{ formatDate(insight.insight_date) }}</span>
            </div>
          </div>
          
          <div class="insight-content">
            <p class="insight-summary">{{ insight.summary }}</p>
            
            <template v-if="insight.insight_data">
              <!-- Top Categories Insight -->
              <template v-if="insight.insight_data.top_categories">
                <div class="top-categories">
                  <h6 class="section-title">Top Spending Categories</h6>
                  <div class="category-list">
                    <div 
                      v-for="category in insight.insight_data.top_categories.slice(0, 3)" 
                      :key="category.name"
                      class="category-item"
                    >
                      <span class="category-name">{{ category.name }}</span>
                      <span class="category-amount">${{ formatCurrency(category.total) }}</span>
                    </div>
                  </div>
                </div>
              </template>

              <!-- Trends Insight -->
              <template v-if="insight.insight_data.trends">
                <div class="trends-info">
                  <h6 class="section-title">Spending Trend</h6>
                  <div class="trend-indicator">
                    <i 
                      :class="[
                        'trend-icon',
                        insight.insight_data.trends.trend_direction === 'increasing' ? 'fas fa-arrow-up text-danger' : 'fas fa-arrow-down text-success'
                      ]"
                    ></i>
                    <span class="trend-text">
                      {{ insight.insight_data.trends.trend_direction === 'increasing' ? 'Spending increased' : 'Spending decreased' }}
                      by {{ Math.abs(insight.insight_data.trends.trend_percentage) }}%
                    </span>
                  </div>
                </div>
              </template>

              <!-- Budget Performance -->
              <template v-if="insight.insight_data.budget_performance">
                <div class="budget-performance">
                  <h6 class="section-title">Budget Performance</h6>
                  <div class="performance-grid">
                    <div 
                      v-for="budget in insight.insight_data.budget_performance.slice(0, 2)" 
                      :key="budget.category"
                      class="performance-item"
                    >
                      <div class="performance-header">
                        <span class="category-name">{{ budget.category }}</span>
                        <span 
                          class="utilization-badge"
                          :class="getUtilizationClass(budget.utilization)"
                        >
                          {{ Math.round(budget.utilization) }}%
                        </span>
                      </div>
                      <div class="performance-bar">
                        <div 
                          class="performance-fill"
                          :style="{ 
                            width: Math.min(budget.utilization, 100) + '%',
                            backgroundColor: getUtilizationColor(budget.utilization)
                          }"
                        ></div>
                      </div>
                    </div>
                  </div>
                </div>
              </template>
            </template>

            <!-- Action Items -->
            <template v-if="insight.action_items && insight.action_items.length > 0">
              <div class="action-items">
                <h6 class="section-title">Recommended Actions</h6>
                <ul class="action-list">
                  <li 
                    v-for="action in insight.action_items.slice(0, 2)" 
                    :key="action"
                    class="action-item"
                  >
                    <i class="fas fa-lightbulb"></i>
                    {{ action }}
                  </li>
                </ul>
              </div>
            </template>
          </div>
        </div>
      </div>

      <!-- View All Link -->
      <div class="view-all" v-if="insights.length > 5">
        <button class="btn btn-outline-primary btn-sm">
          View All Insights ({{ insights.length }})
        </button>
      </div>
    </template>

    <template v-else>
      <div class="empty-state py-4">
        <i class="fas fa-lightbulb fa-2x text-muted mb-2"></i>
        <p class="text-muted mb-0">No insights available</p>
        <small class="text-muted">Insights will be generated from your spending data</small>
      </div>
    </template>
  </div>
</template>

<script>
export default {
  name: 'InsightsWidget',
  props: {
    insights: {
      type: Array,
      default: () => []
    },
    loading: {
      type: Boolean,
      default: false
    }
  },
  methods: {
    getInsightIcon(type) {
      const icons = {
        comprehensive: 'fas fa-chart-bar',
        spending: 'fas fa-shopping-cart',
        budget: 'fas fa-wallet',
        trend: 'fas fa-chart-line',
        category: 'fas fa-tags'
      }
      return icons[type] || 'fas fa-lightbulb'
    },
    formatInsightType(type) {
      return type.charAt(0).toUpperCase() + type.slice(1).replace('_', ' ')
    },
    formatDate(dateString) {
      const date = new Date(dateString)
      const now = new Date()
      const diffTime = Math.abs(now - date)
      const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
      
      if (diffDays === 1) return 'Yesterday'
      if (diffDays < 7) return `${diffDays} days ago`
      if (diffDays < 30) return `${Math.ceil(diffDays / 7)} weeks ago`
      return date.toLocaleDateString()
    },
    formatCurrency(amount) {
      return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      }).format(Math.abs(amount))
    },
    getUtilizationClass(utilization) {
      if (utilization > 100) return 'over-budget'
      if (utilization > 80) return 'high-usage'
      if (utilization > 60) return 'medium-usage'
      return 'low-usage'
    },
    getUtilizationColor(utilization) {
      if (utilization > 100) return '#dc3545'
      if (utilization > 80) return '#fd7e14'
      if (utilization > 60) return '#ffc107'
      return '#28a745'
    }
  }
}
</script>

<style scoped>
.insights-widget {
  height: 100%;
  display: flex;
  flex-direction: column;
}

.insights-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  flex: 1;
  overflow-y: auto;
}

.insight-item {
  padding: 1rem;
  border: 1px solid #e9ecef;
  border-radius: 8px;
  background: #f8f9fa;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.insight-item:hover {
  transform: translateY(-1px);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.insight-header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 0.75rem;
}

.insight-icon {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 0.875rem;
}

.insight-meta {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.insight-type {
  font-weight: 600;
  color: #212529;
  font-size: 0.875rem;
}

.insight-date {
  font-size: 0.75rem;
  color: #6c757d;
}

.insight-content {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.insight-summary {
  margin: 0;
  color: #495057;
  font-size: 0.9rem;
  line-height: 1.4;
}

.section-title {
  margin: 0 0 0.5rem 0;
  font-size: 0.8rem;
  font-weight: 600;
  color: #495057;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.category-list {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.category-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.5rem;
  background: white;
  border-radius: 4px;
  border: 1px solid #e9ecef;
}

.category-name {
  font-size: 0.875rem;
  color: #495057;
}

.category-amount {
  font-weight: 600;
  color: #212529;
  font-size: 0.875rem;
}

.trends-info {
  background: white;
  padding: 0.75rem;
  border-radius: 6px;
  border: 1px solid #e9ecef;
}

.trend-indicator {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.trend-icon {
  font-size: 1rem;
}

.trend-text {
  font-size: 0.875rem;
  color: #495057;
}

.budget-performance {
  background: white;
  padding: 0.75rem;
  border-radius: 6px;
  border: 1px solid #e9ecef;
}

.performance-grid {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.performance-item {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.performance-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.utilization-badge {
  padding: 0.125rem 0.5rem;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 600;
}

.over-budget {
  background: #f8d7da;
  color: #721c24;
}

.high-usage {
  background: #fff3cd;
  color: #856404;
}

.medium-usage {
  background: #d1ecf1;
  color: #0c5460;
}

.low-usage {
  background: #d4edda;
  color: #155724;
}

.performance-bar {
  height: 6px;
  background: #e9ecef;
  border-radius: 3px;
  overflow: hidden;
}

.performance-fill {
  height: 100%;
  border-radius: 3px;
  transition: width 0.3s ease;
}

.action-items {
  background: #fff3cd;
  padding: 0.75rem;
  border-radius: 6px;
  border-left: 4px solid #ffc107;
}

.action-list {
  margin: 0;
  padding: 0;
  list-style: none;
}

.action-item {
  display: flex;
  align-items: flex-start;
  gap: 0.5rem;
  font-size: 0.875rem;
  color: #495057;
  margin-bottom: 0.5rem;
}

.action-item:last-child {
  margin-bottom: 0;
}

.action-item i {
  color: #ffc107;
  margin-top: 0.125rem;
  font-size: 0.75rem;
}

.view-all {
  text-align: center;
  padding-top: 1rem;
  border-top: 1px solid #e9ecef;
  margin-top: 1rem;
}

.empty-state {
  text-align: center;
  flex: 1;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .category-item {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.25rem;
  }
  
  .performance-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.25rem;
  }
}
</style>