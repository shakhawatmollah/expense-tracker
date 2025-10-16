<template>
  <div class="p-4 border rounded">
    <h3 class="text-lg font-bold">API Test Component</h3>
    <div class="mt-4 space-y-2">
      <button 
        @click="testAuth" 
        class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
      >
        Test Auth
      </button>
      <button 
        @click="testTrends" 
        class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600"
      >
        Test Trends API
      </button>
      <div v-if="results" class="mt-4 p-2 bg-gray-100 rounded">
        <pre>{{ results }}</pre>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { dashboardService } from '@/services/dashboardService'
import api from '@/services/api'

const results = ref('')

const testAuth = async () => {
  try {
    const response = await api.get('/auth/me')
    results.value = JSON.stringify(response.data, null, 2)
  } catch (error) {
    results.value = `Auth Error: ${error.message}\n${JSON.stringify(error.response?.data, null, 2)}`
  }
}

const testTrends = async () => {
  try {
    const response = await dashboardService.getTrends(6)
    results.value = JSON.stringify(response, null, 2)
  } catch (error) {
    results.value = `Trends Error: ${error.message}\n${JSON.stringify(error.response?.data, null, 2)}`
  }
}
</script>