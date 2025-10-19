<template>
  <div class="min-h-screen bg-gray-50">
    <AppHeader />
    <div class="flex">
      <AppSidebar class="hidden md:block" />
      <main class="flex-1">
        <div class="max-w-7xl mx-auto">
          <!-- Tab Navigation -->
          <div class="tab-navigation">
            <button 
              @click="activeTab = 'overview'" 
              :class="['tab-button', { active: activeTab === 'overview' }]"
            >
              <i class="fas fa-chart-pie"></i>
              Overview
            </button>
            <button 
              @click="activeTab = 'manage'" 
              :class="['tab-button', { active: activeTab === 'manage' }]"
            >
              <i class="fas fa-list"></i>
              Manage Budgets
            </button>
          </div>
          
          <!-- Tab Content -->
          <div class="tab-content">
            <BudgetOverview v-if="activeTab === 'overview'" />
            <BudgetList v-else :auto-open-create="shouldAutoOpenCreate" />
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import AppHeader from '@/components/layout/AppHeader.vue'
import AppSidebar from '@/components/layout/AppSidebar.vue'
import BudgetList from '@/components/budgets/BudgetList.vue'
import BudgetOverview from '@/components/budgets/BudgetOverview.vue'

const route = useRoute()
const router = useRouter()
const activeTab = ref('overview')
const shouldAutoOpenCreate = ref(false)

// Check for Quick Action
onMounted(() => {
  if (route.query.action === 'create') {
    // Switch to manage tab and trigger modal
    activeTab.value = 'manage'
    shouldAutoOpenCreate.value = true
    // Clear the query parameter
    router.replace({ path: route.path })
  }
})
</script>

<style scoped>
.tab-navigation {
  display: flex;
  background: white;
  border-bottom: 1px solid #E5E7EB;
  margin-bottom: 0;
}

.tab-button {
  padding: 16px 24px;
  border: none;
  background: none;
  color: #6B7280;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  gap: 8px;
  border-bottom: 2px solid transparent;
}

.tab-button:hover {
  color: #374151;
  background: #F9FAFB;
}

.tab-button.active {
  color: #3B82F6;
  border-bottom-color: #3B82F6;
  background: #EFF6FF;
}

.tab-content {
  background: transparent;
}
</style>