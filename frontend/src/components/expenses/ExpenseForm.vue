<template>
  <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" v-if="isOpen" @click.self="closeModal">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
      <div class="mt-3">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-medium text-gray-900">{{ isEditing ? 'Edit Expense' : 'Add New Expense' }}</h3>
          <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
            <XMarkIcon class="h-6 w-6" />
          </button>
        </div>
        
        <form @submit.prevent="submitForm" class="space-y-4">
          <div>
            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
            <input
              type="text"
              id="description"
              v-model="form.description"
              required
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
              placeholder="Enter expense description"
            />
            <p v-if="errors.description" class="mt-1 text-sm text-red-600">{{ errors.description[0] }}</p>
          </div>
          
          <div>
            <label for="amount" class="block text-sm font-medium text-gray-700">Amount</label>
            <div class="mt-1 relative rounded-md shadow-sm">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <span class="text-gray-500 sm:text-sm">$</span>
              </div>
              <input
                type="number"
                step="0.01"
                id="amount"
                v-model="form.amount"
                required
                class="block w-full pl-7 pr-12 border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                placeholder="0.00"
              />
            </div>
            <p v-if="errors.amount" class="mt-1 text-sm text-red-600">{{ errors.amount[0] }}</p>
          </div>
          
          <div>
            <label for="date" class="block text-sm font-medium text-gray-700">Date</label>
            <input
              type="date"
              id="date"
              v-model="form.date"
              required
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
            />
            <p v-if="errors.date" class="mt-1 text-sm text-red-600">{{ errors.date[0] }}</p>
          </div>
          
          <div>
            <label for="category_id" class="block text-sm font-medium text-gray-700">Category</label>
            <select
              id="category_id"
              v-model="form.category_id"
              required
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
            >
              <option value="">Select a category</option>
              <option v-for="category in categories" :key="category.id" :value="category.id">
                {{ category.name }}
              </option>
            </select>
            <p v-if="errors.category_id" class="mt-1 text-sm text-red-600">{{ errors.category_id[0] }}</p>
          </div>
          
          <div class="flex justify-end space-x-3 pt-4">
            <Button type="button" variant="outline" @click="closeModal">
              Cancel
            </Button>
            <Button type="submit" :loading="loading">
              {{ isEditing ? 'Update' : 'Create' }} Expense
            </Button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue'
import { XMarkIcon } from '@heroicons/vue/24/outline'
import { useExpensesStore } from '@/stores/expenses'
import { useCategoriesStore } from '@/stores/categories'
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
    form.amount = newExpense.amount
    form.date = newExpense.date
    form.category_id = newExpense.category_id
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
    
    const formData = {
      description: form.description,
      amount: parseFloat(form.amount),
      date: form.date,
      category_id: parseInt(form.category_id)
    }
    
    if (isEditing.value) {
      await expensesStore.updateExpense(props.expense.id, formData)
    } else {
      await expensesStore.createExpense(formData)
    }
    
    emit('saved')
    closeModal()
  } catch (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    } else {
      console.error('Error saving expense:', error)
    }
  } finally {
    loading.value = false
  }
}
</script>