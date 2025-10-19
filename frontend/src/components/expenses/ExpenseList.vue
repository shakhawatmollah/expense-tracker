<template>
  <div class="space-y-6">
    <!-- Header with Add Button -->
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-lg font-medium text-gray-900">Expenses</h2>
        <p class="text-sm text-gray-600">Manage your expenses</p>
      </div>
      <button @click="openAddModal" class="colorful-budget-btn create-expense-btn">
        <PlusIcon class="h-4 w-4" />
        Add Expense
        <div class="btn-sparkles">
          <div class="sparkle sparkle-1"></div>
          <div class="sparkle sparkle-2"></div>
          <div class="sparkle sparkle-3"></div>
        </div>
      </button>
    </div>

    <!-- Filters -->
    <div class="bg-white p-4 rounded-lg shadow">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700">Search</label>
          <input
            type="text"
            v-model="searchQuery"
            placeholder="Search expenses..."
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Category</label>
          <select
            v-model="selectedCategory"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
          >
            <option value="">All Categories</option>
            <option v-for="category in categories" :key="category.id" :value="category.id">
              {{ category.name }}
            </option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Start Date</label>
          <input
            type="date"
            v-model="startDate"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">End Date</label>
          <input
            type="date"
            v-model="endDate"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
          />
        </div>
      </div>
    </div>

    <!-- Expenses List -->
    <div class="bg-white shadow overflow-hidden sm:rounded-md">
      <div v-if="loading" class="text-center py-8">
        <LoadingSpinner class="mx-auto h-8 w-8" />
        <p class="mt-2 text-sm text-gray-500">Loading expenses...</p>
      </div>
      <div v-else-if="expenses.length === 0" class="text-center py-8">
        <CreditCardIcon class="mx-auto h-12 w-12 text-gray-400" />
        <h3 class="mt-2 text-sm font-medium text-gray-900">No expenses found</h3>
        <p class="mt-1 text-sm text-gray-500">
          {{ pagination.total === 0 ? 'Get started by creating a new expense.' : 'Try adjusting your filters.' }}
        </p>
        <button v-if="pagination.total === 0" @click="openAddModal" class="colorful-budget-btn create-expense-btn mt-4">
          Add Your First Expense
          <div class="btn-sparkles">
            <div class="sparkle sparkle-1"></div>
            <div class="sparkle sparkle-2"></div>
            <div class="sparkle sparkle-3"></div>
          </div>
        </button>
      </div>
      <ul v-else class="divide-y divide-gray-200">
        <li v-for="expense in expenses" :key="expense.id" class="px-6 py-4 hover:bg-gray-50">
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
              <div class="flex-shrink-0">
                <div 
                  class="h-10 w-10 rounded-full flex items-center justify-center text-white text-sm font-medium"
                  :style="{ backgroundColor: expense.category?.color || '#6B7280' }"
                >
                  {{ expense.category?.name?.charAt(0)?.toUpperCase() || 'U' }}
                </div>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate">
                  {{ expense.description }}
                </p>
                <p class="text-sm text-gray-500">
                  {{ expense.category?.name || 'Uncategorized' }} • {{ formatDate(expense.date) }}
                </p>
              </div>
            </div>
            <div class="flex items-center space-x-4">
              <div class="text-right">
                <p class="text-lg font-semibold text-gray-900">
                  ${{ formatAmount(expense.amount) }}
                </p>
              </div>
              <div class="flex space-x-2">
                <Button size="sm" variant="outline" @click="openEditModal(expense)">
                  <PencilIcon class="h-4 w-4" />
                </Button>
                <Button size="sm" variant="danger" @click="confirmDelete(expense)" :loading="deletingId === expense.id">
                  <TrashIcon class="h-4 w-4" />
                </Button>
              </div>
            </div>
          </div>
        </li>
      </ul>
    </div>

    <!-- Pagination -->
    <Pagination 
      v-if="expenses.length > 0 && pagination.last_page > 1"
      :pagination="pagination"
      @page-changed="handlePageChange"
    />

    <!-- Expense Form Modal -->
    <ExpenseForm
      :is-open="showForm"
      :expense="selectedExpense"
      @close="closeModal"
      @saved="handleExpenseSaved"
    />

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteConfirm" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click.self="closeDeleteConfirm">
      <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
          <ExclamationTriangleIcon class="mx-auto h-12 w-12 text-red-500" />
          <h3 class="text-lg font-medium text-gray-900 mt-2">Delete Expense</h3>
          <p class="text-sm text-gray-500 mt-2">
            Are you sure you want to delete "{{ expenseToDelete?.description }}"? This action cannot be undone.
          </p>
          <div class="flex justify-center space-x-3 mt-6">
            <Button variant="outline" @click="closeDeleteConfirm">Cancel</Button>
            <Button variant="danger" @click="deleteExpense" :loading="deletingId === expenseToDelete?.id">
              Delete
            </Button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { 
  CreditCardIcon, 
  PlusIcon, 
  PencilIcon, 
  TrashIcon,
  ExclamationTriangleIcon 
} from '@heroicons/vue/24/outline'
import { useExpensesStore } from '@/stores/expenses'
import { useCategoriesStore } from '@/stores/categories'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'
import Button from '@/components/common/Button.vue'
import Pagination from '@/components/common/Pagination.vue'
import ExpenseForm from './ExpenseForm.vue'

const route = useRoute()
const router = useRouter()
const expenseStore = useExpensesStore()
const categoryStore = useCategoriesStore()

// Reactive data
const showForm = ref(false)
const selectedExpense = ref(null)
const showDeleteConfirm = ref(false)
const expenseToDelete = ref(null)
const deletingId = ref(null)

// Filter states
const searchQuery = ref('')
const selectedCategory = ref('')
const startDate = ref('')
const endDate = ref('')

// Computed properties
const expenses = computed(() => expenseStore.expenses)
const categories = computed(() => categoryStore.categories)
const loading = computed(() => expenseStore.loading)
const pagination = computed(() => expenseStore.pagination)

// Current page and filters state
const currentPage = ref(1)
const currentFilters = ref({})

// Method to fetch expenses with current filters and page
const fetchExpensesWithFilters = async () => {
  const filters = {
    page: currentPage.value,
    per_page: 15,
    search: searchQuery.value.trim() || undefined,
    category_id: selectedCategory.value || undefined,
    start_date: startDate.value || undefined,
    end_date: endDate.value || undefined
  }

  // Remove undefined values
  Object.keys(filters).forEach(key => {
    if (filters[key] === undefined) {
      delete filters[key]
    }
  })

  currentFilters.value = filters
  await expenseStore.fetchExpenses(filters)
}

const handlePageChange = (page) => {
  currentPage.value = page
  fetchExpensesWithFilters()
}

// Methods
const openAddModal = () => {
  selectedExpense.value = null
  showForm.value = true
}

const openEditModal = (expense) => {
  selectedExpense.value = { ...expense }
  showForm.value = true
}

const closeModal = () => {
  showForm.value = false
  selectedExpense.value = null
}

const handleExpenseSaved = () => {
  closeModal()
  // Refresh current page with current filters
  fetchExpensesWithFilters()
}

const confirmDelete = (expense) => {
  expenseToDelete.value = expense
  showDeleteConfirm.value = true
}

const closeDeleteConfirm = () => {
  showDeleteConfirm.value = false
  expenseToDelete.value = null
}

const deleteExpense = async () => {
  if (!expenseToDelete.value) return

  try {
    deletingId.value = expenseToDelete.value.id
    await expenseStore.deleteExpense(expenseToDelete.value.id)
    closeDeleteConfirm()
  } catch (error) {
    console.error('Failed to delete expense:', error)
  } finally {
    deletingId.value = null
  }
}

const formatAmount = (amount) => {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(amount)
}

const formatDate = (date) => {
  return new Intl.DateTimeFormat('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  }).format(new Date(date))
}

// Watchers for real-time filtering
watch([searchQuery, selectedCategory, startDate, endDate], () => {
  currentPage.value = 1 // Reset to first page when filters change
  fetchExpensesWithFilters()
})

// Lifecycle
onMounted(() => {
  fetchExpensesWithFilters()
  categoryStore.fetchCategories()
  
  // Check if we should auto-open the create form from Quick Action
  if (route.query.action === 'create') {
    openAddModal()
    // Clear the query parameter after opening modal
    router.replace({ path: route.path })
  }
})
</script>

<style scoped>
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

/* Create Expense Button - Green/Blue Gradient */
.create-expense-btn {
  background: linear-gradient(45deg, 
    #22c55e 0%, 
    #10b981 25%, 
    #06b6d4 50%, 
    #3b82f6 75%, 
    #6366f1 100%);
  background-size: 300% 300%;
  animation: expense-flow 3s ease infinite;
}

.create-expense-btn:not(:disabled):hover {
  background-size: 400% 400%;
  animation-duration: 1.5s;
  box-shadow: 0 12px 40px rgba(34, 197, 94, 0.5);
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
@keyframes expense-flow {
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