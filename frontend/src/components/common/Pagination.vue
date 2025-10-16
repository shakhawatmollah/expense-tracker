<template>
  <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
    <div class="flex-1 flex justify-between sm:hidden">
      <button
        @click="$emit('page-changed', pagination.current_page - 1)"
        :disabled="pagination.current_page <= 1"
        class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
      >
        Previous
      </button>
      <button
        @click="$emit('page-changed', pagination.current_page + 1)"
        :disabled="pagination.current_page >= pagination.last_page"
        class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
      >
        Next
      </button>
    </div>
    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
      <div>
        <p class="text-sm text-gray-700">
          Showing
          <span class="font-medium">{{ pagination.from || 0 }}</span>
          to
          <span class="font-medium">{{ pagination.to || 0 }}</span>
          of
          <span class="font-medium">{{ pagination.total || 0 }}</span>
          results
        </p>
      </div>
      <div>
        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
          <!-- Previous button -->
          <button
            @click="$emit('page-changed', pagination.current_page - 1)"
            :disabled="pagination.current_page <= 1"
            class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span class="sr-only">Previous</span>
            <ChevronLeftIcon class="h-5 w-5" aria-hidden="true" />
          </button>

          <!-- Page numbers -->
          <template v-for="page in pageNumbers" :key="page">
            <button
              v-if="page !== '...'"
              @click="$emit('page-changed', page)"
              :class="[
                'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                page === pagination.current_page
                  ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600'
                  : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'
              ]"
            >
              {{ page }}
            </button>
            <span
              v-else
              class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700"
            >
              ...
            </span>
          </template>

          <!-- Next button -->
          <button
            @click="$emit('page-changed', pagination.current_page + 1)"
            :disabled="pagination.current_page >= pagination.last_page"
            class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span class="sr-only">Next</span>
            <ChevronRightIcon class="h-5 w-5" aria-hidden="true" />
          </button>
        </nav>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { ChevronLeftIcon, ChevronRightIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  pagination: {
    type: Object,
    required: true
  }
})

defineEmits(['page-changed'])

const pageNumbers = computed(() => {
  const current = props.pagination.current_page
  const last = props.pagination.last_page
  const delta = 2 // Number of pages to show around current page
  const pages = []

  // Always show first page
  if (last > 0) {
    pages.push(1)
  }

  // Calculate start and end for middle pages
  const start = Math.max(2, current - delta)
  const end = Math.min(last - 1, current + delta)

  // Add ellipsis after first page if needed
  if (start > 2) {
    pages.push('...')
  }

  // Add middle pages
  for (let i = start; i <= end; i++) {
    pages.push(i)
  }

  // Add ellipsis before last page if needed
  if (end < last - 1) {
    pages.push('...')
  }

  // Always show last page (if it's not the first page)
  if (last > 1) {
    pages.push(last)
  }

  return pages
})
</script>