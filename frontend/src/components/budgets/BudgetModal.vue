<template>
  <div class="modal-overlay" @click="handleOverlayClick">
    <div class="modern-modal" @click.stop>
      <!-- Modal Header -->
      <div class="modal-header">
        <div class="header-content">
          <div class="header-icon">
            <i class="fas fa-chart-pie"></i>
          </div>
          <div class="header-text">
            <h2 class="modal-title">
              {{ isEditing ? 'Edit Budget' : 'Create New Budget' }}
            </h2>
            <p class="modal-subtitle">{{ isEditing ? 'Update budget settings' : 'Set spending limits' }}</p>
          </div>
        </div>
        <button @click="$emit('close')" class="modal-close" aria-label="Close modal">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <!-- Modal Body -->
      <div class="modal-body">
        <form @submit.prevent="handleSubmit" class="budget-form">
          <!-- Budget Name -->
          <FormInput
            v-model="form.name"
            label="Budget Name"
            placeholder="Enter budget name (e.g., Food & Dining)"
            :error="errors.name"
            required
            clearable
            maxlength="100"
            show-character-count
            help-text="Choose a descriptive name for your budget"
          />

          <!-- Category -->
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
            help-text="Choose the expense category for this budget"
          >
            <template #option="{ option }">
              <div class="category-option">
                <i :class="option.icon || 'fas fa-tag'" class="category-icon"></i>
                <span class="category-name">{{ option.name }}</span>
              </div>
            </template>
          </FormSelect>

          <!-- Budget Amount -->
          <FormInput
            v-model="form.amount"
            type="number"
            step="0.01"
            min="0"
            label="Budget Amount"
            placeholder="0.00"
            prefix="$"
            :error="errors.amount"
            required
            help-text="Set your spending limit for this period"
          />

          <!-- Period -->
          <FormSelect
            v-model="form.period"
            :options="periodOptions"
            option-label="label"
            option-value="value"
            label="Period"
            placeholder="Select period"
            :error="errors.period"
            required
            help-text="Choose your budget time frame"
            @select="updateDatesBasedOnPeriod"
          />

          <!-- Date Range -->
          <div class="date-range-container">
            <h4 class="section-title">Budget Period Dates</h4>
            <div class="date-range-group">
              <FormInput
                v-model="form.start_date"
                type="date"
                label="Start Date"
                :error="errors.start_date"
                required
                help-text="Budget start date"
                class="date-field"
              />
              <FormInput
                v-model="form.end_date"
                type="date"
                label="End Date"
                :error="errors.end_date"
                :min="form.start_date"
                required
                help-text="Budget end date"
                class="date-field"
              />
            </div>
          </div>

          <!-- Alert Thresholds -->
          <div class="thresholds-container">
            <h4 class="section-title">Alert Thresholds</h4>
            <div class="thresholds-group">
              <FormInput
                v-model="form.warning_threshold"
                type="number"
                min="0"
                max="100"
                label="Warning (%)"
                placeholder="80"
                suffix="%"
                help-text="Warning alert percentage"
                class="threshold-field"
              />
              <FormInput
                v-model="form.critical_threshold"
                type="number"
                min="0"
                max="100"
                label="Critical (%)"
                placeholder="100"
                suffix="%"
                help-text="Critical alert percentage"
                class="threshold-field"
              />
            </div>
            <p class="section-help">Set percentage thresholds for budget alerts</p>
          </div>

          <!-- Status -->
          <div class="status-container">
            <div class="checkbox-group">
              <input id="budget-active" v-model="form.is_active" type="checkbox" class="modern-checkbox" />
              <label for="budget-active" class="checkbox-label">
                <span class="checkbox-text">Active Budget</span>
                <span class="checkbox-help">Budget is currently active and tracking expenses</span>
              </label>
            </div>
          </div>

          <!-- Form Actions -->
          <div class="form-actions">
            <button type="button" @click="$emit('close')" class="btn btn-secondary">Cancel</button>
            <button
              type="submit"
              :class="['colorful-budget-btn', isEditing ? 'update-budget-btn' : 'create-budget-btn']"
              :disabled="isSubmitting"
            >
              <i v-if="isSubmitting" class="fas fa-spinner fa-spin mr-2"></i>
              <i v-else :class="isEditing ? 'fas fa-edit' : 'fas fa-plus'" class="mr-2"></i>
              {{ isEditing ? 'Update Budget' : 'Create Budget' }}
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
  import { ref, reactive, computed, watch, onMounted, nextTick } from 'vue'
  import { useBudgetStore } from '@/stores/budget'
  import { useCategoriesStore } from '@/stores/categories'
  import { useToast } from '@/composables/useToast'
  import FormInput from '@/components/common/FormInput.vue'
  import FormSelect from '@/components/common/FormSelect.vue'
  import Button from '@/components/common/Button.vue'
  import debug from '@/utils/debug'

  const props = defineProps({
    budget: {
      type: Object,
      default: null
    },
    isEditing: {
      type: Boolean,
      default: false
    }
  })

  const emit = defineEmits(['close', 'saved'])

  // Stores
  const budgetStore = useBudgetStore()
  const categoryStore = useCategoriesStore()
  const toast = useToast()

  // Reactive data
  const isSubmitting = ref(false)
  const errors = ref({})

  // Transform categories for FormSelect component
  const categoryOptions = computed(() => {
    return categoryStore.categories.map(category => ({
      id: category.id,
      name: category.name,
      icon: category.icon || 'fas fa-tag'
    }))
  })

  // Period options for FormSelect
  const periodOptions = ref([
    { value: 'weekly', label: 'Weekly' },
    { value: 'monthly', label: 'Monthly' },
    { value: 'quarterly', label: 'Quarterly' },
    { value: 'yearly', label: 'Yearly' }
  ])

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

  // Helper function to format date for HTML date input (YYYY-MM-DD)
  const formatDateForInput = dateValue => {
    if (!dateValue) return ''

    try {
      // Handle different date formats
      let date

      if (typeof dateValue === 'string') {
        // Handle ISO string, timestamp, or already formatted date
        if (dateValue.includes('T')) {
          // ISO string like "2024-10-19T14:30:00.000Z"
          date = new Date(dateValue)
        } else if (dateValue.includes('-') && dateValue.length === 10) {
          // Already in YYYY-MM-DD format
          return dateValue
        } else {
          // Try parsing as general date string
          date = new Date(dateValue)
        }
      } else if (typeof dateValue === 'number') {
        // Unix timestamp
        date = new Date(dateValue * 1000)
      } else if (dateValue instanceof Date) {
        date = dateValue
      } else {
        return ''
      }

      // Validate the date
      if (isNaN(date.getTime())) {
        console.warn('Invalid date value:', dateValue)
        return ''
      }

      // Format as YYYY-MM-DD
      const year = date.getFullYear()
      const month = String(date.getMonth() + 1).padStart(2, '0')
      const day = String(date.getDate()).padStart(2, '0')

      return `${year}-${month}-${day}`
    } catch (error) {
      console.warn('Error formatting date:', dateValue, error)
      return ''
    }
  }

  // Initialize form data
  const initializeForm = () => {
    // Clear any existing errors first
    errors.value = {}

    if (props.isEditing && props.budget) {
      console.log('?? Initializing form for EDIT mode with budget:', props.budget)
      console.log('?? Budget object keys:', Object.keys(props.budget))
      console.log('?? Complete budget object structure:', JSON.stringify(props.budget, null, 2))

      // Handle budget editing - properly map budget properties
      form.name = props.budget.name || ''
      form.category_id = props.budget.category_id || props.budget.category?.id || ''

      // Handle amount - could be an object with raw/formatted properties
      if (typeof props.budget.amount === 'object' && props.budget.amount !== null) {
        form.amount = props.budget.amount.raw || props.budget.amount.value || 0
      } else {
        form.amount = props.budget.amount || ''
      }

      // Handle period - could be an object or string
      if (typeof props.budget.period === 'object' && props.budget.period !== null) {
        form.period = props.budget.period.type || props.budget.period.value || ''
      } else {
        form.period = props.budget.period || ''
      }

      // Handle dates - ensure they're in YYYY-MM-DD format for HTML date inputs
      // With the fixed normalization, dates should now be available
      console.log('??? Processing dates after normalization fix:', {
        root_start: props.budget.start_date,
        root_end: props.budget.end_date,
        period_object: props.budget.period_object,
        period_object_start: props.budget.period_object?.start_date,
        period_object_end: props.budget.period_object?.end_date,
        period_string: props.budget.period,
        all_budget_props: Object.keys(props.budget).filter(
          key => key.includes('date') || key.includes('start') || key.includes('end') || key.includes('period')
        )
      })

      let startDate = ''
      let endDate = ''

      // Try to find dates - now they should be at root level after normalization
      if (props.budget.start_date) {
        startDate = props.budget.start_date
        console.log('? Found start_date at root level:', startDate)
      } else if (props.budget.period_object?.start_date) {
        startDate = props.budget.period_object.start_date
        console.log('? Found start_date in period_object:', startDate)
      }

      if (props.budget.end_date) {
        endDate = props.budget.end_date
        console.log('? Found end_date at root level:', endDate)
      } else if (props.budget.period_object?.end_date) {
        endDate = props.budget.period_object.end_date
        console.log('? Found end_date in period_object:', endDate)
      }

      console.log('?? Extracted dates:', { startDate, endDate })

      form.start_date = formatDateForInput(startDate)
      form.end_date = formatDateForInput(endDate)

      console.log('? Form dates set:', {
        form_start_date: form.start_date,
        form_end_date: form.end_date,
        start_empty: !form.start_date,
        end_empty: !form.end_date
      })

      // Force DOM update to ensure input values are set
      nextTick(() => {
        const startInput = document.querySelector('input[type="date"][name="start_date"]')
        const endInput = document.querySelector('input[type="date"][name="end_date"]')

        if (startInput && form.start_date) {
          startInput.value = form.start_date
          console.log('?? Forced start date input value:', startInput.value)
        }

        if (endInput && form.end_date) {
          endInput.value = form.end_date
          console.log('?? Forced end date input value:', endInput.value)
        }
      })

      form.warning_threshold = props.budget.warning_threshold || 80
      form.critical_threshold = props.budget.critical_threshold || 100
      form.is_active = props.budget.is_active !== undefined ? props.budget.is_active : true
    } else {
      console.log('?? Initializing form for ADD mode')

      // Reset form for new budget
      form.name = ''
      form.category_id = ''
      form.amount = ''
      form.period = 'monthly'
      form.warning_threshold = 80
      form.critical_threshold = 100
      form.is_active = true

      // Set default dates for new budget
      const today = new Date()
      form.start_date = today.toISOString().split('T')[0]

      // Default to end of current month
      const endOfMonth = new Date(today.getFullYear(), today.getMonth() + 1, 0)
      form.end_date = endOfMonth.toISOString().split('T')[0]

      console.log('?? Default dates set for new budget:', {
        start_date: form.start_date,
        end_date: form.end_date
      })
    }

    // Final check
    console.log('? Final form state after initialization:', {
      name: form.name,
      category_id: form.category_id,
      amount: form.amount,
      period: form.period,
      start_date: form.start_date,
      end_date: form.end_date,
      dates_empty: !form.start_date || !form.end_date
    })
  }

  // Update end date based on period selection
  const updateDatesBasedOnPeriod = () => {
    if (!form.start_date || !form.period) return

    const startDate = new Date(form.start_date)
    const endDate = new Date(startDate)

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
    console.log('?? Starting form validation...')
    console.log('?? Current form values:', {
      name: form.name,
      category_id: form.category_id,
      amount: form.amount,
      period: form.period,
      start_date: form.start_date,
      end_date: form.end_date
    })

    errors.value = {}

    if (!form.name || !form.name.trim()) {
      errors.value.name = 'Budget name is required'
      console.log('? Name validation failed')
    }

    if (!form.category_id) {
      errors.value.category_id = 'Category is required'
      console.log('? Category validation failed')
    }

    if (!form.amount || form.amount <= 0) {
      errors.value.amount = 'Budget amount must be greater than 0'
      console.log('? Amount validation failed')
    }

    if (!form.period) {
      errors.value.period = 'Period is required'
      console.log('? Period validation failed')
    }

    if (!form.start_date || form.start_date.trim() === '') {
      errors.value.start_date = 'Start date is required'
      console.log('? Start date validation failed:', {
        value: form.start_date,
        type: typeof form.start_date,
        empty: !form.start_date,
        trimmed_empty: form.start_date && form.start_date.trim() === ''
      })
    }

    if (!form.end_date || form.end_date.trim() === '') {
      errors.value.end_date = 'End date is required'
      console.log('? End date validation failed:', {
        value: form.end_date,
        type: typeof form.end_date,
        empty: !form.end_date,
        trimmed_empty: form.end_date && form.end_date.trim() === ''
      })
    } else if (form.start_date && form.end_date < form.start_date) {
      errors.value.end_date = 'End date must be after start date'
      console.log('? End date must be after start date')
    }

    const isValid = Object.keys(errors.value).length === 0
    console.log('?? Validation result:', {
      isValid,
      errorCount: Object.keys(errors.value).length,
      errors: errors.value
    })

    return isValid
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
        toast.success('Budget updated successfully!', 'Success')
      } else {
        await budgetStore.createBudget(budgetData)
        toast.success('Budget created successfully!', 'Success')
      }

      emit('saved')
    } catch (error) {
      console.error('Failed to save budget:', error)

      // Handle validation errors from backend
      if (error.response?.data?.errors) {
        errors.value = error.response.data.errors
        toast.error('Please check the form for errors', 'Validation Error')
      } else {
        toast.error('Failed to save budget. Please try again.', 'Error')
      }
    } finally {
      isSubmitting.value = false
    }
  }

  // Handle overlay click
  const handleOverlayClick = event => {
    if (event.target === event.currentTarget) {
      emit('close')
    }
  }

  // Watch for start date changes to update end date
  watch(
    () => form.start_date,
    () => {
      if (form.period) {
        updateDatesBasedOnPeriod()
      }
    }
  )

  // Watch for budget prop changes (when switching between create/edit)
  watch(
    () => props.budget,
    (newBudget, oldBudget) => {
      console.log('?? Budget prop changed:', {
        newBudget,
        oldBudget,
        isEditing: props.isEditing,
        hasDateData: !!(newBudget?.period?.start_date && newBudget?.period?.end_date)
      })
      initializeForm()
    },
    { immediate: true, deep: true }
  )

  // Watch for isEditing prop changes
  watch(
    () => props.isEditing,
    (isEditing, wasEditing) => {
      console.log('?? isEditing changed:', { isEditing, wasEditing, hasBudget: !!props.budget })
      initializeForm()
    }
  )

  // Initialize
  onMounted(() => {
    initializeForm()

    // Load categories if not already loaded
    if (categoryStore.categories.length === 0) {
      categoryStore.fetchCategories()
    }
  })
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
    max-width: 600px;
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

  .budget-form {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
  }

  /* Section Titles */
  .section-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: #374151;
    margin: 0 0 1rem 0;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #e5e7eb;
  }

  .section-help {
    font-size: 0.875rem;
    color: #6b7280;
    margin-top: 0.5rem;
    font-style: italic;
  }

  /* Date Range Container */
  .date-range-container {
    background: #f8fafc;
    border-radius: 16px;
    padding: 1.5rem;
    border: 1px solid #e2e8f0;
  }

  .date-range-group {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
  }

  /* Thresholds Container */
  .thresholds-container {
    background: #fef7f0;
    border-radius: 16px;
    padding: 1.5rem;
    border: 1px solid #fed7aa;
  }

  .thresholds-group {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
  }

  .threshold-field {
    position: relative;
  }

  /* Status Container */
  .status-container {
    background: #f0fdf4;
    border-radius: 16px;
    padding: 1.5rem;
    border: 1px solid #bbf7d0;
  }

  /* Modern Checkbox */
  .checkbox-group {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
  }

  .modern-checkbox {
    width: 20px;
    height: 20px;
    border: 2px solid #d1d5db;
    border-radius: 6px;
    background: white;
    cursor: pointer;
    transition: all 0.3s ease;
    flex-shrink: 0;
    margin-top: 2px;
  }

  .modern-checkbox:checked {
    background: linear-gradient(135deg, #10b981, #059669);
    border-color: #10b981;
  }

  .modern-checkbox:checked::after {
    content: '?';
    color: white;
    font-size: 14px;
    font-weight: bold;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
  }

  .checkbox-label {
    cursor: pointer;
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
  }

  .checkbox-text {
    font-weight: 600;
    color: #374151;
    font-size: 1rem;
  }

  .checkbox-help {
    font-size: 0.875rem;
    color: #6b7280;
    line-height: 1.4;
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
  @media (max-width: 768px) {
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

    .date-range-group,
    .thresholds-group {
      grid-template-columns: 1fr;
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
  .budget-form :deep(.form-field) {
    transition: all 0.3s ease;
  }

  .budget-form :deep(.has-error) {
    animation: shake 0.5s ease-in-out;
  }

  @keyframes shake {
    0%,
    100% {
      transform: translateX(0);
    }
    25% {
      transform: translateX(-5px);
    }
    75% {
      transform: translateX(5px);
    }
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
    background: linear-gradient(45deg, #667eea 0%, #764ba2 20%, #f093fb 40%, #f5576c 60%, #4facfe 80%, #00f2fe 100%);
    background-size: 300% 300%;
    animation: rainbow-flow 3s ease infinite;
  }

  .create-budget-btn:not(:disabled):hover {
    background-size: 400% 400%;
    animation-duration: 1.5s;
    box-shadow: 0 12px 40px rgba(102, 126, 234, 0.5);
  }

  /* Update Budget Button - Vibrant Gradient */
  .update-budget-btn {
    background: linear-gradient(45deg, #f093fb 0%, #f5576c 25%, #4facfe 50%, #00f2fe 75%, #43e97b 100%);
    background-size: 300% 300%;
    animation: update-flow 3s ease infinite;
  }

  .update-budget-btn:not(:disabled):hover {
    background-size: 400% 400%;
    animation-duration: 1.5s;
    box-shadow: 0 12px 40px rgba(240, 147, 251, 0.5);
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
    0%,
    100% {
      background-position: 0% 50%;
    }
    50% {
      background-position: 100% 50%;
    }
  }

  @keyframes update-flow {
    0%,
    100% {
      background-position: 0% 50%;
    }
    50% {
      background-position: 100% 50%;
    }
  }

  @keyframes sparkle-twinkle {
    0%,
    100% {
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
