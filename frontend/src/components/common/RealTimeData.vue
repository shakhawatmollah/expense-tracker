<template>
  <div class="real-time-data">
    <!-- Auto-refresh Indicator -->
    <div 
      v-if="isAutoRefreshEnabled"
      class="refresh-indicator"
      :class="{ 
        'syncing': isSyncing, 
        'error': hasError,
        'paused': isPaused
      }"
      @click="toggleAutoRefresh"
      title="Real-time sync status"
    >
      <div class="indicator-icon">
        <i v-if="isSyncing" class="fas fa-sync-alt fa-spin"></i>
        <i v-else-if="hasError" class="fas fa-exclamation-triangle"></i>
        <i v-else-if="isPaused" class="fas fa-pause"></i>
        <i v-else class="fas fa-wifi"></i>
      </div>
      
      <div class="indicator-content">
        <div class="status-text">{{ getStatusText() }}</div>
        <div class="last-sync">{{ getLastSyncText() }}</div>
      </div>
      
      <div class="sync-progress" v-if="isSyncing">
        <div class="progress-bar" :style="{ width: `${syncProgress}%` }"></div>
      </div>
    </div>

    <!-- Manual Refresh Button -->
    <button 
      v-if="!isAutoRefreshEnabled || hasError"
      @click="manualRefresh"
      class="manual-refresh-btn"
      :disabled="isSyncing"
      title="Refresh data manually"
    >
      <i class="fas fa-sync-alt" :class="{ 'fa-spin': isSyncing }"></i>
      <span>{{ isSyncing ? 'Syncing...' : 'Refresh' }}</span>
    </button>

    <!-- Sync Settings Button (if controls are hidden, show settings toggle) -->
    <button 
      v-if="!showControls || (showControls && isAutoRefreshEnabled)"
      @click="toggleSettings"
      class="settings-btn"
      :class="{ 'active': showSettings }"
      title="Sync settings"
    >
      <i class="fas fa-cog"></i>
    </button>
  </div>

  <!-- Sync Settings (Teleported to body to avoid clipping) -->
  <Teleport to="body">
    <div v-if="showSettings" class="settings-overlay" @click="closeSettings">
      <div class="sync-settings-modal" @click.stop ref="settingsModal">
        <div class="settings-header">
          <h4>Sync Settings</h4>
          <button @click="closeSettings" class="close-btn">
            <i class="fas fa-times"></i>
          </button>
        </div>
        
        <div class="settings-content">
          <div class="setting-item">
            <label class="setting-label">
              <input 
                type="checkbox" 
                v-model="isAutoRefreshEnabled"
                @change="handleAutoRefreshToggle"
              />
              <span>Enable auto-refresh</span>
            </label>
          </div>
          
          <div class="setting-item" v-if="isAutoRefreshEnabled">
            <label class="setting-label">Refresh interval</label>
            <select v-model="refreshInterval" @change="handleIntervalChange" class="setting-select">
              <option value="30000">30 seconds</option>
              <option value="60000">1 minute</option>
              <option value="300000">5 minutes</option>
              <option value="600000">10 minutes</option>
            </select>
          </div>
          
          <div class="setting-item">
            <label class="setting-label">
              <input 
                type="checkbox" 
                v-model="syncOnFocus"
                @change="handleSyncOnFocusToggle"
              />
              <span>Sync when tab becomes active</span>
            </label>
          </div>
          
          <div class="setting-item">
            <label class="setting-label">
              <input 
                type="checkbox" 
                v-model="showNotifications"
                @change="handleNotificationToggle"
              />
              <span>Show sync notifications</span>
            </label>
          </div>
          
          <div class="setting-item">
            <button @click="manualRefresh" class="refresh-all-btn" :disabled="isSyncing">
              <i class="fas fa-sync-alt" :class="{ 'fa-spin': isSyncing }"></i>
              <span>{{ isSyncing ? 'Refreshing...' : 'Refresh Now' }}</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script>
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue'

export default {
  name: 'RealTimeData',
  props: {
    refreshFunction: {
      type: Function,
      required: true
    },
    refreshInterval: {
      type: Number,
      default: 60000 // 1 minute
    },
    autoRefreshEnabled: {
      type: Boolean,
      default: true
    },
    showControls: {
      type: Boolean,
      default: true
    }
  },
  emits: ['refresh-start', 'refresh-complete', 'refresh-error', 'settings-changed'],
  setup(props, { emit }) {
    // Reactive state
    const isAutoRefreshEnabled = ref(props.autoRefreshEnabled)
    const refreshInterval = ref(props.refreshInterval)
    const isSyncing = ref(false)
    const hasError = ref(false)
    const isPaused = ref(false)
    const lastSyncTime = ref(null)
    const syncProgress = ref(0)
    const showSettings = ref(false)
    const syncOnFocus = ref(true)
    const showNotifications = ref(true)
    const retryCount = ref(0)
    const maxRetries = 3
    
    // Timers and intervals
    let refreshTimer = null
    let progressTimer = null
    let retryTimer = null
    
    // Network status
    const isOnline = ref(navigator.onLine)
    
    // Computed properties
    const getStatusText = () => {
      if (isSyncing.value) return 'Syncing...'
      if (hasError.value) return 'Sync failed'
      if (isPaused.value) return 'Paused'
      if (!isOnline.value) return 'Offline'
      return 'Live'
    }
    
    const getLastSyncText = () => {
      if (!lastSyncTime.value) return 'Never synced'
      
      const now = new Date()
      const diff = now - lastSyncTime.value
      const minutes = Math.floor(diff / 60000)
      const seconds = Math.floor((diff % 60000) / 1000)
      
      if (minutes > 0) return `${minutes}m ago`
      if (seconds > 30) return `${seconds}s ago`
      return 'Just now'
    }

    // Auto-refresh management
    const startAutoRefresh = () => {
      if (!isAutoRefreshEnabled.value || refreshTimer) return
      
      refreshTimer = setInterval(() => {
        if (!isPaused.value && isOnline.value && !isSyncing.value) {
          performRefresh()
        }
      }, refreshInterval.value)
    }

    const stopAutoRefresh = () => {
      if (refreshTimer) {
        clearInterval(refreshTimer)
        refreshTimer = null
      }
    }

    const resetAutoRefresh = () => {
      stopAutoRefresh()
      if (isAutoRefreshEnabled.value) {
        startAutoRefresh()
      }
    }

    // Refresh functionality
    const performRefresh = async (isManual = false) => {
      if (isSyncing.value) return
      
      try {
        isSyncing.value = true
        hasError.value = false
        syncProgress.value = 0
        retryCount.value = 0
        
        emit('refresh-start', { isManual })
        
        // Show progress animation
        startProgressAnimation()
        
        // Perform the actual refresh
        await props.refreshFunction()
        
        // Update sync status
        lastSyncTime.value = new Date()
        syncProgress.value = 100
        
        // Show success notification
        if (showNotifications.value && isManual) {
          window.notify?.success('Data Synced', 'Your dashboard has been updated with the latest data')
        }
        
        emit('refresh-complete', { 
          isManual, 
          timestamp: lastSyncTime.value 
        })
        
        // Auto-hide progress after completion
        setTimeout(() => {
          syncProgress.value = 0
        }, 1000)
        
      } catch (error) {
        console.error('Refresh failed:', error)
        hasError.value = true
        
        // Show error notification
        if (showNotifications.value) {
          window.notify?.error('Sync Failed', 'Failed to refresh data. Will retry automatically.')
        }
        
        emit('refresh-error', { error, isManual })
        
        // Retry logic for auto-refresh
        if (!isManual && retryCount.value < maxRetries) {
          scheduleRetry()
        }
        
      } finally {
        isSyncing.value = false
        stopProgressAnimation()
      }
    }

    const manualRefresh = () => {
      performRefresh(true)
    }

    const scheduleRetry = () => {
      retryCount.value++
      const retryDelay = Math.min(1000 * Math.pow(2, retryCount.value), 30000) // Exponential backoff
      
      retryTimer = setTimeout(() => {
        if (isOnline.value && !isSyncing.value) {
          performRefresh()
        }
      }, retryDelay)
    }

    // Progress animation
    const startProgressAnimation = () => {
      let progress = 0
      progressTimer = setInterval(() => {
        progress += Math.random() * 15
        if (progress > 90) progress = 90 // Don't complete until actual completion
        syncProgress.value = Math.min(progress, 90)
      }, 200)
    }

    const stopProgressAnimation = () => {
      if (progressTimer) {
        clearInterval(progressTimer)
        progressTimer = null
      }
    }

    // Event handlers
    const toggleAutoRefresh = () => {
      if (hasError.value) {
        manualRefresh()
        return
      }
      
      showSettings.value = !showSettings.value
    }

    const toggleSettings = () => {
      showSettings.value = !showSettings.value
    }

    const closeSettings = () => {
      showSettings.value = false
    }

    const handleAutoRefreshToggle = () => {
      emit('settings-changed', { autoRefresh: isAutoRefreshEnabled.value })
      
      if (isAutoRefreshEnabled.value) {
        startAutoRefresh()
        if (showNotifications.value) {
          window.notify?.info('Auto-refresh Enabled', 'Dashboard will update automatically')
        }
      } else {
        stopAutoRefresh()
        if (showNotifications.value) {
          window.notify?.info('Auto-refresh Disabled', 'Manual refresh required')
        }
      }
    }

    const handleIntervalChange = () => {
      emit('settings-changed', { refreshInterval: refreshInterval.value })
      resetAutoRefresh()
    }

    const handleSyncOnFocusToggle = () => {
      emit('settings-changed', { syncOnFocus: syncOnFocus.value })
    }

    const handleNotificationToggle = () => {
      emit('settings-changed', { showNotifications: showNotifications.value })
    }

    // Visibility change handling
    const handleVisibilityChange = () => {
      if (document.hidden) {
        isPaused.value = true
        stopAutoRefresh()
      } else {
        isPaused.value = false
        
        // Sync when tab becomes active if enabled
        if (syncOnFocus.value && isOnline.value) {
          performRefresh()
        }
        
        // Restart auto-refresh
        if (isAutoRefreshEnabled.value) {
          startAutoRefresh()
        }
      }
    }

    // Network status handling
    const handleOnline = () => {
      isOnline.value = true
      hasError.value = false
      
      if (showNotifications.value) {
        window.notify?.success('Back Online', 'Resuming data synchronization')
      }
      
      // Immediate sync when coming back online
      if (isAutoRefreshEnabled.value) {
        performRefresh()
      }
    }

    const handleOffline = () => {
      isOnline.value = false
      stopAutoRefresh()
      
      if (showNotifications.value) {
        window.notify?.warning('Offline', 'Data sync paused until connection is restored')
      }
    }

    // Watch for prop changes
    watch(() => props.autoRefreshEnabled, (newValue) => {
      isAutoRefreshEnabled.value = newValue
      handleAutoRefreshToggle()
    })

    watch(() => props.refreshInterval, (newValue) => {
      refreshInterval.value = newValue
      resetAutoRefresh()
    })

    // Lifecycle
    onMounted(() => {
      // Start auto-refresh if enabled
      if (isAutoRefreshEnabled.value) {
        startAutoRefresh()
      }
      
      // Set up event listeners
      document.addEventListener('visibilitychange', handleVisibilityChange)
      window.addEventListener('online', handleOnline)
      window.addEventListener('offline', handleOffline)
      
      // Initial sync
      nextTick(() => {
        if (isOnline.value) {
          performRefresh()
        }
      })
    })

    onUnmounted(() => {
      // Clean up timers
      stopAutoRefresh()
      stopProgressAnimation()
      
      if (retryTimer) {
        clearTimeout(retryTimer)
      }
      
      // Remove event listeners
      document.removeEventListener('visibilitychange', handleVisibilityChange)
      window.removeEventListener('online', handleOnline)
      window.removeEventListener('offline', handleOffline)
    })

    // Public API
    const pauseSync = () => {
      isPaused.value = true
      stopAutoRefresh()
    }

    const resumeSync = () => {
      isPaused.value = false
      if (isAutoRefreshEnabled.value) {
        startAutoRefresh()
      }
    }

    const forceSync = () => {
      hasError.value = false
      performRefresh(true)
    }

    return {
      // State
      isAutoRefreshEnabled,
      refreshInterval,
      isSyncing,
      hasError,
      isPaused,
      lastSyncTime,
      syncProgress,
      showSettings,
      syncOnFocus,
      showNotifications,
      isOnline,
      
      // Computed
      getStatusText,
      getLastSyncText,
      
      // Methods
      manualRefresh,
      toggleAutoRefresh,
      closeSettings,
      handleAutoRefreshToggle,
      handleIntervalChange,
      handleSyncOnFocusToggle,
      handleNotificationToggle,
      
      // Public API
      pauseSync,
      resumeSync,
      forceSync
    }
  }
}
</script>

<style scoped>
.real-time-data {
  position: relative;
}

/* Refresh Indicator */
.refresh-indicator {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1rem;
  background: rgba(255, 255, 255, 0.9);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.3);
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
  min-width: 200px;
}

.refresh-indicator:hover {
  background: rgba(255, 255, 255, 1);
  transform: translateY(-1px);
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
}

.refresh-indicator.syncing {
  border-color: rgba(59, 130, 246, 0.3);
  background: rgba(59, 130, 246, 0.05);
}

.refresh-indicator.error {
  border-color: rgba(239, 68, 68, 0.3);
  background: rgba(239, 68, 68, 0.05);
}

.refresh-indicator.paused {
  border-color: rgba(156, 163, 175, 0.3);
  background: rgba(156, 163, 175, 0.05);
  opacity: 0.7;
}

.indicator-icon {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.875rem;
  color: white;
  flex-shrink: 0;
  background: linear-gradient(135deg, #10b981, #059669);
  transition: all 0.3s ease;
}

.refresh-indicator.syncing .indicator-icon {
  background: linear-gradient(135deg, #3b82f6, #2563eb);
}

.refresh-indicator.error .indicator-icon {
  background: linear-gradient(135deg, #ef4444, #dc2626);
}

.refresh-indicator.paused .indicator-icon {
  background: linear-gradient(135deg, #6b7280, #4b5563);
}

.indicator-content {
  flex: 1;
  min-width: 0;
}

.status-text {
  font-weight: 600;
  color: #1f2937;
  font-size: 0.875rem;
  margin-bottom: 0.125rem;
}

.last-sync {
  font-size: 0.75rem;
  color: #6b7280;
  font-weight: 500;
}

.sync-progress {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  height: 2px;
  background: rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.progress-bar {
  height: 100%;
  background: linear-gradient(90deg, #3b82f6, #1d4ed8);
  transition: width 0.3s ease;
  position: relative;
}

.progress-bar::after {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
  animation: progressShimmer 1.5s ease-in-out infinite;
}

@keyframes progressShimmer {
  0% { transform: translateX(-100%); }
  100% { transform: translateX(100%); }
}

/* Manual Refresh Button */
.manual-refresh-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 12px;
  font-weight: 600;
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 4px 16px rgba(102, 126, 234, 0.3);
  min-width: 120px;
  justify-content: center;
}

.manual-refresh-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(102, 126, 234, 0.4);
}

.manual-refresh-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
  transform: none;
}

/* Sync Settings */
/* Settings Button */
.settings-btn {
  padding: 0.5rem;
  border: 1px solid rgba(255, 255, 255, 0.3);
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  color: #374151;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 36px;
  height: 36px;
}

.settings-btn:hover {
  background: rgba(255, 255, 255, 0.2);
  border-color: rgba(255, 255, 255, 0.4);
  transform: translateY(-1px);
}

.settings-btn.active {
  background: rgba(59, 130, 246, 0.2);
  border-color: rgba(59, 130, 246, 0.4);
  color: #3b82f6;
}

/* Settings Overlay */
.settings-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.3);
  backdrop-filter: blur(4px);
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
}

/* Settings Modal */
.sync-settings-modal {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.3);
  border-radius: 16px;
  box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
  width: 100%;
  max-width: 400px;
  overflow: hidden;
  animation: settingsSlideIn 0.3s ease-out;
}

@keyframes settingsSlideIn {
  from {
    opacity: 0;
    transform: translateY(-20px) scale(0.95);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

.settings-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.25rem;
  border-bottom: 1px solid rgba(0, 0, 0, 0.1);
}

.settings-header h4 {
  margin: 0;
  font-size: 1rem;
  font-weight: 600;
  color: #1f2937;
}

.close-btn {
  width: 28px;
  height: 28px;
  border: none;
  background: transparent;
  color: #6b7280;
  cursor: pointer;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
  font-size: 0.875rem;
}

.close-btn:hover {
  background: rgba(0, 0, 0, 0.1);
  color: #374151;
}

.settings-content {
  padding: 1rem 1.25rem;
}

.setting-item {
  margin-bottom: 1rem;
}

.setting-item:last-child {
  margin-bottom: 0;
}

.setting-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
  color: #374151;
  font-weight: 500;
  cursor: pointer;
}

.setting-label input[type="checkbox"] {
  width: 16px;
  height: 16px;
  accent-color: #667eea;
  cursor: pointer;
}

.setting-select {
  width: 100%;
  padding: 0.5rem 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  background: white;
  color: #374151;
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.setting-select:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.refresh-all-btn {
  width: 100%;
  padding: 0.75rem 1rem;
  border: none;
  border-radius: 8px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.refresh-all-btn:hover:not(:disabled) {
  background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%);
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.refresh-all-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

/* Mobile Styles */
@media (max-width: 768px) {
  .refresh-indicator {
    padding: 0.625rem 0.875rem;
    min-width: 160px;
  }
  
  .indicator-icon {
    width: 28px;
    height: 28px;
    font-size: 0.8rem;
  }
  
  .status-text {
    font-size: 0.8rem;
  }
  
  .last-sync {
    font-size: 0.7rem;
  }
  
  .manual-refresh-btn {
    padding: 0.625rem 0.875rem;
    font-size: 0.8rem;
    min-width: 100px;
  }
  
  .sync-settings-modal {
    max-width: 90vw;
    width: 320px;
  }
  
  .settings-btn {
    width: 32px;
    height: 32px;
  }
}

/* Dark Mode */
@media (prefers-color-scheme: dark) {
  .refresh-indicator {
    background: rgba(30, 41, 59, 0.9);
    border-color: rgba(71, 85, 105, 0.3);
  }
  
  .refresh-indicator:hover {
    background: rgba(30, 41, 59, 1);
  }
  
  .status-text {
    color: #f1f5f9;
  }
  
  .last-sync {
    color: #94a3b8;
  }
  
  .sync-settings-modal {
    background: rgba(30, 41, 59, 0.95);
    border-color: rgba(71, 85, 105, 0.3);
  }
  
  .settings-btn {
    background: rgba(30, 41, 59, 0.6);
    border-color: rgba(71, 85, 105, 0.4);
    color: #e2e8f0;
  }
  
  .settings-btn:hover {
    background: rgba(30, 41, 59, 0.8);
    border-color: rgba(71, 85, 105, 0.6);
  }
  
  .settings-header h4 {
    color: #f1f5f9;
  }
  
  .setting-label {
    color: #cbd5e1;
  }
  
  .setting-select {
    background: rgba(51, 65, 85, 0.8);
    border-color: rgba(71, 85, 105, 0.5);
    color: #f1f5f9;
  }
}

/* Animations */
@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.6;
  }
}

.refresh-indicator.syncing .indicator-icon {
  animation: pulse 2s ease-in-out infinite;
}

/* Connection status indicator */
.refresh-indicator::before {
  content: '';
  position: absolute;
  top: 8px;
  right: 8px;
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #10b981;
  opacity: 0.8;
}

.refresh-indicator.error::before {
  background: #ef4444;
  animation: pulse 1s ease-in-out infinite;
}

.refresh-indicator.paused::before {
  background: #6b7280;
}

/* Accessibility */
@media (prefers-reduced-motion: reduce) {
  .refresh-indicator,
  .manual-refresh-btn,
  .sync-settings {
    animation: none;
    transition: none;
  }
  
  .progress-bar::after {
    animation: none;
  }
  
  .refresh-indicator.syncing .indicator-icon {
    animation: none;
  }
}

/* High contrast mode */
@media (prefers-contrast: high) {
  .refresh-indicator,
  .sync-settings {
    border: 2px solid;
  }
  
  .manual-refresh-btn {
    border: 2px solid white;
  }
}
</style>