<template>
  <Teleport to="body">
    <div 
      v-if="isVisible" 
      class="modal-overlay"
      @click="closeModal"
    >
      <div 
        class="quick-add-modal"
        @click.stop
      >
        <!-- Modal Header -->
        <div class="modal-header">
          <div class="modal-title">
            <i class="fas fa-plus-circle text-blue-500"></i>
            <h3>Quick Add Expense</h3>
          </div>
          <button @click="closeModal" class="close-btn">
            <i class="fas fa-times"></i>
          </button>
        </div>

        <!-- Modal Content -->
        <form @submit.prevent="submitExpense" class="modal-content">
          <!-- Amount Input -->
          <div class="form-group">
            <label for="amount" class="form-label">
              <i class="fas fa-dollar-sign"></i>
              Amount
            </label>
            <input
              id="amount"
              v-model="form.amount"
              type="number"
              step="0.01"
              min="0"
              class="form-input"
              placeholder="0.00"
              required
              autofocus
            />
          </div>

          <!-- Description Input -->
          <div class="form-group">
            <label for="description" class="form-label">
              <i class="fas fa-edit"></i>
              Description
            </label>
            <input
              id="description"
              v-model="form.description"
              type="text"
              class="form-input"
              placeholder="What did you spend on?"
              required
            />
          </div>

          <!-- Category Select -->
          <div class="form-group">
            <label for="category" class="form-label">
              <i class="fas fa-tags"></i>
              Category
            </label>
            <select
              id="category"
              v-model="form.category_id"
              class="form-select"
              required
            >
              <option value="">Select a category</option>
              <option 
                v-for="category in categories" 
                :key="category.id" 
                :value="category.id"
              >
                {{ category.name }}
              </option>
            </select>
          </div>

          <!-- Date Input -->
          <div class="form-group">
            <label for="date" class="form-label">
              <i class="fas fa-calendar"></i>
              Date
            </label>
            <input
              id="date"
              v-model="form.date"
              type="date"
              class="form-input"
              required
            />
          </div>

          <!-- Quick Category Buttons -->
          <div class="quick-categories">
            <h4 class="quick-categories-title">Quick Categories</h4>
            <div class="category-buttons">
              <button
                v-for="category in popularCategories"
                :key="category.id"
                type="button"
                @click="selectCategory(category.id)"
                :class="['category-btn', { active: form.category_id === category.id }]"
                :style="{ borderColor: category.color, color: category.color }"
              >
                <i :class="getCategoryIcon(category.name)"></i>
                {{ category.name }}
              </button>
            </div>
          </div>

          <!-- Modal Actions -->
          <div class="modal-actions">
            <button 
              type="button" 
              @click="closeModal" 
              class="btn-secondary"
            >
              Cancel
            </button>
            <button 
              type="submit" 
              :disabled="isSubmitting"
              class="btn-primary"
            >
              <i v-if="isSubmitting" class="fas fa-spinner fa-spin"></i>
              <i v-else class="fas fa-plus"></i>
              {{ isSubmitting ? 'Adding...' : 'Add Expense' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useCategoriesStore } from '@/stores/categories'
import { useExpensesStore } from '@/stores/expenses'

// Props
const props = defineProps({
  isVisible: {
    type: Boolean,
    default: false
  }
})

// Emits
const emit = defineEmits(['close', 'expense-added'])

// Stores
const categoriesStore = useCategoriesStore()
const expensesStore = useExpensesStore()

// Reactive state
const isSubmitting = ref(false)
const form = ref({
  amount: '',
  description: '',
  category_id: '',
  date: new Date().toISOString().split('T')[0] // Today's date
})

// Computed properties
const categories = computed(() => categoriesStore.categories)

const popularCategories = computed(() => {
  // Show most popular categories first
  return categories.value.slice(0, 6)
})

// Methods
const closeModal = () => {
  emit('close')
  resetForm()
}

const resetForm = () => {
  form.value = {
    amount: '',
    description: '',
    category_id: '',
    date: new Date().toISOString().split('T')[0]
  }
}

const selectCategory = (categoryId) => {
  form.value.category_id = categoryId
}

const getCategoryIcon = (categoryName) => {
  const iconMap = {
    'Food & Dining': 'fas fa-utensils',
    'Transportation': 'fas fa-car',
    'Shopping': 'fas fa-shopping-bag',
    'Entertainment': 'fas fa-film',
    'Bills & Utilities': 'fas fa-file-invoice-dollar',
    'Healthcare': 'fas fa-heartbeat',
    'Education': 'fas fa-graduation-cap',
    'Travel': 'fas fa-plane',
    'Personal Care': 'fas fa-spa',
    'Other': 'fas fa-ellipsis-h'
  }
  return iconMap[categoryName] || 'fas fa-tag'
}

const submitExpense = async () => {
  if (isSubmitting.value) return

  try {
    isSubmitting.value = true
    
    // Validate form
    if (!form.value.amount || !form.value.description || !form.value.category_id) {
      throw new Error('Please fill in all required fields')
    }

    // Prepare expense data
    const expenseData = {
      amount: parseFloat(form.value.amount),
      description: form.value.description.trim(),
      category_id: parseInt(form.value.category_id),
      date: form.value.date
    }

    // Create expense
    await expensesStore.createExpense(expenseData)

    // Show success notification
    if (window.notify) {
      window.notify.success('Expense Added!', `$${expenseData.amount} expense for ${expenseData.description} has been added successfully`)
    }

    // Emit event and close modal
    emit('expense-added', expenseData)
    closeModal()

  } catch (error) {
    console.error('Error adding expense:', error)
    
    // Show error notification
    if (window.notify) {
      window.notify.error('Error', error.message || 'Failed to add expense. Please try again.')
    }
  } finally {
    isSubmitting.value = false
  }
}

// Watch for modal visibility to reset form
watch(() => props.isVisible, (newVal) => {
  if (newVal) {
    resetForm()
  }
})

// Load categories on mount
onMounted(async () => {
  if (categories.value.length === 0) {
    try {
      await categoriesStore.fetchCategories()
    } catch (error) {
      console.error('Failed to load categories:', error)
    }
  }
})
</script>

<style scoped>
/* Modal Overlay */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 1rem;
}

/* Modal Container */
.quick-add-modal {
  background: white;
  border-radius: 24px;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
  width: 100%;
  max-width: 500px;
  max-height: 90vh;
  overflow-y: auto;
  animation: modalSlideIn 0.3s ease-out;
}

@keyframes modalSlideIn {
  from {
    opacity: 0;
    transform: translateY(-20px) scale(0.95);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

/* Modal Header */
.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem 1.5rem 1rem;
  border-bottom: 1px solid #f1f5f9;
}

.modal-title {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.modal-title h3 {
  font-size: 1.25rem;
  font-weight: 600;
  color: #1e293b;
  margin: 0;
}

.close-btn {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  border: none;
  background: #f8fafc;
  color: #64748b;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
}

.close-btn:hover {
  background: #e2e8f0;
  color: #475569;
}

/* Modal Content */
.modal-content {
  padding: 1rem 1.5rem 1.5rem;
}

/* Form Groups */
.form-group {
  margin-bottom: 1.5rem;
}

.form-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
  font-weight: 500;
  color: #374151;
  margin-bottom: 0.5rem;
}

.form-input,
.form-select {
  width: 100%;
  padding: 0.75rem 1rem;
  border: 1px solid #d1d5db;
  border-radius: 12px;
  font-size: 1rem;
  transition: all 0.2s ease;
  background: white;
}

.form-input:focus,
.form-select:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Quick Categories */
.quick-categories {
  margin-bottom: 1.5rem;
}

.quick-categories-title {
  font-size: 0.875rem;
  font-weight: 500;
  color: #374151;
  margin-bottom: 0.75rem;
}

.category-buttons {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
  gap: 0.5rem;
}

.category-btn {
  padding: 0.75rem;
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  background: white;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  justify-content: center;
}

.category-btn:hover {
  background: #f8fafc;
  transform: translateY(-1px);
}

.category-btn.active {
  background: rgba(59, 130, 246, 0.1);
  border-color: #3b82f6;
}

/* Modal Actions */
.modal-actions {
  display: flex;
  gap: 0.75rem;
  justify-content: flex-end;
  padding-top: 1rem;
  border-top: 1px solid #f1f5f9;
}

.btn-secondary {
  padding: 0.75rem 1.5rem;
  border: 1px solid #d1d5db;
  border-radius: 12px;
  background: white;
  color: #374151;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-secondary:hover {
  background: #f9fafb;
  border-color: #9ca3af;
}

.btn-primary {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 12px;
  background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
  color: white;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.btn-primary:hover:not(:disabled) {
  background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
}

.btn-primary:disabled {
  opacity: 0.7;
  cursor: not-allowed;
  transform: none;
}

/* Mobile Responsive */
@media (max-width: 640px) {
  .modal-overlay {
    padding: 0.5rem;
  }
  
  .quick-add-modal {
    max-height: 95vh;
    border-radius: 16px;
  }
  
  .modal-header {
    padding: 1rem 1rem 0.75rem;
  }
  
  .modal-content {
    padding: 0.75rem 1rem 1rem;
  }
  
  .category-buttons {
    grid-template-columns: 1fr 1fr;
  }
  
  .modal-actions {
    flex-direction: column-reverse;
  }
  
  .btn-secondary,
  .btn-primary {
    width: 100%;
    justify-content: center;
  }
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
  .quick-add-modal {
    background: #1e293b;
    color: #f1f5f9;
  }
  
  .modal-header {
    border-bottom-color: #334155;
  }
  
  .modal-title h3 {
    color: #f1f5f9;
  }
  
  .close-btn {
    background: #334155;
    color: #94a3b8;
  }
  
  .close-btn:hover {
    background: #475569;
    color: #e2e8f0;
  }
  
  .form-label {
    color: #e2e8f0;
  }
  
  .form-input,
  .form-select {
    background: #334155;
    border-color: #475569;
    color: #f1f5f9;
  }
  
  .form-input:focus,
  .form-select:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
  }
  
  .category-btn {
    background: #334155;
    border-color: #475569;
    color: #e2e8f0;
  }
  
  .category-btn:hover {
    background: #475569;
  }
  
  .btn-secondary {
    background: #334155;
    border-color: #475569;
    color: #e2e8f0;
  }
  
  .btn-secondary:hover {
    background: #475569;
  }
  
  .modal-actions {
    border-top-color: #334155;
  }
}
</style>