<template>
  <div class="animate-pulse">
    <!-- Table Header -->
    <div class="bg-gray-50 px-6 py-3 border-b border-gray-200">
      <div class="flex space-x-4">
        <div class="h-4 bg-gray-300 rounded w-1/6"></div>
        <div class="h-4 bg-gray-300 rounded w-1/4"></div>
        <div class="h-4 bg-gray-300 rounded w-1/6"></div>
        <div class="h-4 bg-gray-300 rounded w-1/5"></div>
        <div class="h-4 bg-gray-300 rounded w-1/6"></div>
      </div>
    </div>

    <!-- Table Rows -->
    <div class="divide-y divide-gray-200">
      <div 
        v-for="n in rows" 
        :key="n" 
        class="px-6 py-4 flex space-x-4 items-center"
      >
        <div class="h-4 bg-gray-200 rounded w-1/6"></div>
        <div class="h-4 bg-gray-200 rounded w-1/4"></div>
        <div class="h-4 bg-gray-200 rounded w-1/6"></div>
        <div class="flex items-center space-x-2 w-1/5">
          <div class="h-6 w-6 bg-gray-200 rounded-full"></div>
          <div class="h-4 bg-gray-200 rounded flex-1"></div>
        </div>
        <div class="h-8 bg-gray-200 rounded w-20"></div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  rows: {
    type: Number,
    default: 5
  }
})
</script>

<style scoped>
.animate-pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: .5;
  }
}
</style>