<template>
  <div class="modal-overlay" @click="handleOverlayClick">
    <div class="modal-container" @click.stop>
      <!-- Modal Header -->
      <div class="modal-header">
        <h2 class="modal-title">
          {{ isEditing ? 'Edit Budget' : 'Create New Budget' }}
        </h2>
        <button @click="$emit('close')" class="modal-close">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <!-- Modal Body -->
      <div class="modal-body">
        <form @submit.prevent="handleSubmit" class="budget-form">
          <!-- Budget Name -->
          <div class="form-group">
            <label for="budget-name" class="form-label required">Budget Name</label>
            <input
              id="budget-name"
              v-model="form.name"
              type="text"
              class="form-input"
              :class="{ 'error': errors.name }"
              placeholder="Enter budget name (e.g., Food & Dining)"
              maxlength="100"
            />
            <div v-if="errors.name" class="form-error">{{ errors.name }}</div>
          </div>

          <!-- Category -->
          <div class="form-group">
            <label for="budget-category" class="form-label required">Category</label>
            <select
              id="budget-category"
              v-model="form.category_id"
              class="form-select"
              :class="{ 'error': errors.category_id }"
            >
              <option value="">Select a category</option>
              <option 
                v-for="category in categoryStore.categories" 
                :key="category.id" 
                :value="category.id"
              >
                {{ category.name }}
              </option>
            </select>
            <div v-if="errors.category_id" class="form-error">{{ errors.category_id }}</div>
          </div>

          <!-- Budget Amount -->
          <div class="form-group">
            <label for="budget-amount" class="form-label required">Budget Amount</label>
            <div class="input-group">
              <span class="input-prefix">$</span>
              <input
                id="budget-amount"
                v-model="form.amount"
                type="number"
                step="0.01"
                min="0"
                class="form-input"
                :class="{ 'error': errors.amount }"
                placeholder="0.00"
              />
            </div>
            <div v-if="errors.amount" class="form-error">{{ errors.amount }}</div>
          </div>

          <!-- Period -->
          <div class="form-group">
            <label for="budget-period" class="form-label required">Period</label>
            <select
              id="budget-period"
              v-model="form.period"
              class="form-select"
              :class="{ 'error': errors.period }"
              @change="updateDatesBasedOnPeriod"
            >
              <option value="">Select period</option>
              <option value="weekly">Weekly</option>
              <option value="monthly">Monthly</option>
              <option value="quarterly">Quarterly</option>
              <option value="yearly">Yearly</option>
            </select>
            <div v-if="errors.period" class="form-error">{{ errors.period }}</div>
          </div>

          <!-- Date Range -->
          <div class="form-group">
            <div class="date-range-group">
              <div class="date-field">
                <label for="budget-start-date" class="form-label required">Start Date</label>
                <input
                  id="budget-start-date"
                  v-model="form.start_date"
                  type="date"
                  class="form-input"
                  :class="{ 'error': errors.start_date }"
                />
                <div v-if="errors.start_date" class="form-error">{{ errors.start_date }}</div>
              </div>
              <div class="date-field">
                <label for="budget-end-date" class="form-label required">End Date</label>
                <input
                  id="budget-end-date"
                  v-model="form.end_date"
                  type="date"
                  class="form-input"
                  :class="{ 'error': errors.end_date }"
                  :min="form.start_date"
                />
                <div v-if="errors.end_date" class="form-error">{{ errors.end_date }}</div>
              </div>
            </div>
          </div>

          <!-- Alert Thresholds -->
          <div class="form-group">
            <label class="form-label">Alert Thresholds</label>
            <div class="threshold-group">
              <div class="threshold-field">
                <label for="warning-threshold" class="threshold-label">Warning (%)</label>
                <input
                  id="warning-threshold"
                  v-model="form.warning_threshold"
                  type="number"
                  min="0"
                  max="100"
                  class="form-input"
                  placeholder="80"
                />
              </div>
              <div class="threshold-field">
                <label for="critical-threshold" class="threshold-label">Critical (%)</label>
                <input
                  id="critical-threshold"
                  v-model="form.critical_threshold"
                  type="number"
                  min="0"
                  max="100"
                  class="form-input"
                  placeholder="100"
                />
              </div>
            </div>
            <p class="form-help">Set percentage thresholds for budget alerts</p>
          </div>

          <!-- Status -->
          <div class="form-group">
            <div class="checkbox-group">
              <input
                id="budget-active"
                v-model="form.is_active"
                type="checkbox"
                class="form-checkbox"
              />
              <label for="budget-active" class="checkbox-label">
                Active Budget
                <span class="checkbox-help">Budget is currently active and tracking expenses</span>
              </label>
            </div>
          </div>

          <!-- Form Actions -->
          <div class="form-actions">
            <button 
              type="button" 
              @click="$emit('close')" 
              class="btn btn-secondary"
            >
              Cancel
            </button>
            <button 
              type="submit" 
              class="btn btn-primary"
              :disabled="isSubmitting"
            >
              <i v-if="isSubmitting" class="fas fa-spinner fa-spin mr-2"></i>
              {{ isEditing ? 'Update Budget' : 'Create Budget' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive, computed, watch, onMounted } from 'vue'
import { useBudgetStore } from '@/stores/budget'
import { useCategoriesStore } from '@/stores/categories'

export default {
  name: 'BudgetModal',
  props: {
    budget: {
      type: Object,
      default: null
    },
    isEditing: {
      type: Boolean,
      default: false
    }
  },
  emits: ['close', 'saved'],
  setup(props, { emit }) {
    const budgetStore = useBudgetStore()
    const categoryStore = useCategoriesStore()
    
    const isSubmitting = ref(false)
    const errors = ref({})
    
    // Form data
    const form = reactive({
      name: '',
      category_id: '',
      amount: '',
      period: '',
      start_date: '',
      end_date: '',
      warning_threshold: 80,
      critical_threshold: 100,
      is_active: true
    })
    
    // Initialize form data
    const initializeForm = () => {
      if (props.isEditing && props.budget) {
        Object.keys(form).forEach(key => {
          if (props.budget.hasOwnProperty(key)) {
            form[key] = props.budget[key]
          }
        })
      } else {
        // Set default dates for new budget
        const today = new Date()
        form.start_date = today.toISOString().split('T')[0]
        
        // Default to end of current month
        const endOfMonth = new Date(today.getFullYear(), today.getMonth() + 1, 0)
        form.end_date = endOfMonth.toISOString().split('T')[0]
        form.period = 'monthly'
      }
    }
    
    // Update end date based on period selection
    const updateDatesBasedOnPeriod = () => {
      if (!form.start_date || !form.period) return
      
      const startDate = new Date(form.start_date)
      let endDate = new Date(startDate)
      
      switch (form.period) {
        case 'weekly':
          endDate.setDate(startDate.getDate() + 6)
          break
        case 'monthly':
          endDate.setMonth(startDate.getMonth() + 1)
          endDate.setDate(0) // Last day of the month
          break
        case 'quarterly':
          endDate.setMonth(startDate.getMonth() + 3)
          endDate.setDate(0)
          break
        case 'yearly':
          endDate.setFullYear(startDate.getFullYear() + 1)
          endDate.setDate(startDate.getDate() - 1)
          break
      }
      
      form.end_date = endDate.toISOString().split('T')[0]
    }
    
    // Validation
    const validateForm = () => {
      errors.value = {}
      
      if (!form.name.trim()) {
        errors.value.name = 'Budget name is required'
      }
      
      if (!form.category_id) {
        errors.value.category_id = 'Category is required'
      }
      
      if (!form.amount || form.amount <= 0) {
        errors.value.amount = 'Budget amount must be greater than 0'
      }
      
      if (!form.period) {
        errors.value.period = 'Period is required'
      }
      
      if (!form.start_date) {
        errors.value.start_date = 'Start date is required'
      }
      
      if (!form.end_date) {
        errors.value.end_date = 'End date is required'
      } else if (form.start_date && form.end_date < form.start_date) {
        errors.value.end_date = 'End date must be after start date'
      }
      
      return Object.keys(errors.value).length === 0
    }
    
    // Handle form submission
    const handleSubmit = async () => {
      if (!validateForm()) return
      
      try {
        isSubmitting.value = true
        
        const budgetData = {
          name: form.name.trim(),
          category_id: parseInt(form.category_id),
          amount: parseFloat(form.amount),
          period: form.period,
          start_date: form.start_date,
          end_date: form.end_date,
          warning_threshold: form.warning_threshold || 80,
          critical_threshold: form.critical_threshold || 100,
          is_active: form.is_active
        }
        
        if (props.isEditing) {
          await budgetStore.updateBudget(props.budget.id, budgetData)
        } else {
          await budgetStore.createBudget(budgetData)
        }
        
        emit('saved')
      } catch (error) {
        console.error('Failed to save budget:', error)
        
        // Handle validation errors from backend
        if (error.response?.data?.errors) {
          errors.value = error.response.data.errors
        }
      } finally {
        isSubmitting.value = false
      }
    }
    
    // Handle overlay click
    const handleOverlayClick = (event) => {
      if (event.target === event.currentTarget) {
        emit('close')
      }
    }
    
    // Watch for start date changes to update end date
    watch(() => form.start_date, () => {
      if (form.period) {
        updateDatesBasedOnPeriod()
      }
    })
    
    // Initialize
    onMounted(() => {
      initializeForm()
      
      // Load categories if not already loaded
      if (categoryStore.categories.length === 0) {
        categoryStore.fetchCategories()
      }
    })
    
    return {
      budgetStore,
      categoryStore,
      form,
      errors,
      isSubmitting,
      handleSubmit,
      handleOverlayClick,
      updateDatesBasedOnPeriod
    }
  }
}
</script>

<style scoped>
.modal-overlay {
  @apply fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4;
}

.modal-container {
  @apply bg-white rounded-lg shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto;
}

.modal-header {
  @apply flex justify-between items-center p-6 border-b border-gray-200;
}

.modal-title {
  @apply text-xl font-semibold text-gray-800;
}

.modal-close {
  @apply text-gray-400 hover:text-gray-600 text-xl;
}

.modal-body {
  @apply p-6;
}

.budget-form {
  @apply space-y-6;
}

.form-group {
  @apply space-y-2;
}

.form-label {
  @apply block text-sm font-medium text-gray-700;
}

.form-label.required::after {
  @apply text-red-500 ml-1;
  content: '*';
}

.form-input {
  @apply w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500;
}

.form-select {
  @apply w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500;
}

.form-input.error,
.form-select.error {
  @apply border-red-500 focus:ring-red-500 focus:border-red-500;
}

.form-error {
  @apply text-sm text-red-600;
}

.form-help {
  @apply text-sm text-gray-500;
}

.input-group {
  @apply relative;
}

.input-prefix {
  @apply absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500;
}

.input-group .form-input {
  @apply pl-8;
}

.date-range-group {
  @apply grid grid-cols-1 md:grid-cols-2 gap-4;
}

.threshold-group {
  @apply grid grid-cols-2 gap-4;
}

.threshold-label {
  @apply block text-sm font-medium text-gray-600 mb-1;
}

.checkbox-group {
  @apply flex items-start;
}

.form-checkbox {
  @apply mr-3 mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded;
}

.checkbox-label {
  @apply text-sm font-medium text-gray-700 cursor-pointer;
}

.checkbox-help {
  @apply block text-xs text-gray-500 font-normal mt-1;
}

.form-actions {
  @apply flex justify-end space-x-3 pt-4 border-t border-gray-200;
}
</style>