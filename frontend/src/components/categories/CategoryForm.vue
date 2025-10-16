<template>
  <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" v-if="isOpen" @click.self="closeModal">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
      <div class="mt-3">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-medium text-gray-900">{{ isEditing ? 'Edit Category' : 'Add New Category' }}</h3>
          <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
            <XMarkIcon class="h-6 w-6" />
          </button>
        </div>
        
        <form @submit.prevent="submitForm" class="space-y-4">
          <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
            <input
              type="text"
              id="name"
              v-model="form.name"
              required
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
              placeholder="Enter category name"
            />
            <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name[0] }}</p>
          </div>
          
          <div>
            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
            <textarea
              id="description"
              v-model="form.description"
              rows="3"
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
              placeholder="Optional description"
            ></textarea>
            <p v-if="errors.description" class="mt-1 text-sm text-red-600">{{ errors.description[0] }}</p>
          </div>
          
          <div>
            <label for="color" class="block text-sm font-medium text-gray-700">Color</label>
            <div class="mt-1 flex items-center space-x-3">
              <input
                type="color"
                id="color"
                v-model="form.color"
                class="h-10 w-16 border border-gray-300 rounded cursor-pointer"
              />
              <div class="flex items-center space-x-2">
                <div
                  class="h-8 w-8 rounded-full border-2 border-gray-300"
                  :style="{ backgroundColor: form.color }"
                ></div>
                <span class="text-sm text-gray-600">{{ form.color }}</span>
              </div>
            </div>
            <p v-if="errors.color" class="mt-1 text-sm text-red-600">{{ errors.color[0] }}</p>
          </div>

          <!-- Color Presets -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Quick Colors</label>
            <div class="grid grid-cols-8 gap-2">
              <button
                v-for="color in colorPresets"
                :key="color"
                type="button"
                @click="form.color = color"
                class="h-8 w-8 rounded-full border-2 hover:scale-110 transition-transform"
                :class="form.color === color ? 'border-gray-800' : 'border-gray-300'"
                :style="{ backgroundColor: color }"
              ></button>
            </div>
          </div>
          
          <div class="flex justify-end space-x-3 mt-6">
            <Button type="button" variant="outline" @click="closeModal">Cancel</Button>
            <Button type="submit" :loading="loading">
              {{ isEditing ? 'Update' : 'Create' }} Category
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
import { useCategoriesStore } from '@/stores/categories'
import Button from '@/components/common/Button.vue'

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  },
  category: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close', 'saved'])

const categoriesStore = useCategoriesStore()

const loading = ref(false)
const errors = ref({})

const form = reactive({
  name: '',
  description: '',
  color: '#6B7280'
})

const isEditing = computed(() => !!props.category)

// Color presets for quick selection
const colorPresets = [
  '#EF4444', // Red
  '#F97316', // Orange
  '#F59E0B', // Amber
  '#EAB308', // Yellow
  '#22C55E', // Green
  '#10B981', // Emerald
  '#06B6D4', // Cyan
  '#3B82F6', // Blue
  '#6366F1', // Indigo
  '#8B5CF6', // Violet
  '#A855F7', // Purple
  '#EC4899', // Pink
  '#F43F5E', // Rose
  '#6B7280', // Gray
  '#374151', // Dark Gray
  '#1F2937'  // Almost Black
]

// Define resetForm function before it's used
const resetForm = () => {
  form.name = ''
  form.description = ''
  form.color = '#6B7280'
}

// Watch for category prop changes (for editing)
watch(() => props.category, (newCategory) => {
  if (newCategory) {
    form.name = newCategory.name
    form.description = newCategory.description || ''
    form.color = newCategory.color || '#6B7280'
  } else {
    resetForm()
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
      name: form.name,
      description: form.description,
      color: form.color
    }
    
    if (isEditing.value) {
      await categoriesStore.updateCategory(props.category.id, formData)
    } else {
      await categoriesStore.createCategory(formData)
    }
    emit('saved')
    closeModal()
  } catch (error) {
    console.error('Error saving category:', error)
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
      console.log('Validation errors:', errors.value)
    } else {
      console.error('Unexpected error:', error)
    }
  } finally {
    loading.value = false
  }
}
</script>