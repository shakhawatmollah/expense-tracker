<template>
  <div class="bg-white rounded-lg shadow p-6">
    <div class="mb-4">
      <h3 class="text-lg font-medium text-gray-900">Expense Trends</h3>
      <p class="text-sm text-gray-500">Last 6 months spending overview</p>
    </div>
    <div class="h-64">
      <Line
        v-if="chartData"
        :data="chartData"
        :options="chartOptions"
        class="max-h-64"
      />
      <div v-else-if="loading" class="h-full flex items-center justify-center">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
      </div>
      <div v-else class="h-full flex items-center justify-center text-gray-500">
        No data available
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, watch } from 'vue'
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
import { format } from 'date-fns'

ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  Filler
)

const dashboardStore = useDashboardStore()

const loading = computed(() => dashboardStore.loading)
const trends = computed(() => dashboardStore.trends)

// Watch for trends data changes
watch(trends, (newTrends) => {
  console.log('Trends data updated:', newTrends)
}, { immediate: true })

const chartData = computed(() => {
  if (!trends.value || trends.value.length === 0) {
    console.log('No trends data available')
    return null
  }

  console.log('Trends data:', trends.value) // Debug log

  return {
    labels: trends.value.map(item => {
      // Use label if available, otherwise format the date
      if (item.label) return item.label
      const date = new Date(item.date || item.month)
      return format(date, 'MMM yyyy')
    }),
    datasets: [
      {
        label: 'Monthly Expenses',
        data: trends.value.map(item => parseFloat(item.total) || 0),
        borderColor: 'rgb(79, 70, 229)',
        backgroundColor: 'rgba(79, 70, 229, 0.1)',
        fill: true,
        tension: 0.3,
        pointBackgroundColor: 'rgb(79, 70, 229)',
        pointBorderColor: 'white',
        pointBorderWidth: 2,
        pointRadius: 4
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
      displayColors: false,
      callbacks: {
        label: function(context) {
          return `$${parseFloat(context.parsed.y).toFixed(2)}`
        }
      }
    }
  },
  scales: {
    x: {
      grid: {
        display: false
      },
      ticks: {
        color: '#6B7280'
      }
    },
    y: {
      beginAtZero: true,
      grid: {
        color: '#F3F4F6'
      },
      ticks: {
        color: '#6B7280',
        callback: function(value) {
          return '$' + value
        }
      }
    }
  },
  elements: {
    point: {
      hoverRadius: 6
    }
  }
}))

onMounted(() => {
  console.log('ExpenseChart mounted, trends:', trends.value) // Debug log
  // Trends will be fetched by the parent Dashboard component
})
</script>