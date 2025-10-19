<template>
  <div class="chart-container">
    <div class="chart-header">
      <h3 class="chart-title">Budget Distribution by Category</h3>
      <div class="chart-legend">
        <div 
          v-for="(item, index) in chartData" 
          :key="item.name"
          class="legend-item"
        >
          <div 
            class="legend-color" 
            :style="{ backgroundColor: getColor(index) }"
          ></div>
          <span class="legend-label">{{ item.name }}</span>
        </div>
      </div>
    </div>
    
    <div class="chart-content">
      <!-- Pie Chart using Chart.js -->
      <canvas ref="chartCanvas" class="chart-canvas"></canvas>
    </div>
    
    <div class="chart-summary">
      <div class="summary-grid">
        <div 
          v-for="(item, index) in chartData" 
          :key="item.name"
          class="summary-item"
        >
          <div class="summary-header">
            <div 
              class="summary-color" 
              :style="{ backgroundColor: getColor(index) }"
            ></div>
            <span class="summary-name">{{ item.name }}</span>
          </div>
          <div class="summary-stats">
            <div class="stat-row">
              <span class="stat-label">Spent:</span>
              <span class="stat-value">${{ formatAmount(item.value) }}</span>
            </div>
            <div class="stat-row">
              <span class="stat-label">Budget:</span>
              <span class="stat-value">${{ formatAmount(item.budget) }}</span>
            </div>
            <div class="stat-row">
              <span class="stat-label">Usage:</span>
              <span class="stat-value">{{ getUsagePercentage(item) }}%</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, onUnmounted, watch } from 'vue'
import { Chart, registerables } from 'chart.js'
import { formatCurrency } from '@/utils/formatters'

Chart.register(...registerables)

export default {
  name: 'BudgetCategoryChart',
  props: {
    data: {
      type: Array,
      required: true,
      default: () => []
    }
  },
  setup(props) {
    const chartCanvas = ref(null)
    let chartInstance = null
    
    // Chart colors
    const colors = [
      '#3B82F6', // Blue
      '#EF4444', // Red
      '#10B981', // Green
      '#F59E0B', // Yellow
      '#8B5CF6', // Purple
      '#EC4899', // Pink
      '#6B7280', // Gray
      '#14B8A6', // Teal
      '#F97316', // Orange
      '#84CC16'  // Lime
    ]
    
    const chartData = ref([])
    
    // Methods
    const formatAmount = (amount) => {
      return formatCurrency(amount)
    }
    
    const getColor = (index) => {
      return colors[index % colors.length]
    }
    
    const getUsagePercentage = (item) => {
      if (item.budget === 0) return 0
      return Math.round((item.value / item.budget) * 100)
    }
    
    const createChart = () => {
      if (!chartCanvas.value || chartData.value.length === 0) return
      
      // Destroy existing chart
      if (chartInstance) {
        chartInstance.destroy()
      }
      
      const ctx = chartCanvas.value.getContext('2d')
      
      chartInstance = new Chart(ctx, {
        type: 'doughnut',
        data: {
          labels: chartData.value.map(item => item.name),
          datasets: [{
            data: chartData.value.map(item => item.value),
            backgroundColor: chartData.value.map((_, index) => getColor(index)),
            borderColor: chartData.value.map((_, index) => getColor(index)),
            borderWidth: 2,
            hoverOffset: 10
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              display: false // We'll use custom legend
            },
            tooltip: {
              callbacks: {
                label: (context) => {
                  const item = chartData.value[context.dataIndex]
                  const percentage = getUsagePercentage(item)
                  return [
                    `${context.label}: $${formatAmount(context.parsed)}`,
                    `Budget: $${formatAmount(item.budget)}`,
                    `Usage: ${percentage}%`
                  ]
                }
              }
            }
          },
          cutout: '60%',
          animation: {
            duration: 1000,
            easing: 'easeOutCubic'
          }
        }
      })
    }
    
    // Watch for data changes
    watch(() => props.data, (newData) => {
      chartData.value = newData.filter(item => item.value > 0)
      createChart()
    }, { immediate: true })
    
    // Lifecycle
    onMounted(() => {
      chartData.value = props.data.filter(item => item.value > 0)
      createChart()
    })
    
    onUnmounted(() => {
      if (chartInstance) {
        chartInstance.destroy()
      }
    })
    
    return {
      chartCanvas,
      chartData,
      formatAmount,
      getColor,
      getUsagePercentage
    }
  }
}
</script>

<style scoped>
.chart-container {
  background: white;
  border-radius: 8px;
  padding: 24px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.chart-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 24px;
}

.chart-title {
  font-size: 18px;
  font-weight: 600;
  color: #1F2937;
  margin: 0;
}

.chart-legend {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
}

.legend-item {
  display: flex;
  align-items: center;
  gap: 6px;
}

.legend-color {
  width: 12px;
  height: 12px;
  border-radius: 3px;
}

.legend-label {
  font-size: 12px;
  color: #6B7280;
}

.chart-content {
  position: relative;
  height: 300px;
  margin-bottom: 24px;
}

.chart-canvas {
  max-width: 100%;
  max-height: 100%;
}

.chart-summary {
  border-top: 1px solid #E5E7EB;
  padding-top: 16px;
}

.summary-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
}

.summary-item {
  padding: 12px;
  background: #F9FAFB;
  border-radius: 6px;
}

.summary-header {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 8px;
}

.summary-color {
  width: 16px;
  height: 16px;
  border-radius: 4px;
}

.summary-name {
  font-weight: 500;
  color: #1F2937;
}

.summary-stats {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.stat-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.stat-label {
  font-size: 12px;
  color: #6B7280;
}

.stat-value {
  font-size: 12px;
  font-weight: 500;
  color: #1F2937;
}

@media (max-width: 768px) {
  .chart-header {
    flex-direction: column;
    gap: 16px;
  }
  
  .chart-legend {
    justify-content: center;
  }
  
  .summary-grid {
    grid-template-columns: 1fr;
  }
}
</style>