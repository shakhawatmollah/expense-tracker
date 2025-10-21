<template>
  <div class="p-6 space-y-6 bg-gray-50 min-h-screen">
    <!-- Header Section -->
    <div class="flex justify-between items-start bg-white rounded-lg shadow-sm p-6">
      <div class="flex-1">
        <h1 class="text-3xl font-bold text-gray-900 mb-2 flex items-center gap-3">
          <i class="fas fa-chart-pie text-blue-600"></i>
          Budget Management
        </h1>
        <p class="text-gray-600 text-lg">Track, manage, and optimize your spending budgets</p>
      </div>

      <div class="flex gap-3">
        <button
          @click="refreshData"
          :disabled="loading"
          class="px-4 py-2 border border-blue-600 text-blue-600 rounded-lg hover:bg-blue-50 transition-colors flex items-center gap-2"
        >
          <i class="fas fa-sync-alt" :class="{ 'animate-spin': loading }"></i>
          Refresh
        </button>
        <button @click="showCreateModal = true" class="create-budget-btn">
          <i class="fas fa-plus"></i>
          Create Budget
        </button>
      </div>
    </div>

    <!-- Quick Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <div class="bg-white rounded-lg shadow-sm p-6 flex items-center border-l-4 border-blue-500">
        <div class="flex-1">
          <div class="text-2xl font-bold text-gray-900 mb-1">${{ formatAmount(budgetStore.totalBudgetAmount) }}</div>
          <div class="text-sm text-gray-600 mb-1">Total Budget</div>
          <div class="text-xs font-medium text-green-600 flex items-center gap-1">
            <i class="fas fa-arrow-up text-xs"></i>
            +12% from last month
          </div>
        </div>
        <div class="text-3xl text-blue-500 mr-4">
          <i class="fas fa-wallet"></i>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow-sm p-6 flex items-center border-l-4 border-orange-500">
        <div class="flex-1">
          <div class="text-2xl font-bold text-gray-900 mb-1">${{ formatAmount(spentThisMonth) }}</div>
          <div class="text-sm text-gray-600 mb-1">Spent This Month</div>
          <div class="text-xs font-medium text-red-600 flex items-center gap-1">
            <i class="fas fa-arrow-up text-xs"></i>
            +5% from last month
          </div>
        </div>
        <div class="text-3xl text-orange-500 mr-4">
          <i class="fas fa-credit-card"></i>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow-sm p-6 flex items-center border-l-4 border-green-500">
        <div class="flex-1">
          <div class="text-2xl font-bold text-gray-900 mb-1">${{ formatAmount(remainingBudget) }}</div>
          <div class="text-sm text-gray-600 mb-1">Remaining Budget</div>
          <div class="text-xs font-medium text-green-600 flex items-center gap-1">
            <i class="fas fa-arrow-down text-xs"></i>
            65% available
          </div>
        </div>
        <div class="text-3xl text-green-500 mr-4">
          <i class="fas fa-piggy-bank"></i>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow-sm p-6 flex items-center border-l-4 border-purple-500">
        <div class="flex-1">
          <div class="text-2xl font-bold text-gray-900 mb-1">{{ activeBudgets }}</div>
          <div class="text-sm text-gray-600 mb-1">Active Budgets</div>
          <div class="text-xs font-medium text-purple-600 flex items-center gap-1">
            <i class="fas fa-check text-xs"></i>
            All monitored
          </div>
        </div>
        <div class="text-3xl text-purple-500 mr-4">
          <i class="fas fa-chart-line"></i>
        </div>
      </div>
    </div>

    <!-- Budget Alerts -->
    <div v-if="budgetAlerts.length > 0" class="bg-white rounded-lg shadow-sm p-6">
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-semibold text-gray-900 flex items-center gap-2">
          <i class="fas fa-exclamation-triangle text-orange-500"></i>
          Budget Alerts
          <span class="bg-red-500 text-white px-2 py-1 rounded-full text-xs font-medium">
            {{ budgetAlerts.length }}
          </span>
        </h2>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div
          v-for="alert in budgetAlerts"
          :key="alert.id"
          class="bg-white rounded-lg shadow-sm p-4 flex justify-between items-center"
          :class="{
            'border-l-4 border-red-500': alert.type === 'exceeded',
            'border-l-4 border-orange-500': alert.type === 'warning'
          }"
        >
          <div class="flex items-center flex-1">
            <i
              class="text-xl mr-3"
              :class="{
                'fas fa-exclamation-circle text-red-500': alert.type === 'exceeded',
                'fas fa-exclamation-triangle text-orange-500': alert.type === 'warning'
              }"
            ></i>
            <div>
              <div class="font-medium text-gray-900 mb-1">{{ alert.budgetName }}</div>
              <div class="text-sm text-gray-600">{{ alert.message }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Create Budget Modal -->
    <BudgetForm
      v-if="showCreateModal"
      :show="showCreateModal"
      @close="showCreateModal = false"
      @saved="handleBudgetSaved"
    />
  </div>
</template>

<script setup>
  import { ref, computed, onMounted } from 'vue'
  import { useBudgetStore } from '@/stores/budget'
  import BudgetForm from './BudgetForm.vue'

  const budgetStore = useBudgetStore()
  const showCreateModal = ref(false)
  const loading = ref(false)

  // Computed properties
  const spentThisMonth = computed(() => {
    return budgetStore.budgetSummary?.total_spent || 0
  })

  const remainingBudget = computed(() => {
    const total = budgetStore.budgetSummary?.total_budget_amount || 0
    const spent = spentThisMonth.value
    return total - spent
  })

  const activeBudgets = computed(() => {
    return budgetStore.activeBudgets?.length || 0
  })

  const budgetAlerts = computed(() => {
    return budgetStore.budgetAlerts || []
  })

  // Methods
  const formatAmount = amount => {
    if (!amount) return '0.00'
    return parseFloat(amount).toFixed(2)
  }

  const refreshData = async () => {
    loading.value = true
    try {
      await Promise.all([
        budgetStore.fetchCurrentBudgets(),
        budgetStore.fetchBudgetSummary(),
        budgetStore.fetchBudgetAlerts()
      ])
    } catch (error) {
      console.error('Error refreshing data:', error)
    } finally {
      loading.value = false
    }
  }

  const handleBudgetSaved = () => {
    showCreateModal.value = false
    refreshData()
  }

  // Lifecycle
  onMounted(() => {
    refreshData()
  })
</script>

<style scoped>
  .create-budget-btn {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    border: none;
    color: white;
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 14px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.25);
    position: relative;
    overflow: hidden;
  }

  .create-budget-btn:hover {
    background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(59, 130, 246, 0.35);
  }

  .create-budget-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
    box-shadow: 0 2px 8px rgba(59, 130, 246, 0.15);
  }
</style>
