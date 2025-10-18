<template>
  <div class="budget-list">
    <!-- Header -->
    <div class="budget-header">
      <div class="header-left">
        <h2 class="page-title">Budget Management</h2>
        <p class="page-subtitle">Track and manage your spending budgets</p>
      </div>
      <div class="header-right">
        <button 
          @click="showCreateModal = true" 
          class="btn btn-primary"
        >
          <i class="fas fa-plus"></i>
          Create Budget
        </button>
      </div>
    </div>

    <!-- Summary Cards -->
    <div class="budget-summary-cards">
      <div class="summary-card">
        <div class="card-icon">
          <i class="fas fa-wallet text-blue-500"></i>
        </div>
        <div class="card-content">
          <h3>${{ formatAmount(budgetStore.totalBudgetAmount) }}</h3>
          <p>Total Budget</p>
        </div>
      </div>
      
      <div class="summary-card">
        <div class="card-icon">
          <i class="fas fa-credit-card text-orange-500"></i>
        </div>
        <div class="card-content">
          <h3>${{ formatAmount(budgetStore.totalSpentAmount) }}</h3>
          <p>Total Spent</p>
        </div>
      </div>
      
      <div class="summary-card">
        <div class="card-icon">
          <i class="fas fa-piggy-bank text-green-500"></i>
        </div>
        <div class="card-content">
          <h3>${{ formatAmount(budgetStore.totalRemainingAmount) }}</h3>
          <p>Remaining</p>
        </div>
      </div>
      
      <div class="summary-card">
        <div class="card-icon">
          <i class="fas fa-chart-line text-purple-500"></i>
        </div>
        <div class="card-content">
          <h3>{{ budgetStore.overallBudgetProgress }}%</h3>
          <p>Overall Progress</p>
        </div>
      </div>
    </div>

    <!-- Alerts -->
    <div v-if="budgetStore.budgetAlerts.length > 0" class="budget-alerts mb-6">
      <div class="alert-header">
        <h3 class="text-lg font-semibold text-gray-800">Budget Alerts</h3>
      </div>
      <div class="alert-list">
        <div 
          v-for="alert in budgetStore.budgetAlerts" 
          :key="alert.id"
          :class="[
            'alert-item',
            alert.level === 'critical' ? 'alert-critical' : 'alert-warning'
          ]"
        >
          <div class="alert-icon">
            <i :class="alert.level === 'critical' ? 'fas fa-exclamation-triangle' : 'fas fa-exclamation-circle'"></i>
          </div>
          <div class="alert-content">
            <h4 class="font-medium">{{ alert.budget_name }}</h4>
            <p class="text-sm">{{ alert.message }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="budget-filters">
      <div class="filters-row">
        <div class="filter-group">
          <label>Period</label>
          <select v-model="filters.period" @change="applyFilters" class="form-select">
            <option value="">All Periods</option>
            <option value="weekly">Weekly</option>
            <option value="monthly">Monthly</option>
            <option value="quarterly">Quarterly</option>
            <option value="yearly">Yearly</option>
          </select>
        </div>
        
        <div class="filter-group">
          <label>Category</label>
          <select v-model="filters.category_id" @change="applyFilters" class="form-select">
            <option value="">All Categories</option>
            <option 
              v-for="category in categoryStore.categories" 
              :key="category.id" 
              :value="category.id"
            >
              {{ category.name }}
            </option>
          </select>
        </div>
        
        <div class="filter-group">
          <label>Status</label>
          <select v-model="filters.is_active" @change="applyFilters" class="form-select">
            <option value="">All Budgets</option>
            <option value="1">Active</option>
            <option value="0">Inactive</option>
          </select>
        </div>
        
        <div class="filter-group">
          <button @click="clearFilters" class="btn btn-secondary">
            <i class="fas fa-times"></i>
            Clear
          </button>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="budgetStore.isLoading" class="loading-state">
      <div class="loading-spinner"></div>
      <p>Loading budgets...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="budgetStore.error" class="error-state">
      <div class="error-icon">
        <i class="fas fa-exclamation-triangle"></i>
      </div>
      <p>{{ budgetStore.error }}</p>
      <button @click="loadBudgets" class="btn btn-primary">
        Try Again
      </button>
    </div>

    <!-- Budget List -->
    <div v-else class="budget-grid">
      <BudgetCard
        v-for="budget in budgetStore.budgets"
        :key="budget.id"
        :budget="budget"
        @edit="editBudget"
        @delete="deleteBudget"
        @duplicate="duplicateBudget"
      />
      
      <!-- Empty State -->
      <div v-if="budgetStore.budgets.length === 0" class="empty-state">
        <div class="empty-icon">
          <i class="fas fa-chart-pie"></i>
        </div>
        <h3>No budgets found</h3>
        <p>Create your first budget to start tracking your spending</p>
        <button @click="showCreateModal = true" class="btn btn-primary">
          Create Budget
        </button>
      </div>
    </div>

    <!-- Pagination -->
    <Pagination
      v-if="budgetStore.budgets.length > 0"
      :pagination="budgetStore.pagination"
      @page-changed="handlePageChange"
    />

    <!-- Create/Edit Modal -->
    <BudgetModal
      v-if="showCreateModal || showEditModal"
      :budget="editingBudget"
      :is-editing="showEditModal"
      @close="closeModal"
      @saved="handleBudgetSaved"
    />

    <!-- Delete Confirmation -->
    <ConfirmDialog
      v-if="showDeleteDialog"
      title="Delete Budget"
      message="Are you sure you want to delete this budget? This action cannot be undone."
      confirm-text="Delete"
      confirm-class="btn-danger"
      @confirm="confirmDelete"
      @cancel="showDeleteDialog = false"
    />
  </div>
</template>

<script>
import { ref, onMounted, reactive } from 'vue'
import { useBudgetStore } from '@/stores/budget'
import { useCategoriesStore } from '@/stores/categories'
import BudgetCard from './BudgetCard.vue'
import BudgetModal from './BudgetModal.vue'
import Pagination from '@/components/common/Pagination.vue'
import ConfirmDialog from '@/components/common/ConfirmDialog.vue'
import { formatCurrency } from '@/utils/formatters'

export default {
  name: 'BudgetList',
  components: {
    BudgetCard,
    BudgetModal,
    Pagination,
    ConfirmDialog
  },
  setup() {
    const budgetStore = useBudgetStore()
    const categoryStore = useCategoriesStore()
    
    // Modal states
    const showCreateModal = ref(false)
    const showEditModal = ref(false)
    const showDeleteDialog = ref(false)
    const editingBudget = ref(null)
    const deletingBudgetId = ref(null)
    
    // Filters
    const filters = reactive({
      period: '',
      category_id: '',
      is_active: '',
      page: 1
    })
    
    // Methods
    const formatAmount = (amount) => {
      return formatCurrency(amount)
    }
    
    const loadBudgets = async () => {
      try {
        await Promise.all([
          budgetStore.fetchBudgets(filters),
          budgetStore.fetchBudgetAlerts(),
          budgetStore.fetchBudgetSummary()
        ])
      } catch (error) {
        console.error('Failed to load budgets:', error)
      }
    }
    
    const applyFilters = () => {
      filters.page = 1
      loadBudgets()
    }
    
    const clearFilters = () => {
      Object.keys(filters).forEach(key => {
        if (key !== 'page') {
          filters[key] = ''
        }
      })
      filters.page = 1
      loadBudgets()
    }
    
    const handlePageChange = (page) => {
      filters.page = page
      loadBudgets()
    }
    
    const editBudget = (budget) => {
      editingBudget.value = budget
      showEditModal.value = true
    }
    
    const deleteBudget = (budget) => {
      deletingBudgetId.value = budget.id
      showDeleteDialog.value = true
    }
    
    const confirmDelete = async () => {
      try {
        await budgetStore.deleteBudget(deletingBudgetId.value)
        showDeleteDialog.value = false
        deletingBudgetId.value = null
        
        // Reload data
        await loadBudgets()
      } catch (error) {
        console.error('Failed to delete budget:', error)
      }
    }
    
    const duplicateBudget = async (budget) => {
      try {
        await budgetStore.duplicateBudget(budget.id)
        await loadBudgets()
      } catch (error) {
        console.error('Failed to duplicate budget:', error)
      }
    }
    
    const closeModal = () => {
      showCreateModal.value = false
      showEditModal.value = false
      editingBudget.value = null
    }
    
    const handleBudgetSaved = async () => {
      closeModal()
      await loadBudgets()
    }
    
    // Initialize
    onMounted(async () => {
      await Promise.all([
        categoryStore.fetchCategories(),
        loadBudgets()
      ])
    })
    
    return {
      budgetStore,
      categoryStore,
      showCreateModal,
      showEditModal,
      showDeleteDialog,
      editingBudget,
      filters,
      formatAmount,
      loadBudgets,
      applyFilters,
      clearFilters,
      handlePageChange,
      editBudget,
      deleteBudget,
      confirmDelete,
      duplicateBudget,
      closeModal,
      handleBudgetSaved
    }
  }
}
</script>

<style scoped>
.budget-list {
  @apply p-6;
}

.budget-header {
  @apply flex justify-between items-start mb-6;
}

.header-left {
  @apply flex-1;
}

.page-title {
  @apply text-2xl font-bold text-gray-800 mb-1;
}

.page-subtitle {
  @apply text-gray-600;
}

.header-right {
  @apply flex-shrink-0;
}

.budget-summary-cards {
  @apply grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6;
}

.summary-card {
  @apply bg-white rounded-lg shadow-sm border p-4 flex items-center;
}

.card-icon {
  @apply mr-3 text-2xl;
}

.card-content h3 {
  @apply text-xl font-bold text-gray-800;
}

.card-content p {
  @apply text-sm text-gray-600;
}

.budget-alerts {
  @apply bg-yellow-50 border border-yellow-200 rounded-lg p-4;
}

.alert-header {
  @apply mb-3;
}

.alert-list {
  @apply space-y-2;
}

.alert-item {
  @apply flex items-center p-3 rounded-md;
}

.alert-critical {
  @apply bg-red-100 border border-red-200;
}

.alert-warning {
  @apply bg-yellow-100 border border-yellow-200;
}

.alert-icon {
  @apply mr-3 text-lg;
}

.alert-critical .alert-icon {
  @apply text-red-500;
}

.alert-warning .alert-icon {
  @apply text-yellow-600;
}

.budget-filters {
  @apply bg-white rounded-lg shadow-sm border p-4 mb-6;
}

.filters-row {
  @apply flex flex-wrap gap-4 items-end;
}

.filter-group {
  @apply flex flex-col;
}

.filter-group label {
  @apply text-sm font-medium text-gray-700 mb-1;
}

.budget-grid {
  @apply grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6;
}

.loading-state {
  @apply flex flex-col items-center justify-center py-12;
}

.loading-spinner {
  @apply w-8 h-8 border-4 border-blue-200 border-t-blue-600 rounded-full animate-spin mb-4;
}

.error-state {
  @apply flex flex-col items-center justify-center py-12 text-center;
}

.error-icon {
  @apply text-4xl text-red-500 mb-4;
}

.empty-state {
  @apply col-span-full flex flex-col items-center justify-center py-12 text-center;
}

.empty-icon {
  @apply text-6xl text-gray-400 mb-4;
}

.empty-state h3 {
  @apply text-xl font-semibold text-gray-700 mb-2;
}

.empty-state p {
  @apply text-gray-600 mb-6;
}
</style>