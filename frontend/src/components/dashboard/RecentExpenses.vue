<template>
  <div class="recent-expenses-card">
    <!-- Header Section -->
    <div class="card-header">
      <div class="header-content">
        <div class="header-title">
          <div class="title-icon">
            <i class="fas fa-clock"></i>
          </div>
          <div>
            <h3 class="card-title">Recent Expenses</h3>
            <p class="card-subtitle">Your latest spending activity</p>
          </div>
        </div>
        <div class="header-actions">
          <button @click="toggleFilter" class="filter-btn" :class="{ active: showFilter }" title="Filter expenses">
            <i class="fas fa-filter"></i>
          </button>
          <router-link to="/expenses" class="view-all-btn" title="View all expenses">
            <i class="fas fa-external-link-alt"></i>
            View All
          </router-link>
        </div>
      </div>

      <!-- Filter Section -->
      <div v-if="showFilter" class="filter-section">
        <div class="filter-controls">
          <div class="search-box">
            <i class="fas fa-search search-icon"></i>
            <input v-model="searchQuery" type="text" placeholder="Search expenses..." class="search-input" />
          </div>
          <select v-model="selectedCategory" class="category-filter">
            <option value="">All Categories</option>
            <option v-for="category in categories" :key="category.id" :value="category.id">
              {{ category.name }}
            </option>
          </select>
          <select v-model="dateFilter" class="date-filter">
            <option value="7">Last 7 days</option>
            <option value="30">Last 30 days</option>
            <option value="90">Last 3 months</option>
            <option value="">All time</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-state">
      <div class="loading-skeleton">
        <div v-for="i in 5" :key="i" class="skeleton-item">
          <div class="skeleton-avatar"></div>
          <div class="skeleton-content">
            <div class="skeleton-line skeleton-title"></div>
            <div class="skeleton-line skeleton-subtitle"></div>
          </div>
          <div class="skeleton-amount"></div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else-if="filteredExpenses.length === 0" class="empty-state">
      <div class="empty-icon">
        <i class="fas fa-receipt"></i>
      </div>
      <h3 class="empty-title">
        {{ searchQuery || selectedCategory || dateFilter ? 'No matching expenses' : 'No recent expenses' }}
      </h3>
      <p class="empty-description">
        {{
          searchQuery || selectedCategory || dateFilter
            ? 'Try adjusting your filters to see more results.'
            : 'Start tracking your expenses to see them here.'
        }}
      </p>
      <button
        v-if="!searchQuery && !selectedCategory && !dateFilter"
        @click="$router.push('/expenses?action=create')"
        class="empty-action-btn"
      >
        <i class="fas fa-plus"></i>
        Add First Expense
      </button>
    </div>

    <!-- Expenses List -->
    <div v-else class="expenses-list">
      <div
        v-for="(expense, index) in displayedExpenses"
        :key="expense.id"
        class="expense-item"
        :class="{ 'is-new': isNewExpense(expense) }"
        :style="{ '--delay': index * 0.1 + 's' }"
      >
        <!-- Category Icon -->
        <div class="expense-avatar">
          <div class="category-icon" :style="{ backgroundColor: expense.category?.color || '#6B7280' }">
            <i :class="expense.category?.icon || 'fas fa-tag'"></i>
          </div>
          <div v-if="isNewExpense(expense)" class="new-badge">
            <i class="fas fa-star"></i>
          </div>
        </div>

        <!-- Expense Details -->
        <div class="expense-details">
          <div class="expense-info">
            <h4 class="expense-title">{{ expense.description }}</h4>
            <div class="expense-meta">
              <span class="expense-category">
                <i class="fas fa-folder"></i>
                {{ expense.category?.name || 'Uncategorized' }}
              </span>
              <span class="expense-date">
                <i class="fas fa-calendar"></i>
                {{ formatRelativeDate(expense.date) }}
              </span>
            </div>
          </div>
          <div class="expense-amount">
            <span class="amount-value">${{ formatAmount(expense.amount) }}</span>
            <div class="amount-trend" :class="getTrendClass(expense)">
              <i :class="getTrendIcon(expense)"></i>
            </div>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="expense-actions">
          <button @click="editExpense(expense)" class="action-btn edit-btn" title="Edit expense">
            <i class="fas fa-edit"></i>
          </button>
          <button @click="duplicateExpense(expense)" class="action-btn duplicate-btn" title="Duplicate expense">
            <i class="fas fa-copy"></i>
          </button>
          <button @click="deleteExpense(expense)" class="action-btn delete-btn" title="Delete expense">
            <i class="fas fa-trash"></i>
          </button>
        </div>
      </div>

      <!-- Load More Button -->
      <div v-if="filteredExpenses.length > displayLimit" class="load-more-section">
        <button @click="loadMore" class="load-more-btn">
          <i class="fas fa-chevron-down"></i>
          Show {{ Math.min(5, filteredExpenses.length - displayLimit) }} more
          <span class="remaining-count">({{ filteredExpenses.length - displayLimit }} remaining)</span>
        </button>
      </div>
    </div>

    <!-- Summary Footer -->
    <div v-if="filteredExpenses.length > 0" class="card-footer">
      <div class="summary-stats">
        <div class="stat-item">
          <span class="stat-label">Total</span>
          <span class="stat-value">${{ formatAmount(totalAmount) }}</span>
        </div>
        <div class="stat-item">
          <span class="stat-label">Average</span>
          <span class="stat-value">${{ formatAmount(averageAmount) }}</span>
        </div>
        <div class="stat-item">
          <span class="stat-label">Count</span>
          <span class="stat-value">{{ filteredExpenses.length }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
  import { computed, ref, onMounted, watch } from 'vue'
  import { useRouter } from 'vue-router'
  import { useExpensesStore } from '@/stores/expenses'
  import { useCategoriesStore } from '@/stores/categories'

  const router = useRouter()
  const expensesStore = useExpensesStore()
  const categoriesStore = useCategoriesStore()

  // Reactive state
  const showFilter = ref(false)
  const searchQuery = ref('')
  const selectedCategory = ref('')
  const dateFilter = ref('30') // Default to last 30 days
  const displayLimit = ref(5)

  // Computed properties
  const expenses = computed(() => expensesStore.expenses || [])
  const categories = computed(() => categoriesStore.categories || [])
  const loading = computed(() => expensesStore.loading)

  // Filter expenses based on search, category, and date
  const filteredExpenses = computed(() => {
    let filtered = [...expenses.value]

    // Search filter
    if (searchQuery.value) {
      const query = searchQuery.value.toLowerCase()
      filtered = filtered.filter(
        expense =>
          expense.description?.toLowerCase().includes(query) || expense.category?.name?.toLowerCase().includes(query)
      )
    }

    // Category filter
    if (selectedCategory.value) {
      filtered = filtered.filter(expense => expense.category_id === parseInt(selectedCategory.value))
    }

    // Date filter
    if (dateFilter.value) {
      const daysAgo = parseInt(dateFilter.value)
      const cutoffDate = new Date()
      cutoffDate.setDate(cutoffDate.getDate() - daysAgo)

      filtered = filtered.filter(expense => {
        const expenseDate = new Date(expense.date || expense.expense_date)
        return expenseDate >= cutoffDate
      })
    }

    // Sort by date (newest first)
    return filtered.sort((a, b) => {
      const dateA = new Date(a.date || a.expense_date)
      const dateB = new Date(b.date || b.expense_date)
      return dateB - dateA
    })
  })

  // Display limited expenses
  const displayedExpenses = computed(() => filteredExpenses.value.slice(0, displayLimit.value))

  // Summary calculations
  const totalAmount = computed(() =>
    filteredExpenses.value.reduce((sum, expense) => sum + parseFloat(expense.amount || 0), 0)
  )

  const averageAmount = computed(() =>
    filteredExpenses.value.length > 0 ? totalAmount.value / filteredExpenses.value.length : 0
  )

  // Helper functions
  const formatAmount = amount => {
    return new Intl.NumberFormat('en-US', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    }).format(amount || 0)
  }

  const formatRelativeDate = date => {
    if (!date) return 'Unknown date'

    const expenseDate = new Date(date)
    const now = new Date()
    const diffTime = Math.abs(now - expenseDate)
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))

    if (diffDays === 0) return 'Today'
    if (diffDays === 1) return 'Yesterday'
    if (diffDays <= 7) return `${diffDays} days ago`
    if (diffDays <= 30) return `${Math.ceil(diffDays / 7)} weeks ago`
    if (diffDays <= 365) return `${Math.ceil(diffDays / 30)} months ago`
    return `${Math.ceil(diffDays / 365)} years ago`
  }

  const isNewExpense = expense => {
    if (!expense.date && !expense.expense_date) return false
    const expenseDate = new Date(expense.date || expense.expense_date)
    const oneDayAgo = new Date()
    oneDayAgo.setDate(oneDayAgo.getDate() - 1)
    return expenseDate > oneDayAgo
  }

  const getTrendClass = expense => {
    const amount = parseFloat(expense.amount || 0)
    if (amount > 100) return 'trend-high'
    if (amount > 50) return 'trend-medium'
    return 'trend-low'
  }

  const getTrendIcon = expense => {
    const amount = parseFloat(expense.amount || 0)
    if (amount > 100) return 'fas fa-arrow-up'
    if (amount > 50) return 'fas fa-minus'
    return 'fas fa-arrow-down'
  }

  // Action handlers
  const toggleFilter = () => {
    showFilter.value = !showFilter.value
  }

  const loadMore = () => {
    displayLimit.value += 5
  }

  const editExpense = expense => {
    router.push(`/expenses?edit=${expense.id}`)
  }

  const duplicateExpense = async expense => {
    try {
      const duplicatedData = {
        description: `${expense.description} (Copy)`,
        amount: expense.amount,
        date: new Date().toISOString().split('T')[0], // Today's date
        category_id: expense.category_id
      }

      await expensesStore.createExpense(duplicatedData)
      // Refresh the expenses list
      await loadRecentExpenses()
    } catch (error) {
      console.error('Failed to duplicate expense:', error)
    }
  }

  const deleteExpense = async expense => {
    if (confirm(`Are you sure you want to delete "${expense.description}"?`)) {
      try {
        await expensesStore.deleteExpense(expense.id)
        // Refresh the expenses list
        await loadRecentExpenses()
      } catch (error) {
        console.error('Failed to delete expense:', error)
      }
    }
  }

  const loadRecentExpenses = async () => {
    try {
      await expensesStore.fetchExpenses({
        per_page: 50, // Load more for filtering
        sort: 'date',
        order: 'desc'
      })
    } catch (error) {
      console.error('Failed to load recent expenses:', error)
    }
  }

  // Watch for filter changes to reset display limit
  watch([searchQuery, selectedCategory, dateFilter], () => {
    displayLimit.value = 5
  })

  // Load data on mount
  onMounted(async () => {
    await Promise.all([loadRecentExpenses(), categoriesStore.fetchCategories()])
  })
</script>

<style scoped>
  /* Main Card Container */
  .recent-expenses-card {
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.9), rgba(255, 255, 255, 0.7));
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 24px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  }

  .recent-expenses-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
  }

  /* Header Section */
  .card-header {
    padding: 1.5rem;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.05), rgba(118, 75, 162, 0.05));
    border-bottom: 1px solid rgba(229, 231, 235, 0.8);
  }

  .header-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1rem;
  }

  .header-title {
    display: flex;
    align-items: center;
    gap: 1rem;
  }

  .title-icon {
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

  .card-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #1f2937;
    margin: 0;
  }

  .card-subtitle {
    font-size: 0.875rem;
    color: #6b7280;
    margin: 0.25rem 0 0 0;
  }

  .header-actions {
    display: flex;
    align-items: center;
    gap: 0.75rem;
  }

  .filter-btn {
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

  .filter-btn:hover,
  .filter-btn.active {
    background: rgba(102, 126, 234, 0.1);
    color: #667eea;
    transform: scale(1.05);
  }

  .view-all-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    border-radius: 12px;
    text-decoration: none;
    font-size: 0.875rem;
    font-weight: 600;
    transition: all 0.3s ease;
  }

  .view-all-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
  }

  /* Filter Section */
  .filter-section {
    animation: filterSlideDown 0.3s ease-out;
  }

  @keyframes filterSlideDown {
    from {
      opacity: 0;
      transform: translateY(-10px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  .filter-controls {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr;
    gap: 1rem;
    margin-top: 1rem;
  }

  .search-box {
    position: relative;
  }

  .search-icon {
    position: absolute;
    left: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
    font-size: 0.875rem;
  }

  .search-input {
    width: 100%;
    padding: 0.5rem 0.75rem 0.5rem 2.25rem;
    border: 1px solid #d1d5db;
    border-radius: 12px;
    font-size: 0.875rem;
    transition: all 0.3s ease;
  }

  .search-input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
  }

  .category-filter,
  .date-filter {
    padding: 0.5rem 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 12px;
    font-size: 0.875rem;
    background: white;
    transition: all 0.3s ease;
  }

  .category-filter:focus,
  .date-filter:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
  }

  /* Loading State */
  .loading-state {
    padding: 1.5rem;
  }

  .loading-skeleton {
    display: flex;
    flex-direction: column;
    gap: 1rem;
  }

  .skeleton-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    animation: pulse 2s infinite;
  }

  .skeleton-avatar {
    width: 48px;
    height: 48px;
    background: #e5e7eb;
    border-radius: 12px;
  }

  .skeleton-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
  }

  .skeleton-line {
    height: 1rem;
    background: #e5e7eb;
    border-radius: 4px;
  }

  .skeleton-title {
    width: 60%;
  }

  .skeleton-subtitle {
    width: 40%;
  }

  .skeleton-amount {
    width: 80px;
    height: 1rem;
    background: #e5e7eb;
    border-radius: 4px;
  }

  @keyframes pulse {
    0%,
    100% {
      opacity: 1;
    }
    50% {
      opacity: 0.5;
    }
  }

  /* Empty State */
  .empty-state {
    padding: 3rem 1.5rem;
    text-align: center;
  }

  .empty-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
    font-size: 2rem;
    color: #9ca3af;
  }

  .empty-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
  }

  .empty-description {
    font-size: 0.875rem;
    color: #6b7280;
    margin-bottom: 1.5rem;
  }

  .empty-action-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    border: none;
    border-radius: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
  }

  .empty-action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
  }

  /* Expenses List */
  .expenses-list {
    padding: 0 1.5rem 1.5rem;
  }

  .expense-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    border-radius: 16px;
    margin-bottom: 0.75rem;
    background: rgba(255, 255, 255, 0.5);
    border: 1px solid rgba(229, 231, 235, 0.8);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    animation: expenseSlideIn 0.6s ease-out;
    animation-delay: var(--delay);
    animation-fill-mode: both;
  }

  .expense-item:hover {
    transform: translateY(-2px);
    background: rgba(255, 255, 255, 0.8);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
  }

  .expense-item.is-new {
    background: linear-gradient(135deg, rgba(34, 197, 94, 0.1), rgba(16, 185, 129, 0.1));
    border-color: rgba(34, 197, 94, 0.3);
  }

  @keyframes expenseSlideIn {
    from {
      opacity: 0;
      transform: translateY(20px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  /* Expense Avatar */
  .expense-avatar {
    position: relative;
    flex-shrink: 0;
  }

  .category-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.125rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  }

  .new-badge {
    position: absolute;
    top: -6px;
    right: -6px;
    width: 20px;
    height: 20px;
    background: linear-gradient(135deg, #f59e0b, #d97706);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 0.75rem;
    animation: badgePulse 2s infinite;
  }

  @keyframes badgePulse {
    0%,
    100% {
      transform: scale(1);
    }
    50% {
      transform: scale(1.1);
    }
  }

  /* Expense Details */
  .expense-details {
    flex: 1;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .expense-info {
    flex: 1;
  }

  .expense-title {
    font-size: 1rem;
    font-weight: 600;
    color: #1f2937;
    margin: 0 0 0.5rem 0;
  }

  .expense-meta {
    display: flex;
    align-items: center;
    gap: 1rem;
    font-size: 0.875rem;
    color: #6b7280;
  }

  .expense-category,
  .expense-date {
    display: flex;
    align-items: center;
    gap: 0.25rem;
  }

  .expense-amount {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    text-align: right;
  }

  .amount-value {
    font-size: 1.125rem;
    font-weight: 700;
    color: #1f2937;
  }

  .amount-trend {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
  }

  .trend-high {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
  }

  .trend-medium {
    background: rgba(245, 158, 11, 0.1);
    color: #f59e0b;
  }

  .trend-low {
    background: rgba(34, 197, 94, 0.1);
    color: #22c55e;
  }

  /* Quick Actions */
  .expense-actions {
    display: flex;
    gap: 0.5rem;
    opacity: 0;
    transition: opacity 0.3s ease;
  }

  .expense-item:hover .expense-actions {
    opacity: 1;
  }

  .action-btn {
    width: 32px;
    height: 32px;
    border: none;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.3s ease;
  }

  .edit-btn {
    background: rgba(59, 130, 246, 0.1);
    color: #3b82f6;
  }

  .edit-btn:hover {
    background: rgba(59, 130, 246, 0.2);
    transform: scale(1.1);
  }

  .duplicate-btn {
    background: rgba(107, 114, 128, 0.1);
    color: #6b7280;
  }

  .duplicate-btn:hover {
    background: rgba(107, 114, 128, 0.2);
    transform: scale(1.1);
  }

  .delete-btn {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
  }

  .delete-btn:hover {
    background: rgba(239, 68, 68, 0.2);
    transform: scale(1.1);
  }

  /* Load More Section */
  .load-more-section {
    text-align: center;
    margin-top: 1rem;
  }

  .load-more-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    background: rgba(255, 255, 255, 0.7);
    border: 1px solid rgba(229, 231, 235, 0.8);
    border-radius: 12px;
    color: #374151;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
  }

  .load-more-btn:hover {
    background: rgba(255, 255, 255, 0.9);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  }

  .remaining-count {
    font-size: 0.875rem;
    color: #6b7280;
  }

  /* Summary Footer */
  .card-footer {
    padding: 1.5rem;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.05), rgba(118, 75, 162, 0.05));
    border-top: 1px solid rgba(229, 231, 235, 0.8);
  }

  .summary-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
  }

  .stat-item {
    text-align: center;
  }

  .stat-label {
    display: block;
    font-size: 0.75rem;
    font-weight: 500;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 0.25rem;
  }

  .stat-value {
    display: block;
    font-size: 1.125rem;
    font-weight: 700;
    color: #1f2937;
  }

  /* Responsive Design */
  @media (max-width: 768px) {
    .filter-controls {
      grid-template-columns: 1fr;
      gap: 0.75rem;
    }

    .header-content {
      flex-direction: column;
      align-items: flex-start;
      gap: 1rem;
    }

    .expense-item {
      padding: 0.75rem;
    }

    .expense-details {
      flex-direction: column;
      align-items: flex-start;
      gap: 0.5rem;
    }

    .expense-meta {
      flex-direction: column;
      align-items: flex-start;
      gap: 0.25rem;
    }

    .summary-stats {
      grid-template-columns: 1fr;
      gap: 0.75rem;
    }
  }
</style>
