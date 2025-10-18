<template>
  <div class="financial-health-widget">
    <template v-if="loading">
      <div class="text-center py-4">
        <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
        <p class="mt-2 mb-0">Calculating health score...</p>
      </div>
    </template>
    
    <template v-else-if="healthData?.data?.current">
      <div class="health-score-container">
        <!-- Overall Score Circle -->
        <div class="score-circle-container">
          <div class="score-circle" :style="{ '--score-color': healthStatus?.color }">
            <div class="score-inner">
              <div class="score-value">{{ Math.round(healthData.data.current.overall_score) }}</div>
              <div class="score-label">Score</div>
            </div>
            <svg class="score-ring" width="120" height="120">
              <circle
                cx="60"
                cy="60"
                r="50"
                fill="none"
                stroke="#e9ecef"
                stroke-width="8"
              />
              <circle
                cx="60"
                cy="60"
                r="50"
                fill="none"
                :stroke="healthStatus?.color || '#007bff'"
                stroke-width="8"
                stroke-linecap="round"
                :stroke-dasharray="circumference"
                :stroke-dashoffset="dashOffset"
                transform="rotate(-90 60 60)"
              />
            </svg>
          </div>
          <div class="health-status">
            <span class="status-badge" :style="{ backgroundColor: healthStatus?.color }">
              {{ healthStatus?.status }}
            </span>
          </div>
        </div>

        <!-- Component Scores -->
        <div class="component-scores">
          <div class="score-item">
            <div class="score-item-header">
              <span class="score-item-label">Expense Control</span>
              <span class="score-item-value">
                {{ Math.round(healthData.data.current.expense_control_score) }}%
              </span>
            </div>
            <div class="score-bar">
              <div 
                class="score-bar-fill" 
                :style="{ 
                  width: healthData.data.current.expense_control_score + '%',
                  backgroundColor: getScoreColor(healthData.data.current.expense_control_score)
                }"
              ></div>
            </div>
          </div>

          <div class="score-item">
            <div class="score-item-header">
              <span class="score-item-label">Budget Adherence</span>
              <span class="score-item-value">
                {{ Math.round(healthData.data.current.budget_adherence_score) }}%
              </span>
            </div>
            <div class="score-bar">
              <div 
                class="score-bar-fill" 
                :style="{ 
                  width: healthData.data.current.budget_adherence_score + '%',
                  backgroundColor: getScoreColor(healthData.data.current.budget_adherence_score)
                }"
              ></div>
            </div>
          </div>

          <div class="score-item">
            <div class="score-item-header">
              <span class="score-item-label">Savings Rate</span>
              <span class="score-item-value">
                {{ Math.round(healthData.data.current.savings_rate_score) }}%
              </span>
            </div>
            <div class="score-bar">
              <div 
                class="score-bar-fill" 
                :style="{ 
                  width: healthData.data.current.savings_rate_score + '%',
                  backgroundColor: getScoreColor(healthData.data.current.savings_rate_score)
                }"
              ></div>
            </div>
          </div>

          <div class="score-item">
            <div class="score-item-header">
              <span class="score-item-label">Debt Ratio</span>
              <span class="score-item-value">
                {{ Math.round(healthData.data.current.debt_ratio_score) }}%
              </span>
            </div>
            <div class="score-bar">
              <div 
                class="score-bar-fill" 
                :style="{ 
                  width: healthData.data.current.debt_ratio_score + '%',
                  backgroundColor: getScoreColor(healthData.data.current.debt_ratio_score)
                }"
              ></div>
            </div>
          </div>
        </div>

        <!-- Quick Stats -->
        <div class="health-stats" v-if="healthData.data.current.score_data">
          <div class="stat-item">
            <div class="stat-label">Total Expenses</div>
            <div class="stat-value">${{ formatCurrency(healthData.data.current.score_data.total_expenses) }}</div>
          </div>
          <div class="stat-item">
            <div class="stat-label">Budget Remaining</div>
            <div class="stat-value" :class="{ 'text-danger': healthData.data.current.score_data.budget_remaining < 0 }">
              ${{ formatCurrency(healthData.data.current.score_data.budget_remaining) }}
            </div>
          </div>
        </div>
      </div>
    </template>

    <template v-else>
      <div class="empty-state py-4">
        <i class="fas fa-heartbeat fa-2x text-muted mb-2"></i>
        <p class="text-muted mb-0">No health data available</p>
      </div>
    </template>
  </div>
</template>

<script>
export default {
  name: 'FinancialHealthWidget',
  props: {
    healthData: {
      type: Object,
      default: null
    },
    loading: {
      type: Boolean,
      default: false
    }
  },
  computed: {
    healthStatus() {
      if (!this.healthData?.data?.current) return null
      
      const score = this.healthData.data.current.overall_score
      if (score >= 90) return { status: 'Excellent', color: '#22c55e' }
      if (score >= 80) return { status: 'Very Good', color: '#84cc16' }
      if (score >= 70) return { status: 'Good', color: '#eab308' }
      if (score >= 60) return { status: 'Fair', color: '#f97316' }
      if (score >= 50) return { status: 'Poor', color: '#ef4444' }
      return { status: 'Critical', color: '#dc2626' }
    },
    circumference() {
      return 2 * Math.PI * 50 // radius = 50
    },
    dashOffset() {
      if (!this.healthData?.data?.current) return this.circumference
      const score = this.healthData.data.current.overall_score
      return this.circumference - (score / 100) * this.circumference
    }
  },
  methods: {
    getScoreColor(score) {
      if (score >= 90) return '#22c55e'
      if (score >= 80) return '#84cc16'
      if (score >= 70) return '#eab308'
      if (score >= 60) return '#f97316'
      if (score >= 50) return '#ef4444'
      return '#dc2626'
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
.financial-health-widget {
  height: 100%;
}

.health-score-container {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.score-circle-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
}

.score-circle {
  position: relative;
  width: 120px;
  height: 120px;
}

.score-inner {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  text-align: center;
}

.score-value {
  font-size: 2rem;
  font-weight: 700;
  color: var(--score-color, #007bff);
  line-height: 1;
}

.score-label {
  font-size: 0.875rem;
  color: #6c757d;
  font-weight: 500;
}

.score-ring {
  transform: rotate(-90deg);
}

.status-badge {
  padding: 0.375rem 0.75rem;
  border-radius: 20px;
  color: white;
  font-size: 0.875rem;
  font-weight: 600;
}

.component-scores {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.score-item {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.score-item-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.score-item-label {
  font-size: 0.875rem;
  color: #495057;
  font-weight: 500;
}

.score-item-value {
  font-size: 0.875rem;
  font-weight: 600;
  color: #212529;
}

.score-bar {
  height: 6px;
  background-color: #e9ecef;
  border-radius: 3px;
  overflow: hidden;
}

.score-bar-fill {
  height: 100%;
  border-radius: 3px;
  transition: width 0.3s ease;
}

.health-stats {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
  padding-top: 1rem;
  border-top: 1px solid #e9ecef;
}

.stat-item {
  text-align: center;
}

.stat-label {
  font-size: 0.8rem;
  color: #6c757d;
  margin-bottom: 0.25rem;
}

.stat-value {
  font-size: 1.1rem;
  font-weight: 600;
  color: #212529;
}

.empty-state {
  text-align: center;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .health-stats {
    grid-template-columns: 1fr;
    gap: 0.75rem;
  }
  
  .score-circle {
    width: 100px;
    height: 100px;
  }
  
  .score-ring {
    width: 100px;
    height: 100px;
  }
  
  .score-ring circle {
    r: 40;
    cx: 50;
    cy: 50;
  }
  
  .score-value {
    font-size: 1.75rem;
  }
}
</style>