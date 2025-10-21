<template>
  <div
    class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
    v-if="isOpen"
    @click.self="closeModal"
  >
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
            <button
              type="submit"
              :disabled="loading"
              :class="['colorful-budget-btn', isEditing ? 'update-category-btn' : 'create-category-btn']"
            >
              {{ isEditing ? 'Update' : 'Create' }} Category
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
  import { XMarkIcon } from '@heroicons/vue/24/outline'
  import { useCategoriesStore } from '@/stores/categories'
  import { useToast } from '@/composables/useToast'
  import Button from '@/components/common/Button.vue'
  import debug from '@/utils/debug'

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
  const toast = useToast()

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
    '#1F2937' // Almost Black
  ]

  // Define resetForm function before it's used
  const resetForm = () => {
    form.name = ''
    form.description = ''
    form.color = '#6B7280'
  }

  // Watch for category prop changes (for editing)
  watch(
    () => props.category,
    newCategory => {
      if (newCategory) {
        form.name = newCategory.name
        form.description = newCategory.description || ''
        form.color = newCategory.color || '#6B7280'
      } else {
        resetForm()
      }
      errors.value = {}
    },
    { immediate: true }
  )

  const closeModal = () => {
    resetForm()
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
        toast.success('Category updated successfully!', 'Success')
      } else {
        await categoriesStore.createCategory(formData)
        toast.success('Category created successfully!', 'Success')
      }
      emit('saved')
      closeModal()
    } catch (error) {
      console.error('Error saving category:', error)
      if (error.response?.data?.errors) {
        errors.value = error.response.data.errors
        console.log('Validation errors:', errors.value)
        toast.error('Please check the form for errors', 'Validation Error')
      } else {
        console.error('Unexpected error:', error)
        toast.error('Failed to save category. Please try again.', 'Error')
      }
    } finally {
      loading.value = false
    }
  }
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

  /* Create Category Button - Purple/Pink Gradient */
  .create-category-btn {
    background: linear-gradient(45deg, #8b5cf6 0%, #a855f7 25%, #d946ef 50%, #ec4899 75%, #f43f5e 100%);
    background-size: 300% 300%;
    animation: category-flow 3s ease infinite;
  }

  .create-category-btn:not(:disabled):hover {
    background-size: 400% 400%;
    animation-duration: 1.5s;
    box-shadow: 0 12px 40px rgba(139, 92, 246, 0.5);
  }

  /* Update Category Button - Teal/Indigo Gradient */
  .update-category-btn {
    background: linear-gradient(45deg, #06b6d4 0%, #0891b2 25%, #3b82f6 50%, #6366f1 75%, #8b5cf6 100%);
    background-size: 300% 300%;
    animation: update-category-flow 3s ease infinite;
  }

  .update-category-btn:not(:disabled):hover {
    background-size: 400% 400%;
    animation-duration: 1.5s;
    box-shadow: 0 12px 40px rgba(6, 182, 212, 0.5);
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
  @keyframes category-flow {
    0%,
    100% {
      background-position: 0% 50%;
    }
    50% {
      background-position: 100% 50%;
    }
  }

  @keyframes update-category-flow {
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
