<template>
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
    <div 
      v-for="n in count" 
      :key="n"
      class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 animate-pulse"
    >
      <!-- Header -->
      <div class="flex items-center justify-between mb-4">
        <div class="h-4 bg-gray-200 rounded w-2/3"></div>
        <div class="h-6 w-6 bg-gray-200 rounded-full"></div>
      </div>

      <!-- Main content -->
      <div class="space-y-3">
        <div class="h-8 bg-gray-300 rounded w-1/2"></div>
        <div class="h-4 bg-gray-200 rounded w-3/4"></div>
      </div>

      <!-- Progress bar -->
      <div class="mt-4">
        <div class="h-2 bg-gray-200 rounded-full">
          <div class="h-2 bg-gray-300 rounded-full" :style="`width: ${Math.random() * 80 + 10}%`"></div>
        </div>
      </div>

      <!-- Footer -->
      <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-100">
        <div class="h-3 bg-gray-200 rounded w-1/3"></div>
        <div class="h-3 bg-gray-200 rounded w-1/4"></div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  count: {
    type: Number,
    default: 6
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