<template>
  <div class="spending-patterns-widget">
    <template v-if="loading">
      <div class="text-center py-4">
        <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
        <p class="mt-2 mb-0">Analyzing spending patterns...</p>
      </div>
    </template>
    
    <template v-else-if="patterns && patterns.length > 0">
      <div class="patterns-container">
        <!-- Pattern Type Tabs -->
        <div class="pattern-tabs">
          <button
            v-for="type in patternTypes"
            :key="type.key"
            @click="activeTab = type.key"
            :class="['tab-button', { active: activeTab === type.key }]"
          >
            <i :class="type.icon"></i>
            {{ type.label }}
            <span class="badge">{{ getPatternsByType(type.key).length }}</span>
          </button>
        </div>

        <!-- Pattern Content -->
        <div class="pattern-content">
          <template v-if="filteredPatterns.length > 0">
            <div class="patterns-list">
              <div 
                v-for="pattern in filteredPatterns" 
                :key="pattern.id"
                class="pattern-item"
              >
                <div class="pattern-header">
                  <div class="pattern-info">
                    <h6 class="pattern-name">{{ pattern.pattern_name }}</h6>
                    <p class="pattern-description">{{ pattern.description }}</p>
                  </div>
                  <div class="pattern-metrics">
                    <div class="confidence-score">
                      <span class="metric-label">Confidence</span>
                      <span class="metric-value" :class="getConfidenceClass(pattern.confidence_score)">
                        {{ Math.round(pattern.confidence_score) }}%
                      </span>
                    </div>
                  </div>
                </div>
                
                <div class="pattern-details">
                  <div class="detail-item">
                    <i class="fas fa-calendar-alt"></i>
                    <span>{{ getFrequencyDescription(pattern) }}</span>
                  </div>
                  <div class="detail-item" v-if="pattern.impact_amount">
                    <i class="fas fa-dollar-sign"></i>
                    <span>${{ formatCurrency(pattern.impact_amount) }}</span>
                  </div>
                  <div class="detail-item" v-if="pattern.last_detected">
                    <i class="fas fa-clock"></i>
                    <span>Last detected {{ formatDate(pattern.last_detected) }}</span>
                  </div>
                </div>

                <!-- Pattern Visualization -->
                <div class="pattern-viz" v-if="pattern.pattern_data">
                  <template v-if="pattern.pattern_type === 'monthly_recurring'">
                    <div class="recurring-viz">
                      <div class="viz-header">Monthly Pattern</div>
                      <div class="recurring-timeline">
                        <div 
                          v-for="i in 12" 
                          :key="i"
                          class="timeline-dot"
                          :class="{ active: i % (pattern.frequency || 1) === 0 }"
                        ></div>
                      </div>
                    </div>
                  </template>
                  
                  <template v-else-if="pattern.pattern_type === 'category_spike'">
                    <div class="spike-viz">
                      <div class="viz-header">Category Impact</div>
                      <div class="spike-bar">
                        <div 
                          class="spike-fill" 
                          :style="{ width: Math.min(pattern.pattern_data.deviation || 0, 100) + '%' }"
                        ></div>
                      </div>
                      <small class="text-muted">
                        {{ Math.round(pattern.pattern_data.deviation || 0) }}% above average
                      </small>
                    </div>
                  </template>
                </div>
              </div>
            </div>
          </template>
          
          <template v-else>
            <div class="empty-tab">
              <i class="fas fa-search fa-2x text-muted mb-2"></i>
              <p class="text-muted mb-0">No {{ activeTabLabel }} patterns detected</p>
            </div>
          </template>
        </div>
      </div>
    </template>

    <template v-else>
      <div class="empty-state py-4">
        <i class="fas fa-chart-pie fa-2x text-muted mb-2"></i>
        <p class="text-muted mb-0">No spending patterns detected yet</p>
        <small class="text-muted">Patterns will appear as you track more expenses</small>
      </div>
    </template>
  </div>
</template>

<script>
export default {
  name: 'SpendingPatternsWidget',
  props: {
    patterns: {
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
      activeTab: 'all',
      patternTypes: [
        { 
          key: 'all', 
          label: 'All', 
          icon: 'fas fa-th-list' 
        },
        { 
          key: 'monthly_recurring', 
          label: 'Recurring', 
          icon: 'fas fa-sync-alt' 
        },
        { 
          key: 'category_spike', 
          label: 'Spikes', 
          icon: 'fas fa-arrow-up' 
        },
        { 
          key: 'seasonal', 
          label: 'Seasonal', 
          icon: 'fas fa-leaf' 
        },
        { 
          key: 'anomaly', 
          label: 'Anomalies', 
          icon: 'fas fa-exclamation-triangle' 
        }
      ]
    }
  },
  computed: {
    filteredPatterns() {
      if (this.activeTab === 'all') {
        return this.patterns
      }
      return this.getPatternsByType(this.activeTab)
    },
    activeTabLabel() {
      const tab = this.patternTypes.find(t => t.key === this.activeTab)
      return tab ? tab.label.toLowerCase() : 'patterns'
    }
  },
  methods: {
    getPatternsByType(type) {
      if (type === 'all') return this.patterns
      return this.patterns.filter(pattern => pattern.pattern_type === type)
    },
    getConfidenceClass(score) {
      if (score >= 90) return 'confidence-excellent'
      if (score >= 80) return 'confidence-good'
      if (score >= 70) return 'confidence-fair'
      return 'confidence-poor'
    },
    getFrequencyDescription(pattern) {
      if (pattern.pattern_type === 'monthly_recurring') {
        return `Every ${pattern.frequency || 1} month(s)`
      }
      if (pattern.pattern_type === 'weekly_recurring') {
        return `Every ${pattern.frequency || 1} week(s)`
      }
      if (pattern.pattern_type === 'daily_recurring') {
        return `Every ${pattern.frequency || 1} day(s)`
      }
      if (pattern.pattern_type === 'seasonal') {
        return 'Seasonal pattern'
      }
      if (pattern.pattern_type === 'category_spike') {
        return 'Category spending increase'
      }
      return 'Pattern detected'
    },
    formatCurrency(amount) {
      return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      }).format(Math.abs(amount))
    },
    formatDate(dateString) {
      const date = new Date(dateString)
      const now = new Date()
      const diffTime = Math.abs(now - date)
      const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
      
      if (diffDays === 1) return 'yesterday'
      if (diffDays < 7) return `${diffDays} days ago`
      if (diffDays < 30) return `${Math.ceil(diffDays / 7)} weeks ago`
      return `${Math.ceil(diffDays / 30)} months ago`
    }
  }
}
</script>

<style scoped>
.spending-patterns-widget {
  height: 100%;
}

.patterns-container {
  display: flex;
  flex-direction: column;
  height: 100%;
}

.pattern-tabs {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 1.5rem;
  flex-wrap: wrap;
}

.tab-button {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  border: 1px solid #dee2e6;
  background: white;
  color: #6c757d;
  border-radius: 20px;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.tab-button:hover {
  background: #f8f9fa;
  border-color: #adb5bd;
}

.tab-button.active {
  background: #007bff;
  color: white;
  border-color: #007bff;
}

.badge {
  background: rgba(0, 0, 0, 0.1);
  color: inherit;
  padding: 0.125rem 0.375rem;
  border-radius: 10px;
  font-size: 0.75rem;
  font-weight: 600;
}

.tab-button.active .badge {
  background: rgba(255, 255, 255, 0.2);
}

.pattern-content {
  flex: 1;
  overflow-y: auto;
}

.patterns-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.pattern-item {
  padding: 1rem;
  border: 1px solid #e9ecef;
  border-radius: 8px;
  background: #f8f9fa;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.pattern-item:hover {
  transform: translateY(-1px);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.pattern-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 0.75rem;
}

.pattern-info {
  flex: 1;
}

.pattern-name {
  margin: 0 0 0.25rem 0;
  font-size: 1rem;
  font-weight: 600;
  color: #212529;
}

.pattern-description {
  margin: 0;
  font-size: 0.875rem;
  color: #6c757d;
}

.pattern-metrics {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
}

.confidence-score {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.25rem;
}

.metric-label {
  font-size: 0.75rem;
  color: #6c757d;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.metric-value {
  font-size: 0.875rem;
  font-weight: 600;
  padding: 0.25rem 0.5rem;
  border-radius: 12px;
}

.confidence-excellent {
  background: #d4edda;
  color: #155724;
}

.confidence-good {
  background: #d1ecf1;
  color: #0c5460;
}

.confidence-fair {
  background: #fff3cd;
  color: #856404;
}

.confidence-poor {
  background: #f8d7da;
  color: #721c24;
}

.pattern-details {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  margin-bottom: 1rem;
}

.detail-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
  color: #495057;
}

.detail-item i {
  color: #6c757d;
  width: 14px;
}

.pattern-viz {
  background: white;
  padding: 0.75rem;
  border-radius: 6px;
  border: 1px solid #e9ecef;
}

.viz-header {
  font-size: 0.8rem;
  font-weight: 600;
  color: #495057;
  margin-bottom: 0.5rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.recurring-timeline {
  display: flex;
  gap: 0.25rem;
  align-items: center;
}

.timeline-dot {
  width: 12px;
  height: 12px;
  border-radius: 50%;
  background: #e9ecef;
  transition: background-color 0.2s ease;
}

.timeline-dot.active {
  background: #007bff;
}

.spike-viz {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.spike-bar {
  height: 8px;
  background: #e9ecef;
  border-radius: 4px;
  overflow: hidden;
}

.spike-fill {
  height: 100%;
  background: linear-gradient(90deg, #ffc107, #fd7e14);
  border-radius: 4px;
  transition: width 0.3s ease;
}

.empty-tab, .empty-state {
  text-align: center;
  padding: 2rem 0;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .pattern-header {
    flex-direction: column;
    gap: 0.75rem;
  }
  
  .pattern-metrics {
    align-self: flex-start;
  }
  
  .pattern-details {
    flex-direction: column;
    gap: 0.5rem;
  }
  
  .pattern-tabs {
    justify-content: center;
  }
}
</style>