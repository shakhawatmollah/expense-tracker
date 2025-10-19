<template>
  <div class="space-y-6">
    <!-- Header with Add Button -->
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-lg font-medium text-gray-900">Categories</h2>
        <p class="text-sm text-gray-600">Organize your expenses by categories</p>
      </div>
      <button @click="openAddModal" class="colorful-budget-btn create-category-btn">
        <PlusIcon class="h-4 w-4" />
        Add Category
        <div class="btn-sparkles">
          <div class="sparkle sparkle-1"></div>
          <div class="sparkle sparkle-2"></div>
          <div class="sparkle sparkle-3"></div>
        </div>
      </button>
    </div>

    <!-- Categories Grid -->
    <div v-if="loading && categories.length === 0" class="text-center py-8">
      <LoadingSpinner class="mx-auto h-8 w-8" />
      <p class="mt-2 text-sm text-gray-500">Loading categories...</p>
    </div>
    
    <div v-else-if="categories.length === 0" class="text-center py-8">
      <FolderIcon class="mx-auto h-12 w-12 text-gray-400" />
      <h3 class="mt-2 text-sm font-medium text-gray-900">No categories</h3>
      <p class="mt-1 text-sm text-gray-500">Get started by creating your first category.</p>
      <button @click="openAddModal" class="colorful-budget-btn create-category-btn mt-4">
        Add Your First Category
        <div class="btn-sparkles">
          <div class="sparkle sparkle-1"></div>
          <div class="sparkle sparkle-2"></div>
          <div class="sparkle sparkle-3"></div>
        </div>
      </button>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <div
        v-for="category in categories"
        :key="category.id"
        class="bg-white rounded-lg shadow border hover:shadow-md transition-shadow"
      >
        <div class="p-4">
          <div class="flex items-start justify-between">
            <div class="flex items-center space-x-3 flex-1">
              <div
                class="h-10 w-10 rounded-full flex items-center justify-center text-white text-sm font-medium"
                :style="{ backgroundColor: category.color || '#6B7280' }"
              >
                {{ category.name.charAt(0).toUpperCase() }}
              </div>
              <div class="flex-1 min-w-0">
                <h3 class="text-sm font-medium text-gray-900 truncate">
                  {{ category.name }}
                </h3>
                <p v-if="category.description" class="text-xs text-gray-500 truncate">
                  {{ category.description }}
                </p>
                <p class="text-xs text-gray-400 mt-1">
                  {{ category.expenses_count || 0 }} expenses
                </p>
              </div>
            </div>
            <div class="flex space-x-1">
              <button
                @click="openEditModal(category)"
                class="p-1 text-gray-400 hover:text-gray-600 rounded"
              >
                <PencilIcon class="h-4 w-4" />
              </button>
              <button
                @click="confirmDelete(category)"
                class="p-1 text-gray-400 hover:text-red-600 rounded"
                :class="{ 'opacity-50 cursor-not-allowed': deletingId === category.id }"
                :disabled="deletingId === category.id"
              >
                <TrashIcon class="h-4 w-4" />
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Category Form Modal -->
    <CategoryForm
      :is-open="showForm"
      :category="selectedCategory"
      @close="closeModal"
      @saved="handleCategorySaved"
    />

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteConfirm" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click.self="closeDeleteConfirm">
      <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
          <ExclamationTriangleIcon class="mx-auto h-12 w-12 text-red-500" />
          <h3 class="text-lg font-medium text-gray-900 mt-2">Delete Category</h3>
          <p class="text-sm text-gray-500 mt-2">
            Are you sure you want to delete "{{ categoryToDelete?.name }}"? 
            <span v-if="categoryToDelete?.expenses_count > 0" class="text-red-600 font-medium">
              This category has {{ categoryToDelete.expenses_count }} expenses.
            </span>
            This action cannot be undone.
          </p>
          <div class="flex justify-center space-x-3 mt-6">
            <Button variant="outline" @click="closeDeleteConfirm">Cancel</Button>
            <Button variant="danger" @click="deleteCategory" :loading="deletingId === categoryToDelete?.id">
              Delete
            </Button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { 
  FolderIcon, 
  PlusIcon, 
  PencilIcon, 
  TrashIcon,
  ExclamationTriangleIcon 
} from '@heroicons/vue/24/outline'
import { useCategoriesStore } from '@/stores/categories'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'
import Button from '@/components/common/Button.vue'
import CategoryForm from './CategoryForm.vue'

const categoriesStore = useCategoriesStore()

// Reactive data
const showForm = ref(false)
const selectedCategory = ref(null)
const showDeleteConfirm = ref(false)
const categoryToDelete = ref(null)
const deletingId = ref(null)

// Computed properties
const categories = computed(() => categoriesStore.categories)
const loading = computed(() => categoriesStore.loading)

// Methods
const openAddModal = () => {
  selectedCategory.value = null
  showForm.value = true
}

const openEditModal = (category) => {
  selectedCategory.value = { ...category }
  showForm.value = true
}

const closeModal = () => {
  showForm.value = false
  selectedCategory.value = null
}

const handleCategorySaved = () => {
  closeModal()
  categoriesStore.fetchCategories()
}

const confirmDelete = (category) => {
  categoryToDelete.value = category
  showDeleteConfirm.value = true
}

const closeDeleteConfirm = () => {
  showDeleteConfirm.value = false
  categoryToDelete.value = null
}

const deleteCategory = async () => {
  if (!categoryToDelete.value) return

  try {
    deletingId.value = categoryToDelete.value.id
    await categoriesStore.deleteCategory(categoryToDelete.value.id)
    closeDeleteConfirm()
  } catch (error) {
    console.error('Failed to delete category:', error)
  } finally {
    deletingId.value = null
  }
}

// Lifecycle
onMounted(() => {
  categoriesStore.fetchCategories()
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

/* Create Category Button - Purple/Pink Gradient */
.create-category-btn {
  background: linear-gradient(45deg, 
    #8b5cf6 0%, 
    #a855f7 25%, 
    #d946ef 50%, 
    #ec4899 75%, 
    #f43f5e 100%);
  background-size: 300% 300%;
  animation: category-flow 3s ease infinite;
}

.create-category-btn:not(:disabled):hover {
  background-size: 400% 400%;
  animation-duration: 1.5s;
  box-shadow: 0 12px 40px rgba(139, 92, 246, 0.5);
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