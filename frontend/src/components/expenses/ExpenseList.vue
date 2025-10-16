<template>
  <div class="space-y-6">
    <!-- Header with Add Button -->
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-lg font-medium text-gray-900">Expenses</h2>
        <p class="text-sm text-gray-600">Manage your expenses</p>
      </div>
      <Button @click="openAddModal" class="bg-indigo-600 hover:bg-indigo-700">
        <PlusIcon class="h-4 w-4 mr-2" />
        Add Expense
      </Button>
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
      <div v-else-if="filteredExpenses.length === 0" class="text-center py-8">
        <CreditCardIcon class="mx-auto h-12 w-12 text-gray-400" />
        <h3 class="mt-2 text-sm font-medium text-gray-900">No expenses found</h3>
        <p class="mt-1 text-sm text-gray-500">
          {{ expenses.length === 0 ? 'Get started by creating a new expense.' : 'Try adjusting your filters.' }}
        </p>
        <Button v-if="expenses.length === 0" @click="openAddModal" class="mt-4">
          Add Your First Expense
        </Button>
      </div>
      <ul v-else class="divide-y divide-gray-200">
        <li v-for="expense in filteredExpenses" :key="expense.id" class="px-6 py-4 hover:bg-gray-50">
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
import ExpenseForm from './ExpenseForm.vue'

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

const filteredExpenses = computed(() => {
  let filtered = [...expenses.value]

  // Search filter
  if (searchQuery.value.trim()) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(expense => 
      expense.description.toLowerCase().includes(query) ||
      expense.category?.name?.toLowerCase().includes(query)
    )
  }

  // Category filter
  if (selectedCategory.value) {
    filtered = filtered.filter(expense => 
      expense.category_id === parseInt(selectedCategory.value)
    )
  }

  // Date range filter
  if (startDate.value) {
    filtered = filtered.filter(expense => expense.date >= startDate.value)
  }
  if (endDate.value) {
    filtered = filtered.filter(expense => expense.date <= endDate.value)
  }

  // Sort by date (newest first)
  return filtered.sort((a, b) => new Date(b.date) - new Date(a.date))
})

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
  // Refresh expenses list
  expenseStore.fetchExpenses()
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
  // Filtering is handled by computed property
})

// Lifecycle
onMounted(() => {
  expenseStore.fetchExpenses()
  categoryStore.fetchCategories()
})
</script>