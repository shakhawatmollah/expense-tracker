<template>
  <div class="min-h-screen bg-gray-50">
    <AppHeader />
    <div class="flex">
      <AppSidebar class="hidden md:block" />
      <main class="flex-1 p-6">
        <div class="max-w-7xl mx-auto">
          <BudgetList />
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import AppHeader from '@/components/layout/AppHeader.vue'
import AppSidebar from '@/components/layout/AppSidebar.vue'
import BudgetList from '@/components/budgets/BudgetList.vue'
</script>