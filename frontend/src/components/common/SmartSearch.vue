<template>
  <div class="smart-search" :class="{ 'active': isActive, 'mobile': isMobile }">
    <!-- Search Trigger Button -->
    <button 
      v-if="!isActive"
      @click="activateSearch"
      class="search-trigger"
      title="Search expenses, categories, budgets (Ctrl+/)"
    >
      <i class="fas fa-search"></i>
      <span v-if="!isMobile">Search</span>
    </button>

    <!-- Search Interface -->
    <div v-if="isActive" class="search-interface">
      <!-- Search Input -->
      <div class="search-input-container">
        <i class="fas fa-search search-icon"></i>
        <input
          ref="searchInput"
          v-model="searchQuery"
          @input="handleSearch"
          @keydown="handleKeyDown"
          class="search-input"
          placeholder="Search expenses, categories, budgets..."
          autocomplete="off"
        />
        <button @click="clearSearch" class="clear-btn" v-if="searchQuery">
          <i class="fas fa-times"></i>
        </button>
        <button @click="deactivateSearch" class="close-btn">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <!-- Search Filters -->
      <div class="search-filters" v-if="searchQuery">
        <button 
          v-for="filter in searchFilters"
          :key="filter.value"
          :class="['filter-btn', { active: activeFilter === filter.value }]"
          @click="setActiveFilter(filter.value)"
        >
          <i :class="filter.icon"></i>
          <span>{{ filter.label }}</span>
          <span class="filter-count">{{ getFilterCount(filter.value) }}</span>
        </button>
      </div>

      <!-- Search Results -->
      <div class="search-results" v-if="searchQuery && searchResults.length > 0">
        <div class="results-header">
          <span class="results-count">{{ filteredResults.length }} results</span>
          <div class="results-actions">
            <button @click="exportResults" class="action-btn">
              <i class="fas fa-download"></i>
              Export
            </button>
          </div>
        </div>

        <div class="results-list">
          <div 
            v-for="(result, index) in paginatedResults"
            :key="result.id"
            :class="['result-item', result.type, { selected: selectedIndex === index }]"
            @click="selectResult(result)"
            @mouseenter="selectedIndex = index"
          >
            <div class="result-icon">
              <i :class="getResultIcon(result.type)"></i>
            </div>
            
            <div class="result-content">
              <div class="result-title">{{ result.title }}</div>
              <div class="result-subtitle">{{ result.subtitle }}</div>
              <div class="result-meta">
                <span class="result-type">{{ result.type }}</span>
                <span class="result-date" v-if="result.date">{{ formatDate(result.date) }}</span>
                <span class="result-amount" v-if="result.amount">{{ formatCurrency(result.amount) }}</span>
              </div>
            </div>

            <div class="result-actions">
              <button @click.stop="quickEdit(result)" class="quick-action" title="Quick edit">
                <i class="fas fa-edit"></i>
              </button>
              <button @click.stop="viewDetails(result)" class="quick-action" title="View details">
                <i class="fas fa-eye"></i>
              </button>
            </div>
          </div>
        </div>

        <!-- Load More -->
        <div class="load-more" v-if="hasMoreResults">
          <button @click="loadMoreResults" class="load-more-btn">
            <i class="fas fa-chevron-down"></i>
            Show {{ Math.min(10, searchResults.length - currentPage * pageSize) }} more results
          </button>
        </div>
      </div>

      <!-- No Results -->
      <div class="no-results" v-else-if="searchQuery && searchResults.length === 0">
        <div class="no-results-icon">
          <i class="fas fa-search"></i>
        </div>
        <div class="no-results-title">No results found</div>
        <div class="no-results-subtitle">
          Try adjusting your search or <button @click="showAdvancedSearch" class="link-btn">use advanced search</button>
        </div>
      </div>

      <!-- Search Suggestions -->
      <div class="search-suggestions" v-else-if="!searchQuery">
        <div class="suggestions-header">Recent searches</div>
        <div class="suggestions-list">
          <button 
            v-for="suggestion in recentSearches"
            :key="suggestion"
            @click="applySuggestion(suggestion)"
            class="suggestion-item"
          >
            <i class="fas fa-history"></i>
            <span>{{ suggestion }}</span>
          </button>
        </div>
        
        <div class="suggestions-header">Quick actions</div>
        <div class="suggestions-list">
          <button 
            v-for="action in quickActions"
            :key="action.value"
            @click="performQuickAction(action.value)"
            class="suggestion-item action"
          >
            <i :class="action.icon"></i>
            <span>{{ action.label }}</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Search Overlay -->
    <div 
      v-if="isActive" 
      @click="deactivateSearch"
      class="search-overlay"
    ></div>
  </div>
</template>

<script>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue'

export default {
  name: 'SmartSearch',
  emits: ['select-result', 'quick-action'],
  setup(props, { emit }) {
    // Reactive state
    const isActive = ref(false)
    const isMobile = ref(false)
    const searchQuery = ref('')
    const selectedIndex = ref(0)
    const activeFilter = ref('all')
    const currentPage = ref(1)
    const pageSize = 10
    
    // Refs
    const searchInput = ref(null)

    // Mock data - replace with actual store data
    const searchResults = ref([])
    const recentSearches = ref(['Coffee expenses', 'Food budget', 'Transportation'])
    
    const searchFilters = [
      { value: 'all', label: 'All', icon: 'fas fa-globe' },
      { value: 'expenses', label: 'Expenses', icon: 'fas fa-receipt' },
      { value: 'categories', label: 'Categories', icon: 'fas fa-tag' },
      { value: 'budgets', label: 'Budgets', icon: 'fas fa-bullseye' }
    ]

    const quickActions = [
      { value: 'add-expense', label: 'Add new expense', icon: 'fas fa-plus' },
      { value: 'view-analytics', label: 'View analytics', icon: 'fas fa-chart-line' },
      { value: 'export-data', label: 'Export data', icon: 'fas fa-download' }
    ]

    // Computed properties
    const filteredResults = computed(() => {
      if (activeFilter.value === 'all') return searchResults.value
      return searchResults.value.filter(result => result.type === activeFilter.value)
    })

    const paginatedResults = computed(() => {
      const start = 0
      const end = currentPage.value * pageSize
      return filteredResults.value.slice(start, end)
    })

    const hasMoreResults = computed(() => {
      return filteredResults.value.length > currentPage.value * pageSize
    })

    // Mobile detection
    const detectMobile = () => {
      isMobile.value = window.innerWidth <= 768
    }

    // Search activation
    const activateSearch = async () => {
      isActive.value = true
      await nextTick()
      searchInput.value?.focus()
    }

    const deactivateSearch = () => {
      isActive.value = false
      searchQuery.value = ''
      selectedIndex.value = 0
      currentPage.value = 1
      activeFilter.value = 'all'
    }

    // Search handling
    const handleSearch = (event) => {
      const query = event.target.value
      performSearch(query)
    }

    const performSearch = (query) => {
      if (!query.trim()) {
        searchResults.value = []
        return
      }

      // Mock search results - replace with actual search logic
      const mockResults = [
        {
          id: 1,
          type: 'expenses',
          title: 'Coffee at Starbucks',
          subtitle: 'Food & Dining',
          amount: 5.50,
          date: new Date('2024-01-15')
        },
        {
          id: 2,
          type: 'categories',
          title: 'Food & Dining',
          subtitle: '45 expenses, $324.50 total',
          amount: 324.50
        },
        {
          id: 3,
          type: 'budgets',
          title: 'Monthly Food Budget',
          subtitle: '$156.30 remaining of $500.00',
          amount: 500.00
        }
      ].filter(item => 
        item.title.toLowerCase().includes(query.toLowerCase()) ||
        item.subtitle.toLowerCase().includes(query.toLowerCase())
      )

      searchResults.value = mockResults
      selectedIndex.value = 0
      currentPage.value = 1
    }

    // Keyboard navigation
    const handleKeyDown = (event) => {
      switch (event.key) {
        case 'ArrowDown':
          event.preventDefault()
          selectedIndex.value = Math.min(selectedIndex.value + 1, paginatedResults.value.length - 1)
          break
        case 'ArrowUp':
          event.preventDefault()
          selectedIndex.value = Math.max(selectedIndex.value - 1, 0)
          break
        case 'Enter':
          event.preventDefault()
          if (paginatedResults.value[selectedIndex.value]) {
            selectResult(paginatedResults.value[selectedIndex.value])
          }
          break
        case 'Escape':
          deactivateSearch()
          break
      }
    }

    // Global keyboard shortcuts
    const handleGlobalKeyDown = (event) => {
      if ((event.ctrlKey || event.metaKey) && event.key === '/') {
        event.preventDefault()
        activateSearch()
      }
    }

    // Filter handling
    const setActiveFilter = (filter) => {
      activeFilter.value = filter
      selectedIndex.value = 0
      currentPage.value = 1
    }

    const getFilterCount = (filter) => {
      if (filter === 'all') return searchResults.value.length
      return searchResults.value.filter(result => result.type === filter).length
    }

    // Result handling
    const selectResult = (result) => {
      emit('select-result', result)
      addToRecentSearches(searchQuery.value)
      deactivateSearch()
    }

    const quickEdit = (result) => {
      emit('select-result', { ...result, action: 'edit' })
    }

    const viewDetails = (result) => {
      emit('select-result', { ...result, action: 'view' })
    }

    // Utility functions
    const clearSearch = () => {
      searchQuery.value = ''
      searchResults.value = []
      selectedIndex.value = 0
    }

    const loadMoreResults = () => {
      currentPage.value++
    }

    const exportResults = () => {
      emit('quick-action', 'export-search-results')
    }

    const showAdvancedSearch = () => {
      emit('quick-action', 'advanced-search')
    }

    const applySuggestion = (suggestion) => {
      searchQuery.value = suggestion
      performSearch(suggestion)
    }

    const performQuickAction = (action) => {
      emit('quick-action', action)
      deactivateSearch()
    }

    const addToRecentSearches = (query) => {
      if (query && !recentSearches.value.includes(query)) {
        recentSearches.value.unshift(query)
        recentSearches.value = recentSearches.value.slice(0, 5)
      }
    }

    const getResultIcon = (type) => {
      const icons = {
        expenses: 'fas fa-receipt',
        categories: 'fas fa-tag',
        budgets: 'fas fa-bullseye'
      }
      return icons[type] || 'fas fa-file'
    }

    const formatDate = (date) => {
      return new Intl.DateTimeFormat('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
      }).format(date)
    }

    const formatCurrency = (amount) => {
      return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
      }).format(amount)
    }

    // Lifecycle
    onMounted(() => {
      detectMobile()
      document.addEventListener('keydown', handleGlobalKeyDown)
      window.addEventListener('resize', detectMobile)
    })

    onUnmounted(() => {
      document.removeEventListener('keydown', handleGlobalKeyDown)
      window.removeEventListener('resize', detectMobile)
    })

    return {
      // State
      isActive,
      isMobile,
      searchQuery,
      selectedIndex,
      activeFilter,
      currentPage,
      searchInput,
      
      // Data
      searchResults,
      recentSearches,
      searchFilters,
      quickActions,
      
      // Computed
      filteredResults,
      paginatedResults,
      hasMoreResults,
      
      // Methods
      activateSearch,
      deactivateSearch,
      handleSearch,
      handleKeyDown,
      setActiveFilter,
      getFilterCount,
      selectResult,
      quickEdit,
      viewDetails,
      clearSearch,
      loadMoreResults,
      exportResults,
      showAdvancedSearch,
      applySuggestion,
      performQuickAction,
      getResultIcon,
      formatDate,
      formatCurrency
    }
  }
}
</script>

<style scoped>
.smart-search {
  position: relative;
  z-index: 999;
}

/* Search Trigger */
.search-trigger {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1rem;
  background: rgba(255, 255, 255, 0.9);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.3);
  border-radius: 12px;
  color: #6b7280;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
}

.search-trigger:hover {
  background: rgba(255, 255, 255, 1);
  color: #374151;
  transform: translateY(-1px);
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
}

/* Search Interface */
.search-interface {
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 90vw;
  max-width: 600px;
  max-height: 80vh;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 20px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
  overflow: hidden;
  animation: searchSlideIn 0.3s ease-out;
  z-index: 1001;
}

@keyframes searchSlideIn {
  from {
    opacity: 0;
    transform: translate(-50%, -60%);
  }
  to {
    opacity: 1;
    transform: translate(-50%, -50%);
  }
}

/* Search Input */
.search-input-container {
  position: relative;
  display: flex;
  align-items: center;
  padding: 1rem 1.5rem;
  border-bottom: 1px solid rgba(0, 0, 0, 0.1);
}

.search-icon {
  color: #9ca3af;
  margin-right: 0.75rem;
  font-size: 1.125rem;
}

.search-input {
  flex: 1;
  border: none;
  outline: none;
  background: transparent;
  font-size: 1.125rem;
  color: #1f2937;
}

.search-input::placeholder {
  color: #9ca3af;
}

.clear-btn,
.close-btn {
  width: 32px;
  height: 32px;
  border: none;
  border-radius: 8px;
  background: transparent;
  color: #6b7280;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-left: 0.5rem;
}

.clear-btn:hover,
.close-btn:hover {
  background: rgba(0, 0, 0, 0.1);
  color: #374151;
}

/* Search Filters */
.search-filters {
  display: flex;
  gap: 0.5rem;
  padding: 1rem 1.5rem;
  border-bottom: 1px solid rgba(0, 0, 0, 0.1);
  overflow-x: auto;
}

.filter-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 0.875rem;
  border: 1px solid #e5e7eb;
  border-radius: 20px;
  background: white;
  color: #6b7280;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  white-space: nowrap;
}

.filter-btn.active {
  background: #667eea;
  border-color: #667eea;
  color: white;
}

.filter-btn:hover:not(.active) {
  background: #f3f4f6;
  border-color: #d1d5db;
}

.filter-count {
  background: rgba(0, 0, 0, 0.1);
  color: inherit;
  padding: 0.125rem 0.375rem;
  border-radius: 10px;
  font-size: 0.75rem;
  font-weight: 600;
}

.filter-btn.active .filter-count {
  background: rgba(255, 255, 255, 0.2);
}

/* Search Results */
.search-results {
  max-height: 400px;
  overflow-y: auto;
}

.results-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.5rem 0.5rem;
  border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

.results-count {
  font-size: 0.875rem;
  color: #6b7280;
  font-weight: 500;
}

.results-actions {
  display: flex;
  gap: 0.5rem;
}

.action-btn {
  display: flex;
  align-items: center;
  gap: 0.375rem;
  padding: 0.375rem 0.75rem;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  background: white;
  color: #6b7280;
  font-size: 0.8rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.action-btn:hover {
  background: #f9fafb;
  border-color: #d1d5db;
}

.results-list {
  padding: 0.5rem 0;
}

.result-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0.875rem 1.5rem;
  cursor: pointer;
  transition: all 0.2s ease;
  border-left: 3px solid transparent;
}

.result-item:hover,
.result-item.selected {
  background: rgba(102, 126, 234, 0.05);
  border-left-color: #667eea;
}

.result-icon {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1rem;
  color: white;
  flex-shrink: 0;
}

.result-item.expenses .result-icon {
  background: linear-gradient(135deg, #10b981, #059669);
}

.result-item.categories .result-icon {
  background: linear-gradient(135deg, #f59e0b, #d97706);
}

.result-item.budgets .result-icon {
  background: linear-gradient(135deg, #8b5cf6, #7c3aed);
}

.result-content {
  flex: 1;
  min-width: 0;
}

.result-title {
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 0.25rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.result-subtitle {
  color: #6b7280;
  font-size: 0.875rem;
  margin-bottom: 0.25rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.result-meta {
  display: flex;
  gap: 0.75rem;
  font-size: 0.75rem;
  color: #9ca3af;
}

.result-type {
  text-transform: capitalize;
  font-weight: 500;
}

.result-amount {
  font-weight: 600;
  color: #059669;
}

.result-actions {
  display: flex;
  gap: 0.25rem;
  opacity: 0;
  transition: opacity 0.2s ease;
}

.result-item:hover .result-actions,
.result-item.selected .result-actions {
  opacity: 1;
}

.quick-action {
  width: 32px;
  height: 32px;
  border: none;
  border-radius: 6px;
  background: rgba(0, 0, 0, 0.05);
  color: #6b7280;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.875rem;
}

.quick-action:hover {
  background: rgba(102, 126, 234, 0.1);
  color: #667eea;
}

/* Load More */
.load-more {
  padding: 1rem 1.5rem;
  text-align: center;
  border-top: 1px solid rgba(0, 0, 0, 0.05);
}

.load-more-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin: 0 auto;
  padding: 0.5rem 1rem;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  background: white;
  color: #6b7280;
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.load-more-btn:hover {
  background: #f9fafb;
  border-color: #d1d5db;
}

/* No Results */
.no-results {
  padding: 3rem 1.5rem;
  text-align: center;
}

.no-results-icon {
  width: 64px;
  height: 64px;
  margin: 0 auto 1rem;
  border-radius: 50%;
  background: linear-gradient(135deg, #f3f4f6, #e5e7eb);
  display: flex;
  align-items: center;
  justify-content: center;
  color: #9ca3af;
  font-size: 1.5rem;
}

.no-results-title {
  font-size: 1.125rem;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 0.5rem;
}

.no-results-subtitle {
  color: #6b7280;
  font-size: 0.875rem;
}

.link-btn {
  color: #667eea;
  text-decoration: underline;
  background: none;
  border: none;
  cursor: pointer;
  font-size: inherit;
}

/* Search Suggestions */
.search-suggestions {
  padding: 1rem 0;
  max-height: 400px;
  overflow-y: auto;
}

.suggestions-header {
  padding: 0.5rem 1.5rem;
  font-size: 0.875rem;
  font-weight: 600;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.suggestions-list {
  padding: 0.5rem 0;
}

.suggestion-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  width: 100%;
  padding: 0.75rem 1.5rem;
  border: none;
  background: transparent;
  color: #374151;
  font-size: 0.875rem;
  text-align: left;
  cursor: pointer;
  transition: all 0.2s ease;
}

.suggestion-item:hover {
  background: rgba(102, 126, 234, 0.05);
}

.suggestion-item i {
  width: 16px;
  color: #9ca3af;
}

.suggestion-item.action i {
  color: #667eea;
}

/* Search Overlay */
.search-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.3);
  backdrop-filter: blur(4px);
  z-index: 1000;
  animation: overlayFadeIn 0.2s ease-out;
}

@keyframes overlayFadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

/* Mobile Styles */
.smart-search.mobile .search-interface {
  width: 95vw;
  max-height: 90vh;
  top: 10vh;
  transform: translateX(-50%);
}

.smart-search.mobile .search-input {
  font-size: 1rem;
}

.smart-search.mobile .search-filters {
  padding: 0.75rem 1rem;
}

.smart-search.mobile .filter-btn {
  padding: 0.375rem 0.75rem;
  font-size: 0.8rem;
}

.smart-search.mobile .result-item {
  padding: 0.75rem 1rem;
  gap: 0.75rem;
}

.smart-search.mobile .result-icon {
  width: 36px;
  height: 36px;
  font-size: 0.875rem;
}

/* Dark mode */
@media (prefers-color-scheme: dark) {
  .search-interface {
    background: rgba(30, 41, 59, 0.95);
    border-color: rgba(71, 85, 105, 0.3);
  }
  
  .search-input {
    color: #f1f5f9;
  }
  
  .search-input::placeholder {
    color: #64748b;
  }
  
  .result-title {
    color: #f1f5f9;
  }
  
  .result-subtitle {
    color: #94a3b8;
  }
  
  .no-results-title {
    color: #f1f5f9;
  }
}
</style>