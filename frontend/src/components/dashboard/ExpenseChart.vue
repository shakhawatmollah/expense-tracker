<template>
  <div class="modern-chart-card">
    <div class="chart-header">
      <div class="header-content">
        <div class="header-icon">
          <i class="fas fa-chart-line"></i>
        </div>
        <div class="header-text">
          <h3 class="chart-title">Expense Trends</h3>
          <p class="chart-subtitle">{{ timeRangeText }} spending overview</p>
        </div>
      </div>
      <div class="chart-controls">
        <div class="time-selector">
          <button
            v-for="period in timePeriods"
            :key="period.value"
            @click="selectedPeriod = period.value"
            :class="['time-btn', { active: selectedPeriod === period.value }]"
          >
            {{ period.label }}
          </button>
        </div>
      </div>
    </div>

    <div class="chart-stats">
      <div class="stat-item">
        <div class="stat-value">{{ formatCurrency(totalAmount) }}</div>
        <div class="stat-label">Total Spending</div>
        <div class="stat-trend" :class="trendClass">
          <i :class="trendIcon"></i>
          <span>{{ trendPercentage }}%</span>
        </div>
      </div>
      <div class="stat-item">
        <div class="stat-value">{{ formatCurrency(averageAmount) }}</div>
        <div class="stat-label">Average per {{ selectedPeriod === '6months' ? 'Month' : 'Day' }}</div>
      </div>
      <div class="stat-item">
        <div class="stat-value">{{ formatCurrency(highestAmount) }}</div>
        <div class="stat-label">Highest {{ selectedPeriod === '6months' ? 'Month' : 'Day' }}</div>
      </div>
    </div>

    <div class="chart-container">
      <Line v-if="chartData && !loading" :data="chartData" :options="chartOptions" :key="chartKey" />

      <!-- Enhanced Loading State -->
      <div v-else-if="loading" class="loading-state">
        <div class="loading-animation">
          <div class="loading-bars">
            <div v-for="i in 6" :key="i" class="loading-bar" :style="{ animationDelay: i * 0.1 + 's' }"></div>
          </div>
          <p class="loading-text">Loading chart data...</p>
        </div>
      </div>

      <!-- Enhanced Empty State -->
      <div v-else class="empty-state">
        <div class="empty-icon">
          <i class="fas fa-chart-line"></i>
        </div>
        <h4 class="empty-title">No Data Available</h4>
        <p class="empty-message">Start tracking your expenses to see spending trends</p>
        <button class="empty-action-btn" @click="$emit('add-expense')">
          <i class="fas fa-plus"></i>
          Add Your First Expense
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
  import { computed, onMounted, watch, ref, nextTick } from 'vue'
  import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
    Filler
  } from 'chart.js'
  import { Line } from 'vue-chartjs'
  import { useDashboardStore } from '@/stores/dashboard'
  import { format, subDays, subMonths } from 'date-fns'

  ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, Filler)

  // Emits
  const emit = defineEmits(['add-expense'])

  const dashboardStore = useDashboardStore()
  const selectedPeriod = ref('6months')
  const chartKey = ref(0)

  const timePeriods = [
    { label: '7D', value: '7days' },
    { label: '30D', value: '30days' },
    { label: '6M', value: '6months' },
    { label: '1Y', value: '1year' }
  ]

  const loading = computed(() => dashboardStore.loading)
  const trends = computed(() => dashboardStore.trends)

  // Watch for trends data changes and period selection
  watch(
    trends,
    newTrends => {
      console.log('Trends data updated:', newTrends)
      chartKey.value++
    },
    { immediate: true }
  )

  watch(selectedPeriod, async newPeriod => {
    await dashboardStore.fetchTrends(getPeriodMonths(newPeriod))
    chartKey.value++
  })

  // Helper function to get number of months for API
  const getPeriodMonths = period => {
    switch (period) {
      case '7days':
        return 1
      case '30days':
        return 2
      case '6months':
        return 6
      case '1year':
        return 12
      default:
        return 6
    }
  }

  // Computed properties for stats
  const totalAmount = computed(() => {
    if (!trends.value || trends.value.length === 0) return 0
    return trends.value.reduce((sum, item) => sum + (parseFloat(item.total) || 0), 0)
  })

  const averageAmount = computed(() => {
    if (!trends.value || trends.value.length === 0) return 0
    return totalAmount.value / trends.value.length
  })

  const highestAmount = computed(() => {
    if (!trends.value || trends.value.length === 0) return 0
    return Math.max(...trends.value.map(item => parseFloat(item.total) || 0))
  })

  const trendPercentage = computed(() => {
    if (!trends.value || trends.value.length < 2) return 0
    const current = parseFloat(trends.value[trends.value.length - 1]?.total) || 0
    const previous = parseFloat(trends.value[trends.value.length - 2]?.total) || 0
    if (previous === 0) return 0
    return Math.round(((current - previous) / previous) * 100)
  })

  const trendClass = computed(() => ({
    'trend-up': trendPercentage.value > 0,
    'trend-down': trendPercentage.value < 0,
    'trend-neutral': trendPercentage.value === 0
  }))

  const trendIcon = computed(() => {
    if (trendPercentage.value > 0) return 'fas fa-arrow-up'
    if (trendPercentage.value < 0) return 'fas fa-arrow-down'
    return 'fas fa-minus'
  })

  const timeRangeText = computed(() => {
    const period = timePeriods.find(p => p.value === selectedPeriod.value)
    return period ? period.label : 'Last 6 months'
  })

  // Helper function to format currency
  const formatCurrency = amount => {
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: 'USD',
      minimumFractionDigits: 0,
      maximumFractionDigits: 0
    }).format(amount)
  }

  const chartData = computed(() => {
    if (!trends.value || trends.value.length === 0) {
      console.log('No trends data available')
      return null
    }

    console.log('Trends data:', trends.value) // Debug log

    const gradientColors = {
      primary: ['rgba(102, 126, 234, 0.4)', 'rgba(118, 75, 162, 0.1)'],
      border: 'rgba(102, 126, 234, 1)'
    }

    return {
      labels: trends.value.map(item => {
        // Use label if available, otherwise format the date
        if (item.label) return item.label
        const date = new Date(item.date || item.month)
        return selectedPeriod.value === '7days' || selectedPeriod.value === '30days'
          ? format(date, 'MMM dd')
          : format(date, 'MMM yyyy')
      }),
      datasets: [
        {
          label:
            selectedPeriod.value === '6months' || selectedPeriod.value === '1year'
              ? 'Monthly Expenses'
              : 'Daily Expenses',
          data: trends.value.map(item => parseFloat(item.total) || 0),
          borderColor: gradientColors.border,
          backgroundColor: context => {
            const chart = context.chart
            const { ctx, chartArea } = chart
            if (!chartArea) return null

            const gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom)
            gradient.addColorStop(0, gradientColors.primary[0])
            gradient.addColorStop(1, gradientColors.primary[1])
            return gradient
          },
          fill: true,
          tension: 0.4,
          pointBackgroundColor: '#667eea',
          pointBorderColor: 'white',
          pointBorderWidth: 3,
          pointRadius: 6,
          pointHoverRadius: 8,
          pointHoverBackgroundColor: '#667eea',
          pointHoverBorderColor: 'white',
          pointHoverBorderWidth: 3,
          borderWidth: 3
        }
      ]
    }
  })

  const chartOptions = computed(() => ({
    responsive: true,
    maintainAspectRatio: false,
    interaction: {
      intersect: false,
      mode: 'index'
    },
    animation: {
      duration: 1500,
      easing: 'easeInOutCubic',
      delay: context => {
        return context.dataIndex * 100
      }
    },
    plugins: {
      legend: {
        display: false
      },
      tooltip: {
        backgroundColor: 'rgba(0, 0, 0, 0.9)',
        titleColor: 'white',
        bodyColor: 'white',
        cornerRadius: 12,
        displayColors: false,
        padding: 12,
        titleFont: {
          size: 14,
          weight: 'bold'
        },
        bodyFont: {
          size: 13
        },
        callbacks: {
          title: function (context) {
            return context[0].label
          },
          label: function (context) {
            const value = parseFloat(context.parsed.y)
            return `Spending: ${formatCurrency(value)}`
          },
          afterLabel: function (context) {
            if (trends.value && trends.value.length > 1) {
              const currentIndex = context.dataIndex
              if (currentIndex > 0) {
                const current = parseFloat(context.parsed.y)
                const previous = parseFloat(trends.value[currentIndex - 1]?.total || 0)
                const change = current - previous
                const changePercent = previous !== 0 ? ((change / previous) * 100).toFixed(1) : 0
                const changeText = change >= 0 ? '+' : ''
                return `Change: ${changeText}${formatCurrency(change)} (${changePercent}%)`
              }
            }
            return null
          }
        }
      }
    },
    scales: {
      x: {
        grid: {
          display: false,
          drawBorder: false
        },
        ticks: {
          color: '#6B7280',
          font: {
            size: 12,
            weight: '500'
          },
          padding: 10
        }
      },
      y: {
        beginAtZero: true,
        grid: {
          color: 'rgba(0, 0, 0, 0.05)',
          drawBorder: false
        },
        ticks: {
          color: '#6B7280',
          font: {
            size: 12
          },
          padding: 15,
          callback: function (value) {
            return formatCurrency(value)
          }
        }
      }
    },
    elements: {
      point: {
        hoverRadius: 8,
        hoverBorderWidth: 4
      },
      line: {
        borderCapStyle: 'round',
        borderJoinStyle: 'round'
      }
    }
  }))

  onMounted(() => {
    console.log('ExpenseChart mounted, trends:', trends.value) // Debug log
    // Trends will be fetched by the parent Dashboard component
  })
</script>

<style scoped>
  /* Modern Chart Card */
  .modern-chart-card {
    background: white;
    border-radius: 24px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
    overflow: hidden;
    position: relative;
    border: 1px solid rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  }

  .modern-chart-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 16px 40px rgba(0, 0, 0, 0.12);
  }

  /* Chart Header */
  .chart-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 2rem 2rem 1rem;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.05), rgba(118, 75, 162, 0.05));
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
  }

  .header-content {
    display: flex;
    align-items: center;
    gap: 1rem;
  }

  .header-icon {
    width: 48px;
    height: 48px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.25rem;
    box-shadow: 0 8px 16px rgba(102, 126, 234, 0.3);
  }

  .header-text {
    flex: 1;
  }

  .chart-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1f2937;
    margin: 0 0 0.25rem 0;
  }

  .chart-subtitle {
    font-size: 0.875rem;
    color: #6b7280;
    margin: 0;
  }

  /* Time Selector */
  .chart-controls {
    display: flex;
    align-items: center;
  }

  .time-selector {
    display: flex;
    background: rgba(107, 114, 128, 0.1);
    border-radius: 8px;
    padding: 2px;
    gap: 1px;
  }

  .time-btn {
    padding: 6px 12px;
    border: none;
    background: transparent;
    border-radius: 6px;
    color: #6b7280;
    font-size: 0.75rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
  }

  .time-btn:hover {
    background: rgba(102, 126, 234, 0.1);
    color: #667eea;
  }

  .time-btn.active {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
  }

  /* Chart Stats */
  .chart-stats {
    display: flex;
    padding: 1.5rem 2rem;
    gap: 2rem;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.02), rgba(118, 75, 162, 0.02));
  }

  .stat-item {
    flex: 1;
    text-align: center;
  }

  .stat-value {
    font-size: 1.75rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 0.25rem;
  }

  .stat-label {
    font-size: 0.875rem;
    color: #6b7280;
    font-weight: 500;
    margin-bottom: 0.5rem;
  }

  .stat-trend {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
  }

  .stat-trend.trend-up {
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
  }

  .stat-trend.trend-down {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
  }

  .stat-trend.trend-neutral {
    background: rgba(107, 114, 128, 0.1);
    color: #6b7280;
  }

  /* Chart Container */
  .chart-container {
    height: 320px;
    padding: 0 2rem 2rem;
    position: relative;
  }

  /* Loading State */
  .loading-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100%;
    padding: 2rem;
  }

  .loading-animation {
    text-align: center;
  }

  .loading-bars {
    display: flex;
    gap: 4px;
    margin-bottom: 1rem;
    justify-content: center;
  }

  .loading-bar {
    width: 4px;
    height: 40px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    border-radius: 2px;
    animation: loading-bounce 1.4s ease-in-out infinite both;
  }

  @keyframes loading-bounce {
    0%,
    80%,
    100% {
      transform: scaleY(0.6);
      opacity: 0.8;
    }
    40% {
      transform: scaleY(1);
      opacity: 1;
    }
  }

  .loading-text {
    color: #6b7280;
    font-size: 0.875rem;
    font-weight: 500;
  }

  /* Empty State */
  .empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100%;
    padding: 2rem;
    text-align: center;
  }

  .empty-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: #9ca3af;
    margin-bottom: 1.5rem;
  }

  .empty-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
  }

  .empty-message {
    color: #6b7280;
    margin-bottom: 1.5rem;
    line-height: 1.5;
  }

  .empty-action-btn {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
  }

  .empty-action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
  }

  /* Responsive Design */
  @media (max-width: 768px) {
    .chart-header {
      flex-direction: column;
      gap: 1rem;
      align-items: stretch;
    }

    .chart-stats {
      flex-direction: column;
      gap: 1rem;
    }

    .stat-item {
      text-align: left;
    }

    .stat-value {
      font-size: 1.5rem;
    }

    .chart-container {
      height: 280px;
      padding: 0 1rem 1.5rem;
    }

    .time-selector {
      width: 100%;
      justify-content: space-between;
    }

    .time-btn {
      flex: 1;
      text-align: center;
    }
  }
</style>
