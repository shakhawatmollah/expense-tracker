<template>
  <div id="app">
    <router-view />
    <ToastContainer />
    <ToastNotification position="top-right" />
    <ToastNotification position="bottom-right" />
  </div>
</template>

<script setup>
  import { onMounted } from 'vue'
  import { useAuthStore } from '@/stores/auth'
  import { useNotificationsStore } from '@/stores/notifications'
  import ToastContainer from '@/components/common/ToastContainer.vue'
  import ToastNotification from '@/components/notifications/ToastNotification.vue'

  const authStore = useAuthStore()
  const notificationsStore = useNotificationsStore()

  onMounted(() => {
    // Initialize auth state from localStorage
    authStore.initializeAuth()
    
    // Load notification history from localStorage
    notificationsStore.loadFromLocalStorage()
    
    // Request desktop notification permission if not already granted
    if ('Notification' in window && Notification.permission === 'default') {
      // Don't request immediately, wait for user interaction
      setTimeout(() => {
        if (notificationsStore.preferences.desktop) {
          notificationsStore.requestDesktopPermission()
        }
      }, 5000)
    }
  })
</script>

<style scoped>
  #app {
    min-height: 100vh;
  }
</style>
