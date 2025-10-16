<template>
  <div class="bg-white rounded-lg shadow p-6">
    <div class="mb-4">
      <h3 class="text-lg font-medium text-gray-900">Category Breakdown</h3>
      <p class="text-sm text-gray-500">Current month spending by category</p>
    </div>
    <div class="h-64">
      <Doughnut
        v-if="chartData"
        :data="chartData"
        :options="chartOptions"
        class="max-h-64"
      />
      <div v-else-if="loading" class="h-full flex items-center justify-center">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
      </div>
      <div v-else class="h-full flex items-center justify-center text-gray-500">
        No expenses this month
      </div>
    </div>
    
    <!-- Legend -->
    <div v-if="chartData" class="mt-4 space-y-2">
      <div
        v-for="(category, index) in categoryData"
        :key="category.name"
        class="flex items-center justify-between text-sm"
      >
        <div class="flex items-center">
          <div
            class="w-3 h-3 rounded-full mr-2"
            :style="{ backgroundColor: chartColors[index % chartColors.length] }"
          ></div>
          <span class="text-gray-700">{{ category.name }}</span>
        </div>
        <span class="font-medium text-gray-900">${{ category.total }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import {
  Chart as ChartJS,
  ArcElement,
  Tooltip,
  Legend
} from 'chart.js'
import { Doughnut } from 'vue-chartjs'
import { useExpensesStore } from '@/stores/expenses'
import { useCategoriesStore } from '@/stores/categories'
import { format, startOfMonth, endOfMonth } from 'date-fns'

ChartJS.register(ArcElement, Tooltip, Legend)

const expensesStore = useExpensesStore()
const categoriesStore = useCategoriesStore()

const loading = ref(false)

const chartColors = [
  '#EF4444', // Red
  '#F97316', // Orange
  '#F59E0B', // Amber
  '#EAB308', // Yellow
  '#22C55E', // Green
  '#10B981', // Emerald
  '#06B6D4', // Cyan
  '#3B82F6', // Blue
  '#6366F1', // Indigo
  '#8B5CF6', // Violet
  '#A855F7', // Purple
  '#EC4899', // Pink
]

const categoryData = computed(() => {
  const currentMonthStart = format(startOfMonth(new Date()), 'yyyy-MM-dd')
  const currentMonthEnd = format(endOfMonth(new Date()), 'yyyy-MM-dd')
  
  const currentMonthExpenses = expensesStore.expenses.filter(expense => {
    return expense.date >= currentMonthStart && expense.date <= currentMonthEnd
  })

  const categoryTotals = {}
  
  currentMonthExpenses.forEach(expense => {
    const categoryName = expense.category?.name || 'Uncategorized'
    if (!categoryTotals[categoryName]) {
      categoryTotals[categoryName] = 0
    }
    categoryTotals[categoryName] += parseFloat(expense.amount)
  })

  return Object.entries(categoryTotals)
    .map(([name, total]) => ({
      name,
      total: total.toFixed(2)
    }))
    .sort((a, b) => parseFloat(b.total) - parseFloat(a.total))
})

const chartData = computed(() => {
  if (categoryData.value.length === 0) return null

  return {
    labels: categoryData.value.map(item => item.name),
    datasets: [
      {
        data: categoryData.value.map(item => parseFloat(item.total)),
        backgroundColor: chartColors.slice(0, categoryData.value.length),
        borderWidth: 2,
        borderColor: '#ffffff',
        hoverOffset: 4
      }
    ]
  }
})

const chartOptions = computed(() => ({
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: false
    },
    tooltip: {
      backgroundColor: 'rgba(0, 0, 0, 0.8)',
      titleColor: 'white',
      bodyColor: 'white',
      cornerRadius: 8,
      displayColors: true,
      callbacks: {
        label: function(context) {
          const label = context.label || ''
          const value = context.parsed
          const total = context.dataset.data.reduce((a, b) => a + b, 0)
          const percentage = ((value / total) * 100).toFixed(1)
          return `${label}: $${value.toFixed(2)} (${percentage}%)`
        }
      }
    }
  },
  cutout: '60%'
}))

onMounted(async () => {
  loading.value = true
  try {
    await Promise.all([
      expensesStore.fetchExpenses(),
      categoriesStore.fetchCategories()
    ])
  } catch (error) {
    console.error('Failed to load category breakdown data:', error)
  } finally {
    loading.value = false
  }
})
</script>