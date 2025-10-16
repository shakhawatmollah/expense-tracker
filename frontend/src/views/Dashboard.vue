<template>
  <div class="min-h-screen bg-gray-50">
    <AppHeader />
    <div class="flex">
      <AppSidebar class="hidden md:block" />
      <main class="flex-1 p-6">
        <div class="max-w-7xl mx-auto">
          <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
            <p class="text-gray-600">Welcome back! Here's your expense overview.</p>
          </div>
          
          <SummaryCards />
          
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
            <ExpenseChart />
            <CategoryBreakdown />
          </div>
          
          <div class="mt-6">
            <RecentExpenses />
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import AppHeader from '@/components/layout/AppHeader.vue'
import AppSidebar from '@/components/layout/AppSidebar.vue'
import SummaryCards from '@/components/dashboard/SummaryCards.vue'
import ExpenseChart from '@/components/dashboard/ExpenseChart.vue'
import CategoryBreakdown from '@/components/dashboard/CategoryBreakdown.vue'
import RecentExpenses from '@/components/dashboard/RecentExpenses.vue'
import { useExpensesStore } from '@/stores/expenses'
import { useCategoriesStore } from '@/stores/categories'

const expensesStore = useExpensesStore()
const categoriesStore = useCategoriesStore()

onMounted(async () => {
  try {
    await Promise.all([
      expensesStore.fetchExpenses(),
      categoriesStore.fetchCategories()
    ])
  } catch (error) {
    console.error('Failed to load dashboard data:', error)
  }
})
</script>