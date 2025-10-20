<template>
  <div class="modal-overlay" v-if="isOpen" @click.self="closeModal">
    <div class="modern-modal">
      <!-- Modal Header -->
      <div class="modal-header">
        <div class="header-content">
          <div class="header-icon">
            <i class="fas fa-receipt"></i>
          </div>
          <div class="header-text">
            <h3 class="modal-title">{{ isEditing ? 'Edit Expense' : 'Add New Expense' }}</h3>
            <p class="modal-subtitle">{{ isEditing ? 'Update expense details' : 'Track your spending' }}</p>
          </div>
        </div>
        <button @click="closeModal" class="modal-close" aria-label="Close modal">
          <i class="fas fa-times"></i>
        </button>
      </div>
      
      <!-- Modal Body -->
      <div class="modal-body">
        <form @submit.prevent="submitForm" class="expense-form">
          <!-- General Error Message -->
          <div v-if="errors.general" class="error-banner">
            <div class="flex">
              <div class="flex-shrink-0">
                <i class="fas fa-exclamation-circle text-red-400"></i>
              </div>
              <div class="ml-3">
                <p class="text-sm text-red-800">
                  {{ errors.general[0] }}
                </p>
              </div>
            </div>
          </div>
          
          <!-- Description Field -->
          <FormInput
            v-model="form.description"
            label="Description"
            placeholder="Enter expense description"
            :error="errors.description"
            required
            clearable
            maxlength="255"
            show-character-count
            help-text="Brief description of your expense"
          />
          
          <!-- Amount Field -->
          <FormInput
            v-model="form.amount"
            type="number"
            step="0.01"
            min="0"
            label="Amount"
            placeholder="0.00"
            prefix="$"
            :error="errors.amount"
            required
            help-text="Enter the expense amount"
          />
          
          <!-- Date Field -->
          <FormInput
            v-model="form.date"
            type="date"
            label="Date"
            :error="errors.date"
            required
            help-text="When did this expense occur?"
          />
          
          <!-- Category Field -->
          <FormSelect
            v-model="form.category_id"
            :options="categoryOptions"
            option-label="name"
            option-value="id"
            label="Category"
            placeholder="Select a category"
            :error="errors.category_id"
            required
            searchable
            help-text="Choose the expense category"
          >
            <template #option="{ option }">
              <div class="category-option">
                <i :class="option.icon || 'fas fa-tag'" class="category-icon"></i>
                <span class="category-name">{{ option.name }}</span>
              </div>
            </template>
          </FormSelect>
          
          <!-- Form Actions -->
          <div class="form-actions">
            <Button 
              variant="outline" 
              @click="closeModal"
              :disabled="loading"
            >
              Cancel
            </Button>
            <button 
              type="submit" 
              :disabled="loading"
              :class="[
                'colorful-budget-btn',
                isEditing ? 'update-expense-btn' : 'create-expense-btn'
              ]"
            >
              <i class="fas fa-save"></i>
              {{ isEditing ? 'Update' : 'Create' }} Expense
              <div class="btn-sparkles">
                <div class="sparkle sparkle-1"></div>
                <div class="sparkle sparkle-2"></div>
                <div class="sparkle sparkle-3"></div>
              </div>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue'
import { useExpensesStore } from '@/stores/expenses'
import { useCategoriesStore } from '@/stores/categories'
import FormInput from '@/components/common/FormInput.vue'
import FormSelect from '@/components/common/FormSelect.vue'
import Button from '@/components/common/Button.vue'

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  },
  expense: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close', 'saved'])

const expensesStore = useExpensesStore()
const categoriesStore = useCategoriesStore()

const loading = ref(false)
const errors = ref({})

const form = reactive({
  description: '',
  amount: '',
  date: new Date().toISOString().split('T')[0],
  category_id: ''
})

const isEditing = computed(() => !!props.expense)
const categories = computed(() => categoriesStore.categories)

// Transform categories for FormSelect component
const categoryOptions = computed(() => {
  return categories.value.map(category => ({
    id: category.id,
    name: category.name,
    icon: category.icon || 'fas fa-tag'
  }))
})

// Define resetForm function before it's used
const resetForm = () => {
  form.description = ''
  form.amount = ''
  form.date = new Date().toISOString().split('T')[0]
  form.category_id = ''
}

// Watch for expense prop changes (for editing)
watch(() => props.expense, (newExpense) => {
  if (newExpense) {
    form.description = newExpense.description
    // Handle both old and new API response formats
    form.amount = newExpense.amount
    form.date = newExpense.date
    // Handle both old and new category formats
    form.category_id = typeof newExpense.category === 'object'
      ? newExpense.category.id
      : newExpense.category_id
  } else {
    resetForm()
  }
}, { immediate: true })

// Watch for modal open/close
watch(() => props.isOpen, (isOpen) => {
  if (isOpen) {
    categoriesStore.fetchCategories()
    if (!props.expense) {
      resetForm()
    }
  }
  errors.value = {}
})

const closeModal = () => {
  emit('close')
  errors.value = {}
}

const submitForm = async () => {
  try {
    loading.value = true
    errors.value = {}
    
    // Validate form data
    if (!form.description?.trim()) {
      errors.value.description = ['Description is required']
      return
    }
    
    if (!form.amount || isNaN(parseFloat(form.amount)) || parseFloat(form.amount) <= 0) {
      errors.value.amount = ['Please enter a valid amount greater than 0']
      return
    }
    
    if (!form.date) {
      errors.value.date = ['Date is required']
      return
    }
    
    if (!form.category_id) {
      errors.value.category_id = ['Please select a category']
      return
    }
    
    const formData = {
      description: form.description.trim(),
      amount: parseFloat(form.amount),
      date: form.date,
      category_id: parseInt(form.category_id)
    }
    
    console.log('Submitting expense data:', formData)
    
    if (isEditing.value) {
      await expensesStore.updateExpense(props.expense.id, formData)
    } else {
      await expensesStore.createExpense(formData)
    }
    
    emit('saved')
    closeModal()
  } catch (error) {
    console.error('Error saving expense:', error)
    
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    } else if (error.response?.data?.message) {
      // Show general error message
      errors.value.general = [error.response.data.message]
    } else {
      errors.value.general = ['An unexpected error occurred. Please try again.']
    }
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
/* Modal Overlay */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(8px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 1rem;
  overflow-y: auto;
}

/* Modern Modal */
.modern-modal {
  background: white;
  border-radius: 24px;
  box-shadow: 0 25px 80px rgba(0, 0, 0, 0.15);
  width: 100%;
  max-width: 500px;
  max-height: 90vh;
  overflow: hidden;
  position: relative;
  animation: modalSlideIn 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

@keyframes modalSlideIn {
  from {
    opacity: 0;
    transform: translateY(30px) scale(0.95);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

/* Modal Header */
.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 2rem 2rem 1rem;
  border-bottom: 1px solid #f3f4f6;
  background: linear-gradient(135deg, rgba(102, 126, 234, 0.05), rgba(118, 75, 162, 0.05));
}

.header-content {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.header-icon {
  width: 48px;
  height: 48px;
  background: linear-gradient(135deg, #667eea, #764ba2);
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1.25rem;
}

.header-text {
  flex: 1;
}

.modal-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1f2937;
  margin: 0 0 0.25rem 0;
}

.modal-subtitle {
  font-size: 0.875rem;
  color: #6b7280;
  margin: 0;
}

.modal-close {
  width: 40px;
  height: 40px;
  border: none;
  background: rgba(107, 114, 128, 0.1);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #6b7280;
  cursor: pointer;
  transition: all 0.3s ease;
}

.modal-close:hover {
  background: rgba(239, 68, 68, 0.1);
  color: #ef4444;
  transform: scale(1.05);
}

/* Modal Body */
.modal-body {
  padding: 2rem;
  overflow-y: auto;
  max-height: calc(90vh - 120px);
}

.expense-form {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

/* Error Banner */
.error-banner {
  background: linear-gradient(135deg, #fef2f2, #fee2e2);
  border: 1px solid #fecaca;
  border-radius: 12px;
  padding: 1rem;
  margin-bottom: 1rem;
  box-shadow: 0 4px 12px rgba(220, 38, 38, 0.1);
}

/* Category Option Styling */
.category-option {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.category-icon {
  width: 20px;
  color: #667eea;
  text-align: center;
}

.category-name {
  font-weight: 500;
}

/* Form Actions */
.form-actions {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
  padding-top: 1rem;
  margin-top: 1rem;
  border-top: 1px solid #f3f4f6;
}

/* Responsive Design */
@media (max-width: 640px) {
  .modal-overlay {
    padding: 0.5rem;
  }
  
  .modern-modal {
    max-width: none;
    border-radius: 20px;
    margin: 0.5rem;
  }
  
  .modal-header {
    padding: 1.5rem 1.5rem 1rem;
  }
  
  .modal-body {
    padding: 1.5rem;
  }
  
  .form-actions {
    flex-direction: column-reverse;
  }
  
  .header-content {
    gap: 0.75rem;
  }
  
  .header-icon {
    width: 40px;
    height: 40px;
    font-size: 1rem;
  }
  
  .modal-title {
    font-size: 1.25rem;
  }
}

/* Focus trap for accessibility */
.modern-modal {
  isolation: isolate;
}

/* Smooth transitions for form validation */
.expense-form :deep(.form-field) {
  transition: all 0.3s ease;
}

.expense-form :deep(.has-error) {
  animation: shake 0.5s ease-in-out;
}

@keyframes shake {
  0%, 100% { transform: translateX(0); }
  25% { transform: translateX(-5px); }
  75% { transform: translateX(5px); }
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

/* Update Expense Button - Orange/Red Gradient */
.update-expense-btn {
  background: linear-gradient(45deg, 
    #f97316 0%, 
    #ef4444 25%, 
    #ec4899 50%, 
    #a855f7 75%, 
    #8b5cf6 100%);
  background-size: 300% 300%;
  animation: update-expense-flow 3s ease infinite;
}

.update-expense-btn:not(:disabled):hover {
  background-size: 400% 400%;
  animation-duration: 1.5s;
  box-shadow: 0 12px 40px rgba(249, 115, 22, 0.5);
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

@keyframes update-expense-flow {
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