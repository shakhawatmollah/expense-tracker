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
          
          <!-- Budget Section -->
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
            <BudgetSummaryWidget />
            <BudgetAlertsWidget />
          </div>
          
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
            <BudgetProgressWidget />
            <ExpenseChart />
          </div>
          
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
            <CategoryBreakdown />
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
import BudgetSummaryWidget from '@/components/budgets/BudgetSummaryWidget.vue'
import BudgetAlertsWidget from '@/components/budgets/BudgetAlertsWidget.vue'
import BudgetProgressWidget from '@/components/budgets/BudgetProgressWidget.vue'
import { useExpensesStore } from '@/stores/expenses'
import { useCategoriesStore } from '@/stores/categories'
import { useDashboardStore } from '@/stores/dashboard'
import { useBudgetStore } from '@/stores/budget'

const expensesStore = useExpensesStore()
const categoriesStore = useCategoriesStore()
const dashboardStore = useDashboardStore()
const budgetStore = useBudgetStore()

onMounted(async () => {
  try {
    await Promise.all([
      expensesStore.fetchAllExpenses(), // Use non-paginated for dashboard
      categoriesStore.fetchCategories(),
      dashboardStore.fetchDashboardOverview(),
      dashboardStore.fetchTrends(6),
      budgetStore.fetchCurrentBudgets(),
      budgetStore.fetchBudgetSummary(),
      budgetStore.fetchBudgetAlerts()
    ])
  } catch (error) {
    console.error('Failed to load dashboard data:', error)
  }
})
</script>