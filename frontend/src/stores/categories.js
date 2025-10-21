import { defineStore } from 'pinia'
import { ref } from 'vue'
import { categoryService } from '@/services/categoryService'

export const useCategoriesStore = defineStore('categories', () => {
  const categories = ref([])
  const loading = ref(false)
  const error = ref(null)

  const fetchCategories = async () => {
    loading.value = true
    error.value = null

    try {
      const response = await categoryService.getCategories()
      // API returns { success, message, data }
      categories.value = response.data?.data || response.data || []
    } catch (err) {
      console.error('Error fetching categories:', err)
      error.value = err.response?.data?.message || 'Failed to fetch categories'
      throw err
    } finally {
      loading.value = false
    }
  }

  const createCategory = async categoryData => {
    loading.value = true
    error.value = null

    try {
      const response = await categoryService.createCategory(categoryData)
      // API returns { success, message, data }
      if (response.data && response.data.data) {
        categories.value.push(response.data.data)
      }
      return response
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to create category'
      throw err
    } finally {
      loading.value = false
    }
  }

  const updateCategory = async (id, categoryData) => {
    loading.value = true
    error.value = null

    try {
      const response = await categoryService.updateCategory(id, categoryData)
      // API returns { success, message, data }
      if (response.data && response.data.data) {
        const index = categories.value.findIndex(category => category.id === id)
        if (index !== -1) {
          categories.value[index] = response.data.data
        }
      }
      return response
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to update category'
      throw err
    } finally {
      loading.value = false
    }
  }

  const deleteCategory = async id => {
    loading.value = true
    error.value = null

    try {
      await categoryService.deleteCategory(id)
      categories.value = categories.value.filter(category => category.id !== id)
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to delete category'
      throw err
    } finally {
      loading.value = false
    }
  }

  const getCategoryById = id => {
    return categories.value.find(category => category.id === id)
  }

  return {
    categories,
    loading,
    error,
    fetchCategories,
    createCategory,
    updateCategory,
    deleteCategory,
    getCategoryById
  }
})
