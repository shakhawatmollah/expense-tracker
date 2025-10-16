<template>
  <div class="space-y-6">
    <!-- Header with Add Button -->
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-lg font-medium text-gray-900">Categories</h2>
        <p class="text-sm text-gray-600">Organize your expenses by categories</p>
      </div>
      <Button @click="openAddModal" class="bg-indigo-600 hover:bg-indigo-700">
        <PlusIcon class="h-4 w-4 mr-2" />
        Add Category
      </Button>
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
      <Button @click="openAddModal" class="mt-4">
        Add Your First Category
      </Button>
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