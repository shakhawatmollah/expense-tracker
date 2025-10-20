<template>
  <aside :class="['modern-sidebar', { 'collapsed': isCollapsed }]">
    <!-- Sidebar Header -->
    <div class="sidebar-header">
      <div class="sidebar-brand">
        <div class="brand-icon">
          <i class="fas fa-chart-line"></i>
        </div>
        <span v-if="!isCollapsed" class="brand-text">Menu</span>
      </div>
      <button @click="toggleSidebar" class="collapse-btn">
        <i :class="isCollapsed ? 'fas fa-chevron-right' : 'fas fa-chevron-left'"></i>
      </button>
    </div>

    <!-- Navigation Menu -->
    <nav class="sidebar-nav">
      <div class="nav-section">
        <h4 v-if="!isCollapsed" class="nav-title">Main Navigation</h4>
        <div class="nav-items">
          <router-link
            v-for="item in navigation"
            :key="item.name"
            :to="item.href"
            :class="[
              'nav-item',
              { 'active': $route.name === item.name }
            ]"
            :title="isCollapsed ? item.name : ''"
          >
            <div class="nav-icon">
              <i :class="item.iconClass"></i>
            </div>
            <span v-if="!isCollapsed" class="nav-text">{{ item.name }}</span>
            <div v-if="item.badge && !isCollapsed" class="nav-badge">
              {{ item.badge }}
            </div>
          </router-link>
        </div>
      </div>

      <!-- Quick Actions -->
      <div v-if="!isCollapsed" class="nav-section">
        <h4 class="nav-title">Quick Actions</h4>
        <div class="quick-actions">
          <button @click="addExpense" class="quick-action-btn expense-btn">
            <i class="fas fa-plus"></i>
            Add Expense
          </button>
          <button @click="createBudget" class="quick-action-btn budget-btn">
            <i class="fas fa-piggy-bank"></i>
            New Budget
          </button>
        </div>
      </div>

      <!-- User Stats -->
      <div v-if="!isCollapsed" class="nav-section user-stats">
        <h4 class="nav-title">This Month</h4>
        <div class="stats-card">
          <div class="stat-item">
            <div class="stat-icon expenses">
              <i class="fas fa-arrow-down"></i>
            </div>
            <div class="stat-content">
              <span class="stat-value">${{ monthlyExpensesTotal }}</span>
              <span class="stat-label">Expenses</span>
            </div>
          </div>
          <div class="stat-item">
            <div class="stat-icon budget">
              <i class="fas fa-target"></i>
            </div>
            <div class="stat-content">
              <span class="stat-value">${{ monthlyBudgetTotal }}</span>
              <span class="stat-label">Budget</span>
            </div>
          </div>
        </div>
      </div>
    </nav>
  </aside>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useExpensesStore } from '@/stores/expenses'
import { useCategoriesStore } from '@/stores/categories'

const router = useRouter()
const isCollapsed = ref(false)
const expensesStore = useExpensesStore()
const categoriesStore = useCategoriesStore()

// Dynamic navigation with computed badges
const navigation = computed(() => [
  { 
    name: 'Dashboard', 
    href: '/', 
    iconClass: 'fas fa-home',
    badge: null
  },
  { 
    name: 'Expenses', 
    href: '/expenses', 
    iconClass: 'fas fa-credit-card',
    badge: expensesStore.totalExpenses > 0 ? expensesStore.totalExpenses.toString() : null
  },
  { 
    name: 'Categories', 
    href: '/categories', 
    iconClass: 'fas fa-tags',
    badge: categoriesStore.categories.length > 0 ? categoriesStore.categories.length.toString() : null
  },
  { 
    name: 'Budgets', 
    href: '/budgets', 
    iconClass: 'fas fa-chart-bar',
    badge: null // TODO: Add budget store and count
  },
  { 
    name: 'Analytics', 
    href: '/analytics', 
    iconClass: 'fas fa-chart-pie',
    badge: null
  },
])

// Computed properties for dynamic stats
const monthlyExpensesTotal = computed(() => {
  const currentDate = new Date()
  const currentMonth = currentDate.getMonth()
  const currentYear = currentDate.getFullYear()
  
  const monthlyTotal = expensesStore.expenses
    .filter(expense => {
      if (!expense.date) return false
      const expenseDate = new Date(expense.date)
      return expenseDate.getMonth() === currentMonth && expenseDate.getFullYear() === currentYear
    })
    .reduce((total, expense) => total + parseFloat(expense.amount || 0), 0)
  
  return monthlyTotal.toFixed(2)
})

const monthlyBudgetTotal = computed(() => {
  // TODO: Replace with actual budget store data when available
  return '3,000.00'
})

const toggleSidebar = () => {
  isCollapsed.value = !isCollapsed.value
}

// Load data on component mount
onMounted(async () => {
  try {
    // Load expenses and categories for badge counts
    await Promise.all([
      expensesStore.fetchExpenses(),
      categoriesStore.fetchCategories()
    ])
  } catch (error) {
    console.error('Failed to load sidebar data:', error)
  }
})

// Quick Actions Functions
const addExpense = () => {
  router.push({ path: '/expenses', query: { action: 'create' } })
}

const createBudget = () => {
  router.push({ path: '/budgets', query: { action: 'create' } })
}
</script>

<style scoped>
.modern-sidebar {
  width: 280px;
  background: linear-gradient(180deg, 
    rgba(44, 62, 80, 0.95) 0%, 
    rgba(52, 73, 94, 0.95) 50%,
    rgba(44, 62, 80, 0.98) 100%
  );
  backdrop-filter: blur(20px);
  min-height: 100vh;
  position: sticky;
  top: 0;
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  box-shadow: 
    4px 0 40px rgba(0, 0, 0, 0.15),
    inset -1px 0 0 rgba(255, 255, 255, 0.1);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  border-right: 1px solid rgba(255, 255, 255, 0.08);
}

.modern-sidebar.collapsed {
  width: 80px;
}

/* Sidebar Header */
.sidebar-header {
  padding: 1.5rem 1.25rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.sidebar-brand {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.brand-icon {
  width: 48px;
  height: 48px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1.25rem;
  box-shadow: 
    0 8px 32px rgba(102, 126, 234, 0.3),
    0 0 0 1px rgba(255, 255, 255, 0.1),
    inset 0 1px 0 rgba(255, 255, 255, 0.2);
  position: relative;
  overflow: hidden;
}

.brand-icon::before {
  content: '';
  position: absolute;
  top: -50%;
  left: -50%;
  width: 200%;
  height: 200%;
  background: radial-gradient(circle, rgba(255,255,255,0.2) 0%, transparent 70%);
  animation: brandIconGlow 4s ease-in-out infinite;
}

@keyframes brandIconGlow {
  0%, 100% { opacity: 0; transform: scale(0.8); }
  50% { opacity: 1; transform: scale(1); }
}

.brand-text {
  color: white;
  font-weight: 600;
  font-size: 1.1rem;
}

.collapse-btn {
  background: rgba(255, 255, 255, 0.1);
  border: none;
  color: white;
  padding: 0.5rem;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s ease;
  backdrop-filter: blur(10px);
}

.collapse-btn:hover {
  background: rgba(255, 255, 255, 0.2);
  transform: scale(1.1);
}

/* Navigation */
.sidebar-nav {
  flex: 1;
  padding: 1rem 0;
  overflow-y: auto;
}

.nav-section {
  margin-bottom: 2rem;
}

.nav-title {
  color: rgba(255, 255, 255, 0.6);
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 1px;
  margin: 0 0 1rem 1.25rem;
}

.nav-items {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  padding: 0 1rem;
}

.nav-item {
  display: flex;
  align-items: center;
  padding: 1rem 1.25rem;
  color: rgba(255, 255, 255, 0.8);
  text-decoration: none;
  border-radius: 16px;
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  overflow: hidden;
  gap: 1rem;
  margin-bottom: 0.25rem;
  border: 1px solid transparent;
}

.nav-item::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
  transition: left 0.6s cubic-bezier(0.4, 0, 0.2, 1);
}

.nav-item::after {
  content: '';
  position: absolute;
  left: 0;
  top: 50%;
  width: 3px;
  height: 0;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 0 2px 2px 0;
  transition: height 0.3s ease;
  transform: translateY(-50%);
}

.nav-item:hover::before {
  left: 100%;
}

.nav-item:hover {
  background: rgba(255, 255, 255, 0.12);
  color: white;
  transform: translateX(8px);
  border-color: rgba(255, 255, 255, 0.15);
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.nav-item:hover::after {
  height: 24px;
}

.nav-item.active {
  background: linear-gradient(135deg, 
    rgba(102, 126, 234, 0.2) 0%, 
    rgba(118, 75, 162, 0.2) 100%
  );
  color: white;
  border-color: rgba(102, 126, 234, 0.3);
  box-shadow: 
    0 8px 32px rgba(102, 126, 234, 0.2),
    inset 0 1px 0 rgba(255, 255, 255, 0.1);
  transform: translateX(8px);
}

.nav-item.active::after {
  height: 32px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  box-shadow: 0 2px 8px rgba(102, 126, 234, 0.4);
}

.nav-icon {
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
  flex-shrink: 0;
}

.nav-text {
  font-weight: 500;
  font-size: 0.95rem;
  flex: 1;
}

.nav-badge {
  background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
  color: white;
  font-size: 0.7rem;
  font-weight: 700;
  padding: 0.3rem 0.6rem;
  border-radius: 12px;
  min-width: 22px;
  text-align: center;
  box-shadow: 
    0 4px 16px rgba(255, 107, 107, 0.3),
    0 0 0 1px rgba(255, 255, 255, 0.2);
  animation: badgePulse 2s ease-in-out infinite;
  position: relative;
  overflow: hidden;
}

.nav-badge::before {
  content: '';
  position: absolute;
  top: -50%;
  left: -50%;
  width: 200%;
  height: 200%;
  background: radial-gradient(circle, rgba(255,255,255,0.3) 0%, transparent 70%);
  animation: badgeShine 3s ease-in-out infinite;
}

@keyframes badgePulse {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.05); }
}

@keyframes badgeShine {
  0%, 100% { opacity: 0; transform: scale(0.5); }
  50% { opacity: 1; transform: scale(1); }
}

/* Quick Actions */
.quick-actions {
  padding: 0 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.quick-action-btn {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border: none;
  color: white;
  padding: 1rem 1.25rem;
  border-radius: 16px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  display: flex;
  align-items: center;
  gap: 0.75rem;
  box-shadow: 
    0 8px 32px rgba(102, 126, 234, 0.25),
    0 0 0 1px rgba(255, 255, 255, 0.1),
    inset 0 1px 0 rgba(255, 255, 255, 0.2);
  position: relative;
  overflow: hidden;
}

.quick-action-btn::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
  transition: left 0.6s cubic-bezier(0.4, 0, 0.2, 1);
}

.quick-action-btn:hover::before {
  left: 100%;
}

.quick-action-btn:hover {
  transform: translateY(-4px) scale(1.02);
  box-shadow: 
    0 16px 48px rgba(102, 126, 234, 0.35),
    0 8px 32px rgba(102, 126, 234, 0.25),
    0 0 0 1px rgba(255, 255, 255, 0.2);
}

.expense-btn {
  background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
}

.budget-btn {
  background: linear-gradient(135deg, #26de81 0%, #20bf6b 100%);
}

/* User Stats */
.user-stats {
  padding: 0 1rem;
}

.stats-card {
  background: rgba(255, 255, 255, 0.05);
  border-radius: 16px;
  padding: 1rem;
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.stat-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 0.75rem;
}

.stat-item:last-child {
  margin-bottom: 0;
}

.stat-icon {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 0.9rem;
}

.stat-icon.expenses {
  background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
}

.stat-icon.budget {
  background: linear-gradient(135deg, #26de81 0%, #20bf6b 100%);
}

.stat-content {
  display: flex;
  flex-direction: column;
}

.stat-value {
  color: white;
  font-weight: 600;
  font-size: 0.95rem;
}

.stat-label {
  color: rgba(255, 255, 255, 0.6);
  font-size: 0.75rem;
}

/* Responsive Design */
@media (max-width: 768px) {
  .modern-sidebar {
    position: fixed;
    z-index: 1000;
    transform: translateX(-100%);
  }
  
  .modern-sidebar.collapsed {
    width: 280px;
    transform: translateX(0);
  }
}

/* Scrollbar Styling */
.sidebar-nav::-webkit-scrollbar {
  width: 4px;
}

.sidebar-nav::-webkit-scrollbar-track {
  background: rgba(255, 255, 255, 0.1);
}

.sidebar-nav::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.3);
  border-radius: 2px;
}

.sidebar-nav::-webkit-scrollbar-thumb:hover {
  background: rgba(255, 255, 255, 0.5);
}
</style>