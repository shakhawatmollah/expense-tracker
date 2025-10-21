<template>
  <div class="category-breakdown-card">
    <div class="card-header">
      <div class="header-content">
        <div class="header-icon">
          <i class="fas fa-chart-pie"></i>
        </div>
        <div class="header-text">
          <h3 class="card-title">Category Breakdown</h3>
          <p class="card-subtitle">{{ timeRangeText }} spending by category</p>
        </div>
      </div>
      <div class="period-selector">
        <button
          v-for="period in periods"
          :key="period.value"
          @click="selectedPeriod = period.value"
          :class="['period-btn', { active: selectedPeriod === period.value }]"
        >
          {{ period.label }}
        </button>
      </div>
    </div>

    <div class="chart-section">
      <div class="chart-container">
        <!-- Enhanced Loading State -->
        <div v-if="loading" class="loading-state">
          <div class="loading-donut">
            <div class="donut-segment" v-for="i in 8" :key="i" :style="{ animationDelay: i * 0.1 + 's' }"></div>
          </div>
          <p class="loading-text">Loading categories...</p>
        </div>

        <!-- Interactive Donut Chart -->
        <div v-else-if="chartData" class="interactive-chart">
          <div class="chart-wrapper">
            <Doughnut :data="chartData" :options="chartOptions" @click="handleChartClick" ref="chartRef" />

            <!-- Center Statistics -->
            <div class="chart-center">
              <div class="center-value">{{ formatCurrency(totalAmount) }}</div>
              <div class="center-label">Total Spent</div>
              <div class="center-count">{{ categoryData.length }} categories</div>
            </div>
          </div>

          <!-- Category Filter -->
          <div class="category-filter">
            <button @click="selectedCategory = null" :class="['filter-btn', { active: selectedCategory === null }]">
              All
            </button>
            <button
              v-for="(category, index) in categoryData.slice(0, 6)"
              :key="category.name"
              @click="selectedCategory = category.name"
              :class="['filter-btn', { active: selectedCategory === category.name }]"
              :style="{ '--category-color': chartColors[index] }"
            >
              {{ category.name }}
            </button>
          </div>
        </div>

        <!-- Enhanced Empty State -->
        <div v-else class="empty-state">
          <div class="empty-icon">
            <i class="fas fa-chart-pie"></i>
          </div>
          <h4 class="empty-title">No Categories Yet</h4>
          <p class="empty-message">Start adding expenses to see your spending breakdown</p>
          <button class="empty-action-btn" @click="$emit('add-expense')">
            <i class="fas fa-plus"></i>
            Add Expense
          </button>
        </div>
      </div>

      <!-- Enhanced Legend with Animations -->
      <div v-if="chartData && displayedCategories.length > 0" class="legend-section">
        <div class="legend-header">
          <h4 class="legend-title">Categories</h4>
          <div class="view-toggle">
            <button @click="viewMode = 'percentage'" :class="['toggle-btn', { active: viewMode === 'percentage' }]">
              %
            </button>
            <button @click="viewMode = 'amount'" :class="['toggle-btn', { active: viewMode === 'amount' }]">$</button>
          </div>
        </div>

        <div class="legend-items">
          <div
            v-for="(category, index) in displayedCategories"
            :key="category.name"
            :class="[
              'legend-item',
              {
                highlighted: selectedCategory === category.name,
                dimmed: selectedCategory && selectedCategory !== category.name
              }
            ]"
            @click="toggleCategory(category.name)"
            @mouseenter="highlightCategory(category.name)"
            @mouseleave="highlightCategory(null)"
          >
            <div class="legend-color" :style="{ backgroundColor: chartColors[index] }">
              <div class="color-pulse"></div>
            </div>

            <div class="legend-content">
              <div class="legend-name">{{ category.name }}</div>
              <div class="legend-details">
                <span class="legend-amount">{{ formatCurrency(parseFloat(category.total)) }}</span>
                <span class="legend-percentage">{{ category.percentage }}%</span>
              </div>
            </div>

            <div class="legend-bar">
              <div
                class="bar-fill"
                :style="{
                  width: category.percentage + '%',
                  backgroundColor: chartColors[index]
                }"
              ></div>
            </div>

            <div class="legend-value">
              {{ viewMode === 'percentage' ? category.percentage + '%' : formatCurrency(parseFloat(category.total)) }}
            </div>
          </div>
        </div>

        <div v-if="categoryData.length > displayedCategories.length" class="show-more">
          <button @click="showAllCategories = !showAllCategories" class="show-more-btn">
            <span>
              {{ showAllCategories ? 'Show Less' : `Show ${categoryData.length - displayedCategories.length} More` }}
            </span>
            <i :class="['fas', showAllCategories ? 'fa-chevron-up' : 'fa-chevron-down']"></i>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
  import { computed, onMounted, ref, watch } from 'vue'
  import { Chart as ChartJS, ArcElement, Tooltip, Legend } from 'chart.js'
  import { Doughnut } from 'vue-chartjs'
  import { useExpensesStore } from '@/stores/expenses'
  import { useCategoriesStore } from '@/stores/categories'
  import { format, startOfMonth, endOfMonth, subMonths, subDays } from 'date-fns'

  ChartJS.register(ArcElement, Tooltip, Legend)

  // Emits
  const emit = defineEmits(['add-expense', 'category-selected'])

  const expensesStore = useExpensesStore()
  const categoriesStore = useCategoriesStore()

  const loading = ref(false)
  const selectedPeriod = ref('month')
  const selectedCategory = ref(null)
  const highlightedCategory = ref(null)
  const viewMode = ref('amount')
  const showAllCategories = ref(false)
  const chartRef = ref(null)

  const periods = [
    { label: 'Week', value: 'week' },
    { label: 'Month', value: 'month' },
    { label: '3M', value: '3months' },
    { label: 'Year', value: 'year' }
  ]

  const chartColors = [
    '#667eea',
    '#764ba2',
    '#f093fb',
    '#f5576c',
    '#4facfe',
    '#00f2fe',
    '#43e97b',
    '#38f9d7',
    '#ffecd2',
    '#fcb69f',
    '#a8edea',
    '#fed6e3',
    '#d299c2',
    '#fef9d3',
    '#667eea',
    '#764ba2'
  ]

  // Helper functions
  const formatCurrency = amount => {
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: 'USD',
      minimumFractionDigits: 0,
      maximumFractionDigits: 0
    }).format(amount)
  }

  const getDateRange = () => {
    const now = new Date()
    switch (selectedPeriod.value) {
      case 'week':
        return {
          start: format(subDays(now, 7), 'yyyy-MM-dd'),
          end: format(now, 'yyyy-MM-dd')
        }
      case 'month':
        return {
          start: format(startOfMonth(now), 'yyyy-MM-dd'),
          end: format(endOfMonth(now), 'yyyy-MM-dd')
        }
      case '3months':
        return {
          start: format(subMonths(now, 3), 'yyyy-MM-dd'),
          end: format(now, 'yyyy-MM-dd')
        }
      case 'year':
        return {
          start: format(subMonths(now, 12), 'yyyy-MM-dd'),
          end: format(now, 'yyyy-MM-dd')
        }
      default:
        return {
          start: format(startOfMonth(now), 'yyyy-MM-dd'),
          end: format(endOfMonth(now), 'yyyy-MM-dd')
        }
    }
  }

  const timeRangeText = computed(() => {
    const period = periods.find(p => p.value === selectedPeriod.value)
    return period ? `${period.label}ly` : 'Current month'
  })

  const categoryData = computed(() => {
    const { start, end } = getDateRange()

    const filteredExpenses = expensesStore.expenses.filter(expense => {
      return expense.date >= start && expense.date <= end
    })

    const categoryTotals = {}

    filteredExpenses.forEach(expense => {
      const categoryName = expense.category?.name || 'Uncategorized'
      if (!categoryTotals[categoryName]) {
        categoryTotals[categoryName] = 0
      }
      categoryTotals[categoryName] += parseFloat(expense.amount)
    })

    const total = Object.values(categoryTotals).reduce((sum, amount) => sum + amount, 0)

    return Object.entries(categoryTotals)
      .map(([name, amount]) => ({
        name,
        total: amount.toFixed(2),
        percentage: total > 0 ? ((amount / total) * 100).toFixed(1) : 0
      }))
      .sort((a, b) => parseFloat(b.total) - parseFloat(a.total))
  })

  const totalAmount = computed(() => {
    return categoryData.value.reduce((total, category) => total + parseFloat(category.total), 0)
  })

  const displayedCategories = computed(() => {
    if (selectedCategory.value) {
      return categoryData.value.filter(cat => cat.name === selectedCategory.value)
    }
    return showAllCategories.value ? categoryData.value : categoryData.value.slice(0, 6)
  })

  const chartData = computed(() => {
    if (categoryData.value.length === 0) return null

    const dataToShow = selectedCategory.value
      ? categoryData.value.filter(item => item.name === selectedCategory.value)
      : categoryData.value

    return {
      labels: dataToShow.map(item => item.name),
      datasets: [
        {
          data: dataToShow.map(item => parseFloat(item.total)),
          backgroundColor: dataToShow.map((_, index) => chartColors[index % chartColors.length]),
          borderWidth: 3,
          borderColor: '#ffffff',
          hoverOffset: 8,
          hoverBorderWidth: 4,
          hoverBorderColor: '#ffffff'
        }
      ]
    }
  })

  const chartOptions = computed(() => ({
    responsive: true,
    maintainAspectRatio: false,
    interaction: {
      intersect: false
    },
    animation: {
      animateRotate: true,
      animateScale: true,
      duration: 1500,
      easing: 'easeInOutCubic'
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
        displayColors: true,
        padding: 12,
        titleFont: {
          size: 14,
          weight: 'bold'
        },
        bodyFont: {
          size: 13
        },
        callbacks: {
          label: function (context) {
            const label = context.label || ''
            const value = context.parsed
            const category = categoryData.value.find(cat => cat.name === label)
            return [`${label}: ${formatCurrency(value)}`, `Percentage: ${category?.percentage}%`]
          }
        }
      }
    },
    cutout: '65%',
    radius: '90%',
    elements: {
      arc: {
        borderJoinStyle: 'round'
      }
    }
  }))

  // Event handlers
  const handleChartClick = (event, elements) => {
    if (elements.length > 0) {
      const index = elements[0].index
      const categoryName = chartData.value.labels[index]
      toggleCategory(categoryName)
    }
  }

  const toggleCategory = categoryName => {
    if (selectedCategory.value === categoryName) {
      selectedCategory.value = null
    } else {
      selectedCategory.value = categoryName
      emit('category-selected', categoryName)
    }
  }

  const highlightCategory = categoryName => {
    highlightedCategory.value = categoryName
  }

  // Watch for period changes
  watch(selectedPeriod, () => {
    selectedCategory.value = null
  })

  onMounted(async () => {
    loading.value = true
    try {
      await Promise.all([expensesStore.fetchExpenses(), categoriesStore.fetchCategories()])
    } catch (error) {
      console.error('Failed to load category breakdown data:', error)
    } finally {
      loading.value = false
    }
  })
</script>

<style scoped>
  /* Category Breakdown Card */
  .category-breakdown-card {
    background: white;
    border-radius: 24px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
    overflow: hidden;
    border: 1px solid rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  }

  .category-breakdown-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 16px 40px rgba(0, 0, 0, 0.12);
  }

  /* Card Header */
  .card-header {
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

  .card-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1f2937;
    margin: 0 0 0.25rem 0;
  }

  .card-subtitle {
    font-size: 0.875rem;
    color: #6b7280;
    margin: 0;
  }

  /* Period Selector */
  .period-selector {
    display: flex;
    background: rgba(107, 114, 128, 0.1);
    border-radius: 8px;
    padding: 2px;
    gap: 1px;
  }

  .period-btn {
    padding: 6px 12px;
    border: none;
    background: transparent;
    border-radius: 6px;
    color: #6b7280;
    font-size: 0.75rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
  }

  .period-btn:hover {
    background: rgba(102, 126, 234, 0.1);
    color: #667eea;
  }

  .period-btn.active {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
  }

  /* Chart Section */
  .chart-section {
    display: flex;
    gap: 2rem;
    padding: 2rem;
  }

  .chart-container {
    flex: 1;
    max-width: 400px;
  }

  /* Interactive Chart */
  .interactive-chart {
    height: 320px;
    position: relative;
  }

  .chart-wrapper {
    position: relative;
    height: 280px;
    margin-bottom: 1rem;
  }

  .chart-center {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
    pointer-events: none;
  }

  .center-value {
    font-size: 1.75rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 0.25rem;
  }

  .center-label {
    font-size: 0.875rem;
    color: #6b7280;
    font-weight: 500;
    margin-bottom: 0.125rem;
  }

  .center-count {
    font-size: 0.75rem;
    color: #9ca3af;
  }

  /* Category Filter */
  .category-filter {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    justify-content: center;
  }

  .filter-btn {
    padding: 4px 8px;
    border: 1px solid #e5e7eb;
    background: white;
    border-radius: 16px;
    color: #6b7280;
    font-size: 0.625rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
  }

  .filter-btn:hover {
    border-color: #d1d5db;
    transform: translateY(-1px);
  }

  .filter-btn.active {
    border-color: var(--category-color, #667eea);
    background: var(--category-color, #667eea);
    color: white;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
  }

  /* Loading State */
  .loading-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 320px;
  }

  .loading-donut {
    position: relative;
    width: 80px;
    height: 80px;
    margin-bottom: 1rem;
  }

  .donut-segment {
    position: absolute;
    width: 10px;
    height: 30px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    border-radius: 5px;
    animation: donut-pulse 1.5s ease-in-out infinite;
    transform-origin: 50% 40px;
  }

  .donut-segment:nth-child(1) {
    transform: rotate(0deg);
  }
  .donut-segment:nth-child(2) {
    transform: rotate(45deg);
  }
  .donut-segment:nth-child(3) {
    transform: rotate(90deg);
  }
  .donut-segment:nth-child(4) {
    transform: rotate(135deg);
  }
  .donut-segment:nth-child(5) {
    transform: rotate(180deg);
  }
  .donut-segment:nth-child(6) {
    transform: rotate(225deg);
  }
  .donut-segment:nth-child(7) {
    transform: rotate(270deg);
  }
  .donut-segment:nth-child(8) {
    transform: rotate(315deg);
  }

  @keyframes donut-pulse {
    0%,
    100% {
      opacity: 0.3;
      transform: scale(0.8);
    }
    50% {
      opacity: 1;
      transform: scale(1);
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
    height: 320px;
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

  /* Legend Section */
  .legend-section {
    flex: 1;
    min-width: 300px;
  }

  .legend-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
  }

  .legend-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: #374151;
    margin: 0;
  }

  .view-toggle {
    display: flex;
    background: rgba(107, 114, 128, 0.1);
    border-radius: 8px;
    padding: 2px;
  }

  .toggle-btn {
    padding: 4px 8px;
    border: none;
    background: transparent;
    border-radius: 4px;
    color: #6b7280;
    font-size: 0.75rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
  }

  .toggle-btn.active {
    background: white;
    color: #374151;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  }

  /* Legend Items */
  .legend-items {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    max-height: 240px;
    overflow-y: auto;
    padding-right: 0.5rem;
  }

  .legend-items::-webkit-scrollbar {
    width: 4px;
  }

  .legend-items::-webkit-scrollbar-track {
    background: #f3f4f6;
    border-radius: 2px;
  }

  .legend-items::-webkit-scrollbar-thumb {
    background: #d1d5db;
    border-radius: 2px;
  }

  .legend-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.75rem;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
    border: 1px solid transparent;
    position: relative;
  }

  .legend-item:hover {
    background: rgba(102, 126, 234, 0.05);
    border-color: rgba(102, 126, 234, 0.2);
    transform: translateX(4px);
  }

  .legend-item.highlighted {
    background: rgba(102, 126, 234, 0.1);
    border-color: rgba(102, 126, 234, 0.3);
    transform: translateX(6px);
  }

  .legend-item.dimmed {
    opacity: 0.5;
  }

  .legend-color {
    position: relative;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    flex-shrink: 0;
    overflow: hidden;
  }

  .color-pulse {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    border-radius: 50%;
    background: inherit;
    animation: color-pulse 2s infinite;
  }

  @keyframes color-pulse {
    0%,
    100% {
      transform: scale(1);
      opacity: 1;
    }
    50% {
      transform: scale(1.2);
      opacity: 0.7;
    }
  }

  .legend-content {
    flex: 1;
    min-width: 0;
  }

  .legend-name {
    font-size: 0.875rem;
    font-weight: 500;
    color: #374151;
    margin-bottom: 0.25rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .legend-details {
    display: flex;
    gap: 0.5rem;
    font-size: 0.75rem;
    color: #6b7280;
  }

  .legend-bar {
    width: 60px;
    height: 4px;
    background: rgba(0, 0, 0, 0.1);
    border-radius: 2px;
    overflow: hidden;
    flex-shrink: 0;
  }

  .bar-fill {
    height: 100%;
    border-radius: 2px;
    transition: width 1s ease-out;
  }

  .legend-value {
    font-size: 0.875rem;
    font-weight: 600;
    color: #374151;
    min-width: 60px;
    text-align: right;
    flex-shrink: 0;
  }

  /* Show More Button */
  .show-more {
    margin-top: 1rem;
    text-align: center;
  }

  .show-more-btn {
    background: transparent;
    border: 1px solid #e5e7eb;
    color: #6b7280;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 0.75rem;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
  }

  .show-more-btn:hover {
    border-color: #667eea;
    color: #667eea;
    background: rgba(102, 126, 234, 0.05);
  }

  /* Responsive Design */
  @media (max-width: 1024px) {
    .chart-section {
      flex-direction: column;
      gap: 1.5rem;
    }

    .chart-container {
      max-width: none;
    }

    .legend-section {
      min-width: auto;
    }
  }

  @media (max-width: 768px) {
    .card-header {
      flex-direction: column;
      gap: 1rem;
      align-items: stretch;
    }

    .chart-section {
      padding: 1.5rem;
    }

    .interactive-chart {
      height: 280px;
    }

    .chart-wrapper {
      height: 240px;
    }

    .center-value {
      font-size: 1.5rem;
    }

    .period-selector {
      width: 100%;
      justify-content: space-between;
    }

    .period-btn {
      flex: 1;
      text-align: center;
    }
  }
</style>
