<template>
  <div class="export-modal">
    <div class="modal-overlay" @click="$emit('close')"></div>
    <div class="modal-content">
      <div class="modal-header">
        <h2>Export Data</h2>
        <button class="close-btn" @click="$emit('close')">
          <span>×</span>
        </button>
      </div>

      <div class="modal-body">
        <!-- Export Type Selection -->
        <div class="form-group">
          <label>Export Type</label>
          <select v-model="exportType" class="form-control">
            <option value="expenses">Expenses</option>
            <option value="categories">Categories</option>
            <option value="budgets">Budgets</option>
            <option value="financial-report">Financial Report</option>
          </select>
        </div>

        <!-- Format Selection -->
        <div class="form-group">
          <label>Format</label>
          <div class="format-options">
            <label class="format-option">
              <input type="radio" v-model="format" value="csv" />
              <span>CSV</span>
            </label>
            <label class="format-option" v-if="exportType === 'financial-report'">
              <input type="radio" v-model="format" value="pdf" />
              <span>PDF</span>
            </label>
            <label class="format-option">
              <input type="radio" v-model="format" value="xlsx" />
              <span>Excel</span>
            </label>
          </div>
        </div>

        <!-- Date Range (for expenses and financial report) -->
        <div v-if="exportType === 'expenses' || exportType === 'financial-report'" class="date-range">
          <div class="form-group">
            <label>Start Date</label>
            <input type="date" v-model="startDate" class="form-control" />
          </div>
          <div class="form-group">
            <label>End Date</label>
            <input type="date" v-model="endDate" class="form-control" />
          </div>
        </div>

        <!-- Category Filter (for expenses) -->
        <div v-if="exportType === 'expenses'" class="form-group">
          <label>Category (Optional)</label>
          <select v-model="categoryId" class="form-control">
            <option :value="null">All Categories</option>
            <option v-for="category in categories" :key="category.id" :value="category.id">
              {{ category.name }}
            </option>
          </select>
        </div>

        <!-- Period Filter (for budgets) -->
        <div v-if="exportType === 'budgets'" class="form-group">
          <label>Period (Optional)</label>
          <select v-model="period" class="form-control">
            <option :value="null">All Periods</option>
            <option value="weekly">Weekly</option>
            <option value="monthly">Monthly</option>
            <option value="yearly">Yearly</option>
            <option value="custom">Custom</option>
          </select>
        </div>

        <!-- Error Message -->
        <div v-if="error" class="error-message">
          {{ error }}
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-secondary" @click="$emit('close')" :disabled="loading">
          Cancel
        </button>
        <button class="btn btn-primary" @click="handleExport" :disabled="loading">
          <span v-if="loading">Exporting...</span>
          <span v-else>Export</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useCategoriesStore } from '@/stores/categories'
import api from '@/services/api'

const emit = defineEmits(['close', 'success'])

const categoriesStore = useCategoriesStore()

const exportType = ref('expenses')
const format = ref('csv')
const startDate = ref('')
const endDate = ref('')
const categoryId = ref(null)
const period = ref(null)
const loading = ref(false)
const error = ref('')

const categories = computed(() => categoriesStore.categories)

onMounted(async () => {
  // Set default date range (current month)
  const now = new Date()
  const firstDay = new Date(now.getFullYear(), now.getMonth(), 1)
  const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0)
  
  startDate.value = firstDay.toISOString().split('T')[0]
  endDate.value = lastDay.toISOString().split('T')[0]

  // Fetch categories if not already loaded
  if (categories.value.length === 0) {
    await categoriesStore.fetchCategories()
  }
})

const handleExport = async () => {
  error.value = ''
  loading.value = true

  try {
    const params = new URLSearchParams()
    params.append('format', format.value)

    if (exportType.value === 'expenses' || exportType.value === 'financial-report') {
      if (startDate.value) params.append('start_date', startDate.value)
      if (endDate.value) params.append('end_date', endDate.value)
    }

    if (exportType.value === 'expenses' && categoryId.value) {
      params.append('category_id', categoryId.value)
    }

    if (exportType.value === 'budgets' && period.value) {
      params.append('period', period.value)
    }

    if (exportType.value === 'financial-report') {
      params.append('include_charts', 'true')
    }

    const endpoint = `/export/${exportType.value}?${params.toString()}`

    // Make request to download file
    const response = await api.get(endpoint, {
      responseType: 'blob'
    })

    // Create download link
    const url = window.URL.createObjectURL(new Blob([response.data]))
    const link = document.createElement('a')
    link.href = url

    // Get filename from response headers or generate one
    const contentDisposition = response.headers['content-disposition']
    let filename = `export_${exportType.value}_${new Date().getTime()}.${format.value}`
    
    if (contentDisposition) {
      const filenameMatch = contentDisposition.match(/filename="?(.+)"?/i)
      if (filenameMatch && filenameMatch[1]) {
        filename = filenameMatch[1]
      }
    }

    link.setAttribute('download', filename)
    document.body.appendChild(link)
    link.click()
    link.remove()
    window.URL.revokeObjectURL(url)

    emit('success', 'Export completed successfully!')
    emit('close')
  } catch (err) {
    console.error('Export error:', err)
    error.value = err.response?.data?.message || 'Failed to export data. Please try again.'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.export-modal {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
}

.modal-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(4px);
}

.modal-content {
  position: relative;
  background: white;
  border-radius: 12px;
  width: 90%;
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 24px;
  border-bottom: 1px solid #e5e7eb;
}

.modal-header h2 {
  margin: 0;
  font-size: 24px;
  font-weight: 600;
  color: #111827;
}

.close-btn {
  background: none;
  border: none;
  font-size: 32px;
  color: #6b7280;
  cursor: pointer;
  padding: 0;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 6px;
  transition: all 0.2s;
}

.close-btn:hover {
  background: #f3f4f6;
  color: #111827;
}

.modal-body {
  padding: 24px;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: 500;
  color: #374151;
  font-size: 14px;
}

.form-control {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 14px;
  transition: all 0.2s;
}

.form-control:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.format-options {
  display: flex;
  gap: 12px;
}

.format-option {
  flex: 1;
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
}

.format-option:hover {
  border-color: #3b82f6;
  background: #eff6ff;
}

.format-option input[type="radio"] {
  margin: 0;
}

.format-option input[type="radio"]:checked + span {
  color: #3b82f6;
  font-weight: 600;
}

.date-range {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

.error-message {
  padding: 12px;
  background: #fee2e2;
  border: 1px solid #fecaca;
  border-radius: 6px;
  color: #dc2626;
  font-size: 14px;
  margin-top: 16px;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  padding: 24px;
  border-top: 1px solid #e5e7eb;
}

.btn {
  padding: 10px 20px;
  border-radius: 6px;
  font-weight: 500;
  font-size: 14px;
  cursor: pointer;
  transition: all 0.2s;
  border: none;
}

.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-secondary {
  background: #f3f4f6;
  color: #374151;
}

.btn-secondary:hover:not(:disabled) {
  background: #e5e7eb;
}

.btn-primary {
  background: #3b82f6;
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background: #2563eb;
}

@media (max-width: 768px) {
  .modal-content {
    width: 95%;
    max-width: none;
  }

  .date-range {
    grid-template-columns: 1fr;
  }

  .format-options {
    flex-direction: column;
  }
}
</style>
