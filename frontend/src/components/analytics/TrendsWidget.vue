<template>
  <div class="trends-widget">
    <template v-if="loading">
      <div class="text-center py-4">
        <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
        <p class="mt-2 mb-0">Analyzing trends...</p>
      </div>
    </template>
    
    <template v-else-if="trends">
      <div class="trends-container">
        <!-- Trends Chart Placeholder -->
        <div class="chart-container">
          <div class="chart-header">
            <h6 class="chart-title">Spending Trends - {{ formatPeriod(period) }}</h6>
            <div class="chart-controls">
              <select v-model="selectedMetric" class="form-select form-select-sm">
                <option value="total">Total Spending</option>
                <option value="average">Average Daily</option>
                <option value="categories">By Category</option>
              </select>
            </div>
          </div>
          
          <!-- Simple trend visualization -->
          <div class="simple-chart">
            <div class="chart-placeholder">
              <div class="trend-line-chart">
                <!-- Mock trend line -->
                <div class="chart-grid">
                  <div 
                    v-for="i in 12" 
                    :key="i"
                    class="grid-line"
                  ></div>
                </div>
                <div class="trend-path">
                  <div 
                    v-for="(point, index) in mockTrendData" 
                    :key="index"
                    class="trend-point"
                    :style="{ 
                      left: (index * 8.33) + '%',
                      bottom: point + '%'
                    }"
                  ></div>
                  <svg class="trend-svg" viewBox="0 0 100 100" preserveAspectRatio="none">
                    <polyline
                      fill="none"
                      stroke="#007bff"
                      stroke-width="2"
                      :points="trendPolylinePoints"
                    />
                  </svg>
                </div>
              </div>
              <div class="chart-labels">
                <div class="x-labels">
                  <span v-for="month in last12Months" :key="month">{{ month }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Trend Insights -->
        <div class="trend-insights">
          <div class="insights-grid">
            <!-- Current Trend -->
            <div class="insight-card">
              <div class="insight-icon trend-up" v-if="currentTrend === 'increasing'">
                <i class="fas fa-trending-up"></i>
              </div>
              <div class="insight-icon trend-down" v-else-if="currentTrend === 'decreasing'">
                <i class="fas fa-trending-down"></i>
              </div>
              <div class="insight-icon trend-stable" v-else>
                <i class="fas fa-minus"></i>
              </div>
              <div class="insight-content">
                <h6 class="insight-title">Current Trend</h6>
                <p class="insight-value">{{ formatTrend(currentTrend) }}</p>
                <small class="insight-subtitle">Last 3 months</small>
              </div>
            </div>

            <!-- Highest Month -->
            <div class="insight-card">
              <div class="insight-icon high-month">
                <i class="fas fa-arrow-up"></i>
              </div>
              <div class="insight-content">
                <h6 class="insight-title">Highest Month</h6>
                <p class="insight-value">${{ formatCurrency(highestMonth.amount) }}</p>
                <small class="insight-subtitle">{{ highestMonth.month }}</small>
              </div>
            </div>

            <!-- Lowest Month -->
            <div class="insight-card">
              <div class="insight-icon low-month">
                <i class="fas fa-arrow-down"></i>
              </div>
              <div class="insight-content">
                <h6 class="insight-title">Lowest Month</h6>
                <p class="insight-value">${{ formatCurrency(lowestMonth.amount) }}</p>
                <small class="insight-subtitle">{{ lowestMonth.month }}</small>
              </div>
            </div>

            <!-- Average -->
            <div class="insight-card">
              <div class="insight-icon average">
                <i class="fas fa-chart-bar"></i>
              </div>
              <div class="insight-content">
                <h6 class="insight-title">Monthly Average</h6>
                <p class="insight-value">${{ formatCurrency(monthlyAverage) }}</p>
                <small class="insight-subtitle">{{ period }} period</small>
              </div>
            </div>
          </div>
        </div>

        <!-- Category Breakdown -->
        <div class="category-trends" v-if="selectedMetric === 'categories'">
          <h6 class="section-title">Category Trends</h6>
          <div class="category-chart">
            <div 
              v-for="category in topCategories" 
              :key="category.name"
              class="category-bar-item"
            >
              <div class="category-info">
                <span class="category-name">{{ category.name }}</span>
                <span class="category-amount">${{ formatCurrency(category.amount) }}</span>
              </div>
              <div class="category-bar">
                <div 
                  class="category-fill"
                  :style="{ 
                    width: (category.amount / maxCategoryAmount) * 100 + '%',
                    backgroundColor: category.color
                  }"
                ></div>
              </div>
              <div class="category-trend">
                <span 
                  :class="[
                    'trend-indicator',
                    category.trend > 0 ? 'increasing' : 'decreasing'
                  ]"
                >
                  <i 
                    :class="[
                      'fas',
                      category.trend > 0 ? 'fa-arrow-up' : 'fa-arrow-down'
                    ]"
                  ></i>
                  {{ Math.abs(category.trend) }}%
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>

    <template v-else>
      <div class="empty-state py-4">
        <i class="fas fa-chart-line fa-2x text-muted mb-2"></i>
        <p class="text-muted mb-0">No trend data available</p>
        <small class="text-muted">Trends will appear as you track more expenses</small>
      </div>
    </template>
  </div>
</template>

<script>
export default {
  name: 'TrendsWidget',
  props: {
    trends: {
      type: Object,
      default: null
    },
    period: {
      type: String,
      default: 'monthly'
    },
    loading: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      selectedMetric: 'total',
      // Mock data for demonstration
      mockTrendData: [45, 52, 48, 61, 55, 67, 59, 73, 68, 71, 65, 69],
      currentTrend: 'increasing',
      monthlyAverage: 2650,
      highestMonth: { month: 'March 2024', amount: 3200 },
      lowestMonth: { month: 'January 2024', amount: 1800 },
      topCategories: [
        { name: 'Food & Dining', amount: 850, trend: 5, color: '#007bff' },
        { name: 'Transportation', amount: 650, trend: -3, color: '#28a745' },
        { name: 'Shopping', amount: 480, trend: 12, color: '#ffc107' },
        { name: 'Utilities', amount: 320, trend: 2, color: '#dc3545' },
        { name: 'Entertainment', amount: 280, trend: -8, color: '#6f42c1' }
      ]
    }
  },
  computed: {
    last12Months() {
      const months = []
      for (let i = 11; i >= 0; i--) {
        const date = new Date()
        date.setMonth(date.getMonth() - i)
        months.push(date.toLocaleDateString('en-US', { month: 'short' }))
      }
      return months
    },
    trendPolylinePoints() {
      return this.mockTrendData
        .map((point, index) => `${index * 8.33},${100 - point}`)
        .join(' ')
    },
    maxCategoryAmount() {
      return Math.max(...this.topCategories.map(c => c.amount))
    }
  },
  methods: {
    formatPeriod(period) {
      const periods = {
        weekly: 'Weekly',
        monthly: 'Monthly', 
        quarterly: 'Quarterly',
        yearly: 'Yearly'
      }
      return periods[period] || 'Monthly'
    },
    formatTrend(trend) {
      const trends = {
        increasing: 'Spending Up',
        decreasing: 'Spending Down',
        stable: 'Stable Spending'
      }
      return trends[trend] || 'Unknown'
    },
    formatCurrency(amount) {
      return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      }).format(Math.abs(amount))
    }
  }
}
</script>

<style scoped>
.trends-widget {
  height: 100%;
}

.trends-container {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
  height: 100%;
}

.chart-container {
  background: white;
  border: 1px solid #e9ecef;
  border-radius: 8px;
  padding: 1rem;
  flex: 1;
}

.chart-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.chart-title {
  margin: 0;
  font-size: 1rem;
  font-weight: 600;
  color: #495057;
}

.chart-controls .form-select {
  min-width: 140px;
}

.simple-chart {
  height: 200px;
  display: flex;
  flex-direction: column;
}

.chart-placeholder {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.trend-line-chart {
  flex: 1;
  position: relative;
  background: linear-gradient(180deg, #f8f9fa 0%, #ffffff 100%);
  border: 1px solid #e9ecef;
  border-radius: 6px;
  overflow: hidden;
}

.chart-grid {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  display: flex;
  justify-content: space-between;
}

.grid-line {
  width: 1px;
  background: #e9ecef;
  opacity: 0.5;
}

.trend-path {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
}

.trend-point {
  position: absolute;
  width: 6px;
  height: 6px;
  background: #007bff;
  border-radius: 50%;
  transform: translate(-50%, 50%);
  border: 2px solid white;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.trend-svg {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
}

.chart-labels {
  margin-top: 0.5rem;
}

.x-labels {
  display: flex;
  justify-content: space-between;
  font-size: 0.75rem;
  color: #6c757d;
}

.trend-insights {
  background: white;
  border: 1px solid #e9ecef;
  border-radius: 8px;
  padding: 1rem;
}

.insights-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 1rem;
}

.insight-card {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0.75rem;
  background: #f8f9fa;
  border-radius: 6px;
  border: 1px solid #e9ecef;
}

.insight-icon {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1rem;
}

.trend-up {
  background: #dc3545;
}

.trend-down {
  background: #28a745;
}

.trend-stable {
  background: #17a2b8;
}

.high-month {
  background: #fd7e14;
}

.low-month {
  background: #20c997;
}

.average {
  background: #6f42c1;
}

.insight-content {
  flex: 1;
}

.insight-title {
  margin: 0 0 0.25rem 0;
  font-size: 0.8rem;
  color: #6c757d;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  font-weight: 600;
}

.insight-value {
  margin: 0 0 0.25rem 0;
  font-size: 1.1rem;
  font-weight: 700;
  color: #212529;
}

.insight-subtitle {
  color: #6c757d;
  font-size: 0.75rem;
}

.category-trends {
  background: white;
  border: 1px solid #e9ecef;
  border-radius: 8px;
  padding: 1rem;
}

.section-title {
  margin: 0 0 1rem 0;
  font-size: 1rem;
  font-weight: 600;
  color: #495057;
}

.category-chart {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.category-bar-item {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.category-info {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.category-name {
  font-size: 0.875rem;
  color: #495057;
  font-weight: 500;
}

.category-amount {
  font-size: 0.875rem;
  font-weight: 600;
  color: #212529;
}

.category-bar {
  height: 8px;
  background: #e9ecef;
  border-radius: 4px;
  overflow: hidden;
}

.category-fill {
  height: 100%;
  border-radius: 4px;
  transition: width 0.3s ease;
}

.category-trend {
  text-align: right;
}

.trend-indicator {
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
  font-size: 0.75rem;
  font-weight: 600;
}

.trend-indicator.increasing {
  color: #dc3545;
}

.trend-indicator.decreasing {
  color: #28a745;
}

.empty-state {
  text-align: center;
  display: flex;
  flex-direction: column;
  justify-content: center;
  height: 100%;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .chart-header {
    flex-direction: column;
    gap: 0.75rem;
    align-items: flex-start;
  }
  
  .insights-grid {
    grid-template-columns: 1fr;
  }
  
  .insight-card {
    justify-content: center;
    text-align: center;
  }
  
  .simple-chart {
    height: 150px;
  }
  
  .x-labels span {
    font-size: 0.7rem;
  }
}

@media (max-width: 480px) {
  .trends-container {
    gap: 1rem;
  }
  
  .category-info {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.25rem;
  }
}
</style>