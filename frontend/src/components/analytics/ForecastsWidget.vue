<template>
  <div class="forecasts-widget">
    <template v-if="loading">
      <div class="text-center py-4">
        <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
        <p class="mt-2 mb-0">Generating forecasts...</p>
      </div>
    </template>
    
    <template v-else-if="forecasts">
      <div class="forecasts-container">
        <!-- Next Month Forecast -->
        <div class="forecast-section" v-if="forecasts.next_month_forecast">
          <h6 class="section-title">
            <i class="fas fa-calendar-alt"></i>
            Next Month Forecast
          </h6>
          <div class="forecast-card">
            <div class="forecast-value">
              ${{ formatCurrency(forecasts.next_month_forecast) }}
            </div>
            <div class="forecast-label">Predicted Spending</div>
            <div class="forecast-comparison" v-if="currentMonthSpending">
              <span 
                :class="[
                  'comparison-indicator',
                  forecasts.next_month_forecast > currentMonthSpending ? 'increase' : 'decrease'
                ]"
              >
                <i 
                  :class="[
                    'fas',
                    forecasts.next_month_forecast > currentMonthSpending ? 'fa-arrow-up' : 'fa-arrow-down'
                  ]"
                ></i>
                {{ formatPercentageChange(forecasts.next_month_forecast, currentMonthSpending) }}%
              </span>
              <span class="comparison-text">vs this month</span>
            </div>
          </div>
        </div>

        <!-- Category Forecasts -->
        <div class="forecast-section" v-if="forecasts.category_forecasts && forecasts.category_forecasts.length > 0">
          <h6 class="section-title">
            <i class="fas fa-tags"></i>
            Category Forecasts
          </h6>
          <div class="category-forecasts">
            <div 
              v-for="categoryForecast in forecasts.category_forecasts.slice(0, 5)" 
              :key="categoryForecast.category"
              class="category-forecast-item"
            >
              <div class="category-info">
                <span class="category-name">{{ categoryForecast.category }}</span>
                <span class="category-amount">${{ formatCurrency(categoryForecast.forecast) }}</span>
              </div>
              <div class="category-bar">
                <div 
                  class="category-fill"
                  :style="{ 
                    width: getRelativeWidth(categoryForecast.forecast, forecasts.category_forecasts) + '%' 
                  }"
                ></div>
              </div>
              <div class="category-change" v-if="categoryForecast.change">
                <span 
                  :class="[
                    'change-indicator',
                    categoryForecast.change > 0 ? 'increase' : 'decrease'
                  ]"
                >
                  <i 
                    :class="[
                      'fas',
                      categoryForecast.change > 0 ? 'fa-arrow-up' : 'fa-arrow-down'
                    ]"
                  ></i>
                  {{ Math.abs(categoryForecast.change) }}%
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Trend Analysis -->
        <div class="forecast-section" v-if="forecasts.trend_analysis">
          <h6 class="section-title">
            <i class="fas fa-chart-line"></i>
            Trend Analysis
          </h6>
          <div class="trend-analysis">
            <div class="trend-card">
              <div class="trend-header">
                <span class="trend-label">Overall Trend</span>
                <span 
                  :class="[
                    'trend-badge',
                    getTrendClass(forecasts.trend_analysis.overall_trend)
                  ]"
                >
                  {{ formatTrendLabel(forecasts.trend_analysis.overall_trend) }}
                </span>
              </div>
              <div class="trend-details" v-if="forecasts.trend_analysis.trend_percentage">
                <div class="trend-chart">
                  <div class="chart-container">
                    <div 
                      class="trend-line"
                      :class="getTrendClass(forecasts.trend_analysis.overall_trend)"
                      :style="{ 
                        width: Math.abs(forecasts.trend_analysis.trend_percentage) + '%',
                        maxWidth: '100%'
                      }"
                    ></div>
                  </div>
                  <span class="trend-percentage">
                    {{ Math.abs(forecasts.trend_analysis.trend_percentage) }}%
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Forecast Confidence -->
        <div class="forecast-section">
          <h6 class="section-title">
            <i class="fas fa-shield-alt"></i>
            Forecast Confidence
          </h6>
          <div class="confidence-meter">
            <div class="confidence-bar">
              <div 
                class="confidence-fill"
                :style="{ width: forecastConfidence + '%' }"
              ></div>
            </div>
            <div class="confidence-label">
              {{ forecastConfidence }}% Confidence
            </div>
            <small class="confidence-note">
              Based on {{ historicalDataPoints }} months of spending data
            </small>
          </div>
        </div>
      </div>
    </template>

    <template v-else>
      <div class="empty-state py-4">
        <i class="fas fa-crystal-ball fa-2x text-muted mb-2"></i>
        <p class="text-muted mb-0">No forecast data available</p>
        <small class="text-muted">Forecasts require at least 3 months of spending history</small>
      </div>
    </template>
  </div>
</template>

<script>
export default {
  name: 'ForecastsWidget',
  props: {
    forecasts: {
      type: Object,
      default: null
    },
    loading: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      currentMonthSpending: 2500, // This would come from actual data
      historicalDataPoints: 6 // This would come from actual data
    }
  },
  computed: {
    forecastConfidence() {
      // Calculate confidence based on available data
      // More historical data = higher confidence
      return Math.min(60 + (this.historicalDataPoints * 8), 95)
    }
  },
  methods: {
    formatCurrency(amount) {
      return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      }).format(Math.abs(amount))
    },
    formatPercentageChange(newValue, oldValue) {
      if (!oldValue) return 0
      return Math.abs(Math.round(((newValue - oldValue) / oldValue) * 100))
    },
    getRelativeWidth(value, allForecasts) {
      const maxValue = Math.max(...allForecasts.map(f => f.forecast))
      return (value / maxValue) * 100
    },
    getTrendClass(trend) {
      const classes = {
        increasing: 'trend-increasing',
        decreasing: 'trend-decreasing',
        stable: 'trend-stable'
      }
      return classes[trend] || 'trend-stable'
    },
    formatTrendLabel(trend) {
      const labels = {
        increasing: 'Increasing',
        decreasing: 'Decreasing',
        stable: 'Stable'
      }
      return labels[trend] || 'Unknown'
    }
  }
}
</script>

<style scoped>
.forecasts-widget {
  height: 100%;
}

.forecasts-container {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
  height: 100%;
}

.forecast-section {
  background: white;
  border: 1px solid #e9ecef;
  border-radius: 8px;
  padding: 1rem;
}

.section-title {
  margin: 0 0 1rem 0;
  font-size: 0.9rem;
  font-weight: 600;
  color: #495057;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.section-title i {
  color: #007bff;
}

.forecast-card {
  text-align: center;
  padding: 1rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border-radius: 8px;
}

.forecast-value {
  font-size: 2rem;
  font-weight: 700;
  margin-bottom: 0.5rem;
}

.forecast-label {
  font-size: 0.9rem;
  opacity: 0.9;
  margin-bottom: 1rem;
}

.forecast-comparison {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.comparison-indicator {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  font-weight: 600;
  font-size: 0.875rem;
}

.comparison-indicator.increase {
  color: #ffc107;
}

.comparison-indicator.decrease {
  color: #28a745;
}

.comparison-text {
  font-size: 0.875rem;
  opacity: 0.8;
}

.category-forecasts {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.category-forecast-item {
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
  height: 6px;
  background: #e9ecef;
  border-radius: 3px;
  overflow: hidden;
}

.category-fill {
  height: 100%;
  background: linear-gradient(90deg, #007bff, #0056b3);
  border-radius: 3px;
  transition: width 0.3s ease;
}

.category-change {
  text-align: right;
}

.change-indicator {
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
  font-size: 0.75rem;
  font-weight: 600;
}

.change-indicator.increase {
  color: #dc3545;
}

.change-indicator.decrease {
  color: #28a745;
}

.trend-analysis {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.trend-card {
  background: #f8f9fa;
  padding: 1rem;
  border-radius: 6px;
  border: 1px solid #e9ecef;
}

.trend-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.trend-label {
  font-size: 0.875rem;
  color: #495057;
  font-weight: 500;
}

.trend-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 600;
}

.trend-increasing {
  background: #f8d7da;
  color: #721c24;
}

.trend-decreasing {
  background: #d4edda;
  color: #155724;
}

.trend-stable {
  background: #d1ecf1;
  color: #0c5460;
}

.trend-details {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.trend-chart {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.chart-container {
  flex: 1;
  height: 8px;
  background: #e9ecef;
  border-radius: 4px;
  overflow: hidden;
}

.trend-line {
  height: 100%;
  border-radius: 4px;
  transition: width 0.3s ease;
}

.trend-line.trend-increasing {
  background: #dc3545;
}

.trend-line.trend-decreasing {
  background: #28a745;
}

.trend-line.trend-stable {
  background: #17a2b8;
}

.trend-percentage {
  font-size: 0.875rem;
  font-weight: 600;
  color: #495057;
  min-width: 50px;
  text-align: right;
}

.confidence-meter {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.confidence-bar {
  height: 12px;
  background: #e9ecef;
  border-radius: 6px;
  overflow: hidden;
}

.confidence-fill {
  height: 100%;
  background: linear-gradient(90deg, #28a745, #20c997);
  border-radius: 6px;
  transition: width 0.3s ease;
}

.confidence-label {
  text-align: center;
  font-weight: 600;
  color: #495057;
}

.confidence-note {
  text-align: center;
  color: #6c757d;
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
  .forecasts-container {
    gap: 1rem;
  }
  
  .forecast-value {
    font-size: 1.75rem;
  }
  
  .category-info {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.25rem;
  }
  
  .trend-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;
  }
  
  .trend-chart {
    flex-direction: column;
    gap: 0.5rem;
  }
  
  .trend-percentage {
    text-align: center;
    min-width: auto;
  }
}
</style>