<template>
  <div class="min-h-screen bg-gray-50">
    <AppHeader />
    <div class="flex">
      <AppSidebar class="hidden md:block" />
      <main class="flex-1 p-6">
        <div class="max-w-7xl mx-auto">
          <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-900">Expenses</h1>
            <p class="text-gray-600">Manage your expenses</p>
          </div>

          <ExpenseList />
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
  import AppHeader from '@/components/layout/AppHeader.vue'
  import AppSidebar from '@/components/layout/AppSidebar.vue'
  import ExpenseList from '@/components/expenses/ExpenseList.vue'

  // ExpenseList component will handle fetching expenses with pagination
</script>
