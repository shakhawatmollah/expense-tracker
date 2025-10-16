<template>
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
    <div class="bg-white rounded-lg shadow p-6">
      <div class="flex items-center">
        <div class="flex-shrink-0">
          <CreditCardIcon class="h-8 w-8 text-indigo-600" />
        </div>
        <div class="ml-5 w-0 flex-1">
          <dl>
            <dt class="text-sm font-medium text-gray-500 truncate">Total Expenses</dt>
            <dd class="text-lg font-medium text-gray-900">
              <span v-if="loading">...</span>
              <span v-else>${{ totalExpenses }}</span>
            </dd>
          </dl>
        </div>
      </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
      <div class="flex items-center">
        <div class="flex-shrink-0">
          <CalendarIcon class="h-8 w-8 text-green-600" />
        </div>
        <div class="ml-5 w-0 flex-1">
          <dl>
            <dt class="text-sm font-medium text-gray-500 truncate">This Month</dt>
            <dd class="text-lg font-medium text-gray-900">
              <span v-if="loading">...</span>
              <span v-else>${{ thisMonthExpenses }}</span>
            </dd>
          </dl>
        </div>
      </div>
      <div v-if="!loading && monthlyChange !== 0" class="mt-2">
        <div class="flex items-center text-sm">
          <span 
            :class="monthlyChange > 0 ? 'text-red-600' : 'text-green-600'"
            class="flex items-center"
          >
            <span v-if="monthlyChange > 0">↑</span>
            <span v-else>↓</span>
            {{ Math.abs(monthlyChange).toFixed(1) }}%
          </span>
          <span class="text-gray-500 ml-1">from last month</span>
        </div>
      </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
      <div class="flex items-center">
        <div class="flex-shrink-0">
          <TagIcon class="h-8 w-8 text-yellow-600" />
        </div>
        <div class="ml-5 w-0 flex-1">
          <dl>
            <dt class="text-sm font-medium text-gray-500 truncate">Categories</dt>
            <dd class="text-lg font-medium text-gray-900">
              <span v-if="loading">...</span>
              <span v-else>{{ totalCategories }}</span>
            </dd>
          </dl>
        </div>
      </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
      <div class="flex items-center">
        <div class="flex-shrink-0">
          <ChartBarIcon class="h-8 w-8 text-purple-600" />
        </div>
        <div class="ml-5 w-0 flex-1">
          <dl>
            <dt class="text-sm font-medium text-gray-500 truncate">Avg/Month</dt>
            <dd class="text-lg font-medium text-gray-900">
              <span v-if="loading">...</span>
              <span v-else>${{ averageMonthly }}</span>
            </dd>
          </dl>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { 
  CreditCardIcon, 
  CalendarIcon, 
  TagIcon, 
  ChartBarIcon 
} from '@heroicons/vue/24/outline'
import { useExpensesStore } from '@/stores/expenses'
import { useCategoriesStore } from '@/stores/categories'
import { format, startOfMonth, endOfMonth, subMonths } from 'date-fns'

const expensesStore = useExpensesStore()
const categoriesStore = useCategoriesStore()

const loading = ref(false)

const totalExpenses = computed(() => {
  return expensesStore.expenses
    .reduce((total, expense) => total + parseFloat(expense.amount), 0)
    .toFixed(2)
})

const thisMonthExpenses = computed(() => {
  const currentMonthStart = format(startOfMonth(new Date()), 'yyyy-MM-dd')
  const currentMonthEnd = format(endOfMonth(new Date()), 'yyyy-MM-dd')
  
  return expensesStore.expenses
    .filter(expense => expense.date >= currentMonthStart && expense.date <= currentMonthEnd)
    .reduce((total, expense) => total + parseFloat(expense.amount), 0)
    .toFixed(2)
})

const lastMonthExpenses = computed(() => {
  const lastMonth = subMonths(new Date(), 1)
  const lastMonthStart = format(startOfMonth(lastMonth), 'yyyy-MM-dd')
  const lastMonthEnd = format(endOfMonth(lastMonth), 'yyyy-MM-dd')
  
  return expensesStore.expenses
    .filter(expense => expense.date >= lastMonthStart && expense.date <= lastMonthEnd)
    .reduce((total, expense) => total + parseFloat(expense.amount), 0)
})

const monthlyChange = computed(() => {
  const current = parseFloat(thisMonthExpenses.value)
  const last = lastMonthExpenses.value
  
  if (last === 0) return 0
  return ((current - last) / last) * 100
})

const totalCategories = computed(() => {
  return categoriesStore.categories.length
})

const averageMonthly = computed(() => {
  if (expensesStore.expenses.length === 0) return '0.00'
  
  // Get unique months from expenses
  const months = new Set(
    expensesStore.expenses.map(expense => expense.date.substring(0, 7))
  )
  
  if (months.size === 0) return '0.00'
  
  const total = parseFloat(totalExpenses.value)
  return (total / months.size).toFixed(2)
})

onMounted(async () => {
  loading.value = true
  try {
    await Promise.all([
      expensesStore.fetchExpenses(),
      categoriesStore.fetchCategories()
    ])
  } catch (error) {
    console.error('Failed to load summary data:', error)
  } finally {
    loading.value = false
  }
})
</script>