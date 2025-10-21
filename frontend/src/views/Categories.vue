<template>
  <div class="min-h-screen bg-gray-50">
    <AppHeader />
    <div class="flex">
      <AppSidebar class="hidden md:block" />
      <main class="flex-1 p-6">
        <div class="max-w-7xl mx-auto">
          <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-900">Categories</h1>
            <p class="text-gray-600">Manage your expense categories</p>
          </div>

          <CategoryList :auto-open-create="autoOpenCreate" />
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
  import CategoryList from '@/components/categories/CategoryList.vue'
  import { useCategoriesStore } from '@/stores/categories'

  const route = useRoute()
  const router = useRouter()
  const categoriesStore = useCategoriesStore()
  const autoOpenCreate = ref(false)

  onMounted(async () => {
    try {
      await categoriesStore.fetchCategories()
      
      // Handle quick action from query parameter
      if (route.query.action === 'create') {
        autoOpenCreate.value = true
        // Clear the query parameter
        router.replace({ path: route.path })
      }
    } catch (error) {
      console.error('Failed to load categories:', error)
    }
  })
</script>
