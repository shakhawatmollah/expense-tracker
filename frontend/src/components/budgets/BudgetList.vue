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
          class="colorful-budget-btn create-budget-btn"
        >
          <i class="fas fa-plus"></i>
          Create Budget
          <div class="btn-sparkles">
            <div class="sparkle sparkle-1"></div>
            <div class="sparkle sparkle-2"></div>
            <div class="sparkle sparkle-3"></div>
          </div>
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
        <button @click="showCreateModal = true" class="colorful-budget-btn create-budget-btn">
          <i class="fas fa-plus"></i>
          Create Budget
          <div class="btn-sparkles">
            <div class="sparkle sparkle-1"></div>
            <div class="sparkle sparkle-2"></div>
            <div class="sparkle sparkle-3"></div>
          </div>
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
  props: {
    autoOpenCreate: {
      type: Boolean,
      default: false
    }
  },
  components: {
    BudgetCard,
    BudgetModal,
    Pagination,
    ConfirmDialog
  },
  setup(props) {
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
      
      // Check if we should auto-open create modal
      if (props.autoOpenCreate) {
        showCreateModal.value = true
      }
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
  padding: 1.5rem;
}

.budget-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1.5rem;
}

.header-left {
  flex: 1;
}

.page-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 0.25rem;
}

.page-subtitle {
  color: #6b7280;
}

.header-right {
  flex-shrink: 0;
}

.budget-summary-cards {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

@media (min-width: 768px) {
  .budget-summary-cards {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (min-width: 1024px) {
  .budget-summary-cards {
    grid-template-columns: repeat(4, 1fr);
  }
}

.summary-card {
  background-color: white;
  border-radius: 0.5rem;
  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
  border: 1px solid #e5e7eb;
  padding: 1rem;
  display: flex;
  align-items: center;
}

.card-icon {
  margin-right: 0.75rem;
  font-size: 1.5rem;
}

.card-content h3 {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1f2937;
}

.card-content p {
  font-size: 0.875rem;
  color: #6b7280;
}

.budget-alerts {
  background-color: #fffbeb;
  border: 1px solid #fed7aa;
  border-radius: 0.5rem;
  padding: 1rem;
}

.alert-header {
  margin-bottom: 0.75rem;
}

.alert-list > * + * {
  margin-top: 0.5rem;
}

.alert-item {
  display: flex;
  align-items: center;
  padding: 0.75rem;
  border-radius: 0.375rem;
}

.alert-critical {
  background-color: #fef2f2;
  border: 1px solid #fecaca;
}

.alert-warning {
  background-color: #fffbeb;
  border: 1px solid #fed7aa;
}

.alert-icon {
  margin-right: 0.75rem;
  font-size: 1.125rem;
}

.alert-critical .alert-icon {
  color: #ef4444;
}

.alert-warning .alert-icon {
  color: #d97706;
}

.budget-filters {
  background-color: white;
  border-radius: 0.5rem;
  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
  border: 1px solid #e5e7eb;
  padding: 1rem;
  margin-bottom: 1.5rem;
}

.filters-row {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  align-items: flex-end;
}

.filter-group {
  display: flex;
  flex-direction: column;
}

.filter-group label {
  font-size: 0.875rem;
  font-weight: 500;
  color: #374151;
  margin-bottom: 0.25rem;
}

.budget-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1.5rem;
}

@media (min-width: 768px) {
  .budget-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (min-width: 1024px) {
  .budget-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}

.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 3rem 0;
}

.loading-spinner {
  width: 2rem;
  height: 2rem;
  border: 4px solid #dbeafe;
  border-top: 4px solid #2563eb;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 1rem;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.error-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 3rem 0;
  text-align: center;
}

.error-icon {
  font-size: 2.25rem;
  color: #ef4444;
  margin-bottom: 1rem;
}

.empty-state {
  grid-column: 1 / -1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 3rem 0;
  text-align: center;
}

.empty-icon {
  font-size: 3.75rem;
  color: #9ca3af;
  margin-bottom: 1rem;
}

.empty-state h3 {
  font-size: 1.25rem;
  font-weight: 600;
  color: #374151;
  margin-bottom: 0.5rem;
}

.empty-state p {
  color: #6b7280;
  margin-bottom: 1.5rem;
}

/* Colorful Budget Buttons */
.colorful-budget-btn {
  position: relative;
  padding: 12px 24px;
  border: none;
  border-radius: 16px;
  font-weight: 700;
  font-size: 0.875rem;
  color: white;
  cursor: pointer;
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  overflow: hidden;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
}

.colorful-budget-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
  transform: none !important;
}

.colorful-budget-btn:not(:disabled):hover {
  transform: translateY(-2px) scale(1.02);
  box-shadow: 0 12px 40px rgba(0, 0, 0, 0.4);
}

.colorful-budget-btn:not(:disabled):active {
  transform: translateY(0) scale(0.98);
}

/* Create Budget Button - Rainbow Gradient */
.create-budget-btn {
  background: linear-gradient(45deg, 
    #667eea 0%, 
    #764ba2 20%, 
    #f093fb 40%, 
    #f5576c 60%, 
    #4facfe 80%, 
    #00f2fe 100%);
  background-size: 300% 300%;
  animation: rainbow-flow 3s ease infinite;
}

.create-budget-btn:not(:disabled):hover {
  background-size: 400% 400%;
  animation-duration: 1.5s;
  box-shadow: 0 12px 40px rgba(102, 126, 234, 0.5);
}

/* Button Sparkles Animation */
.btn-sparkles {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  pointer-events: none;
  overflow: hidden;
  border-radius: 16px;
}

.sparkle {
  position: absolute;
  width: 4px;
  height: 4px;
  background: white;
  border-radius: 50%;
  opacity: 0;
  animation: sparkle-twinkle 2s infinite;
}

.sparkle-1 {
  top: 20%;
  left: 20%;
  animation-delay: 0s;
}

.sparkle-2 {
  top: 60%;
  right: 30%;
  animation-delay: 0.7s;
}

.sparkle-3 {
  bottom: 25%;
  left: 70%;
  animation-delay: 1.4s;
}

/* Keyframe Animations */
@keyframes rainbow-flow {
  0%, 100% {
    background-position: 0% 50%;
  }
  50% {
    background-position: 100% 50%;
  }
}

@keyframes sparkle-twinkle {
  0%, 100% {
    opacity: 0;
    transform: scale(0);
  }
  50% {
    opacity: 1;
    transform: scale(1);
  }
}

/* Pulse effect on hover */
.colorful-budget-btn:not(:disabled)::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: inherit;
  border-radius: inherit;
  opacity: 0;
  animation: btn-pulse 2s infinite;
}

@keyframes btn-pulse {
  0% {
    transform: scale(1);
    opacity: 0;
  }
  50% {
    transform: scale(1.05);
    opacity: 0.3;
  }
  100% {
    transform: scale(1.1);
    opacity: 0;
  }
}
</style>