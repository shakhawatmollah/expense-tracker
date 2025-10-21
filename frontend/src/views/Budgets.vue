<template>
  <div class="min-h-screen bg-gray-50">
    <AppHeader />
    <div class="flex">
      <AppSidebar class="hidden md:block" />
      <main class="flex-1">
        <div class="max-w-7xl mx-auto">
          <!-- Tab Navigation -->
          <div class="tab-navigation">
            <button @click="activeTab = 'overview'" :class="['tab-button', { active: activeTab === 'overview' }]">
              <i class="fas fa-chart-pie"></i>
              Overview
            </button>
            <button @click="activeTab = 'manage'" :class="['tab-button', { active: activeTab === 'manage' }]">
              <i class="fas fa-list"></i>
              Manage Budgets
            </button>
          </div>

          <!-- Tab Content -->
          <div class="tab-content">
            <BudgetOverview v-if="activeTab === 'overview'" />
            <BudgetList v-else />
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

  // Check for Quick Action - only used to switch tabs
  onMounted(() => {
    if (route.query.action === 'create') {
      // Switch to manage tab only
      activeTab.value = 'manage'
      // Clear the query parameter
      router.replace({ path: route.path })
    }
  })
</script>

<style scoped>
  .tab-navigation {
    display: flex;
    background: white;
    border-bottom: 1px solid #e5e7eb;
    margin-bottom: 0;
  }

  .tab-button {
    padding: 16px 24px;
    border: none;
    background: none;
    color: #6b7280;
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
    background: #f9fafb;
  }

  .tab-button.active {
    color: #3b82f6;
    border-bottom-color: #3b82f6;
    background: #eff6ff;
  }

  .tab-content {
    background: transparent;
  }
</style>
