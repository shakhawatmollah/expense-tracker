<template>
  <div class="recommendations-widget">
    <template v-if="loading">
      <div class="text-center py-4">
        <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
        <p class="mt-2 mb-0">Generating recommendations...</p>
      </div>
    </template>

    <template v-else-if="recommendations && recommendations.length > 0">
      <div class="recommendations-list">
        <div
          v-for="(recommendation, index) in recommendations.slice(0, 5)"
          :key="index"
          class="recommendation-item"
          :class="getRecommendationClass(recommendation.type)"
        >
          <div class="recommendation-header">
            <div class="recommendation-icon">
              <i :class="getRecommendationIcon(recommendation.type)"></i>
            </div>
            <div class="recommendation-meta">
              <span class="recommendation-type">{{ formatRecommendationType(recommendation.type) }}</span>
              <span class="recommendation-priority" v-if="recommendation.priority">
                {{ recommendation.priority }} Priority
              </span>
            </div>
          </div>

          <div class="recommendation-content">
            <p class="recommendation-message">{{ recommendation.message }}</p>

            <!-- Cost Reduction Recommendation -->
            <template v-if="recommendation.type === 'cost_reduction'">
              <div class="recommendation-details">
                <div class="detail-row" v-if="recommendation.category">
                  <span class="detail-label">Category:</span>
                  <span class="detail-value">{{ recommendation.category }}</span>
                </div>
                <div class="detail-row" v-if="recommendation.potential_savings">
                  <span class="detail-label">Potential Savings:</span>
                  <span class="detail-value savings-amount">
                    ${{ formatCurrency(recommendation.potential_savings) }}/month
                  </span>
                </div>
              </div>
            </template>

            <!-- Budget Optimization Recommendation -->
            <template v-else-if="recommendation.type === 'budget_optimization'">
              <div class="recommendation-details">
                <div class="detail-row" v-if="recommendation.suggested_amount">
                  <span class="detail-label">Suggested Budget:</span>
                  <span class="detail-value">${{ formatCurrency(recommendation.suggested_amount) }}</span>
                </div>
              </div>
            </template>

            <!-- Spending Alert Recommendation -->
            <template v-else-if="recommendation.type === 'spending_alert'">
              <div class="recommendation-details">
                <div class="detail-row" v-if="recommendation.current_amount">
                  <span class="detail-label">Current Spending:</span>
                  <span class="detail-value">${{ formatCurrency(recommendation.current_amount) }}</span>
                </div>
                <div class="detail-row" v-if="recommendation.limit_amount">
                  <span class="detail-label">Recommended Limit:</span>
                  <span class="detail-value">${{ formatCurrency(recommendation.limit_amount) }}</span>
                </div>
              </div>
            </template>

            <!-- Action Buttons -->
            <div class="recommendation-actions">
              <button class="btn btn-sm btn-outline-primary" @click="applyRecommendation(recommendation)">
                <i class="fas fa-check"></i>
                Apply
              </button>
              <button class="btn btn-sm btn-outline-secondary" @click="dismissRecommendation(index)">
                <i class="fas fa-times"></i>
                Dismiss
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- View All Link -->
      <div class="view-all" v-if="recommendations.length > 5">
        <button class="btn btn-outline-primary btn-sm">View All Recommendations ({{ recommendations.length }})</button>
      </div>
    </template>

    <template v-else>
      <div class="empty-state py-4">
        <i class="fas fa-magic fa-2x text-muted mb-2"></i>
        <p class="text-muted mb-0">No recommendations available</p>
        <small class="text-muted">Recommendations will appear based on your spending patterns</small>
      </div>
    </template>

    <!-- Success Message -->
    <div v-if="successMessage" class="alert alert-success alert-dismissible fade show mt-3" role="alert">
      {{ successMessage }}
      <button type="button" class="btn-close" @click="successMessage = ''"></button>
    </div>
  </div>
</template>

<script>
  export default {
    name: 'RecommendationsWidget',
    props: {
      recommendations: {
        type: Array,
        default: () => []
      },
      loading: {
        type: Boolean,
        default: false
      }
    },
    data() {
      return {
        successMessage: ''
      }
    },
    methods: {
      getRecommendationClass(type) {
        const classes = {
          cost_reduction: 'recommendation-savings',
          budget_optimization: 'recommendation-budget',
          spending_alert: 'recommendation-warning',
          investment: 'recommendation-investment',
          saving_goal: 'recommendation-goal'
        }
        return classes[type] || 'recommendation-default'
      },
      getRecommendationIcon(type) {
        const icons = {
          cost_reduction: 'fas fa-piggy-bank',
          budget_optimization: 'fas fa-chart-pie',
          spending_alert: 'fas fa-exclamation-triangle',
          investment: 'fas fa-chart-line',
          saving_goal: 'fas fa-bullseye'
        }
        return icons[type] || 'fas fa-lightbulb'
      },
      formatRecommendationType(type) {
        const types = {
          cost_reduction: 'Cost Reduction',
          budget_optimization: 'Budget Optimization',
          spending_alert: 'Spending Alert',
          investment: 'Investment Opportunity',
          saving_goal: 'Savings Goal'
        }
        return types[type] || type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
      },
      formatCurrency(amount) {
        return new Intl.NumberFormat('en-US', {
          minimumFractionDigits: 2,
          maximumFractionDigits: 2
        }).format(Math.abs(amount))
      },
      async applyRecommendation(recommendation) {
        try {
          // This would integrate with your budget/expense management system
          console.log('Applying recommendation:', recommendation)

          // Show success message
          this.successMessage = 'Recommendation applied successfully!'

          // Hide success message after 3 seconds
          setTimeout(() => {
            this.successMessage = ''
          }, 3000)
        } catch (error) {
          console.error('Error applying recommendation:', error)
          // You could show an error message here
        }
      },
      dismissRecommendation(index) {
        // Remove the recommendation from the list
        this.$emit('dismiss-recommendation', index)
      }
    }
  }
</script>

<style scoped>
  .recommendations-widget {
    height: 100%;
    display: flex;
    flex-direction: column;
  }

  .recommendations-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    flex: 1;
    overflow-y: auto;
  }

  .recommendation-item {
    padding: 1rem;
    border-radius: 8px;
    border-left: 4px solid #007bff;
    background: #f8f9fa;
    transition:
      transform 0.2s ease,
      box-shadow 0.2s ease;
  }

  .recommendation-item:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  }

  .recommendation-savings {
    border-left-color: #28a745;
    background: #f8fff9;
  }

  .recommendation-budget {
    border-left-color: #007bff;
    background: #f8f9ff;
  }

  .recommendation-warning {
    border-left-color: #ffc107;
    background: #fffcf0;
  }

  .recommendation-investment {
    border-left-color: #6f42c1;
    background: #fbf8ff;
  }

  .recommendation-goal {
    border-left-color: #fd7e14;
    background: #fff9f5;
  }

  .recommendation-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0.75rem;
  }

  .recommendation-icon {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 0.875rem;
  }

  .recommendation-savings .recommendation-icon {
    background: #28a745;
  }

  .recommendation-budget .recommendation-icon {
    background: #007bff;
  }

  .recommendation-warning .recommendation-icon {
    background: #ffc107;
    color: #212529;
  }

  .recommendation-investment .recommendation-icon {
    background: #6f42c1;
  }

  .recommendation-goal .recommendation-icon {
    background: #fd7e14;
  }

  .recommendation-meta {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
  }

  .recommendation-type {
    font-weight: 600;
    color: #212529;
    font-size: 0.875rem;
  }

  .recommendation-priority {
    font-size: 0.75rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .recommendation-content {
    display: flex;
    flex-direction: column;
    gap: 1rem;
  }

  .recommendation-message {
    margin: 0;
    color: #495057;
    font-size: 0.9rem;
    line-height: 1.4;
  }

  .recommendation-details {
    background: white;
    padding: 0.75rem;
    border-radius: 6px;
    border: 1px solid #e9ecef;
  }

  .detail-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
  }

  .detail-row:last-child {
    margin-bottom: 0;
  }

  .detail-label {
    font-size: 0.875rem;
    color: #6c757d;
    font-weight: 500;
  }

  .detail-value {
    font-size: 0.875rem;
    color: #212529;
    font-weight: 600;
  }

  .savings-amount {
    color: #28a745;
  }

  .recommendation-actions {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
  }

  .recommendation-actions .btn {
    display: flex;
    align-items: center;
    gap: 0.375rem;
    font-size: 0.8rem;
    padding: 0.375rem 0.75rem;
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
    .detail-row {
      flex-direction: column;
      align-items: flex-start;
      gap: 0.25rem;
    }

    .recommendation-actions {
      justify-content: center;
    }

    .recommendation-header {
      text-align: center;
      justify-content: center;
    }
  }
</style>
