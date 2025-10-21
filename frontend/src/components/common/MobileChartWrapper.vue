<template>
  <div class="mobile-chart-wrapper" :class="{ fullscreen: isFullscreen }">
    <!-- Chart Header with Mobile Actions -->
    <div class="mobile-chart-header">
      <div class="chart-info">
        <h3 class="chart-title">{{ title }}</h3>
        <p class="chart-subtitle" v-if="subtitle">{{ subtitle }}</p>
      </div>

      <!-- Mobile Actions -->
      <div class="mobile-actions">
        <button
          @click="toggleFullscreen"
          class="action-btn fullscreen-btn"
          :aria-label="isFullscreen ? 'Exit fullscreen' : 'View fullscreen'"
        >
          <i :class="isFullscreen ? 'fas fa-compress' : 'fas fa-expand'"></i>
        </button>

        <button @click="shareChart" class="action-btn share-btn" aria-label="Share chart" v-if="showShare">
          <i class="fas fa-share-alt"></i>
        </button>

        <button @click="downloadChart" class="action-btn download-btn" aria-label="Download chart" v-if="showDownload">
          <i class="fas fa-download"></i>
        </button>
      </div>
    </div>

    <!-- Chart Container with Touch Gestures -->
    <div
      class="chart-container"
      @touchstart="handleTouchStart"
      @touchmove="handleTouchMove"
      @touchend="handleTouchEnd"
      @wheel="handleWheel"
      :style="{ transform: `scale(${zoomLevel})` }"
    >
      <slot />
    </div>

    <!-- Mobile Controls -->
    <div class="mobile-controls" v-if="isMobile">
      <!-- Zoom Controls -->
      <div class="zoom-controls" v-if="allowZoom">
        <button @click="zoomOut" :disabled="zoomLevel <= minZoom" class="zoom-btn" aria-label="Zoom out">
          <i class="fas fa-minus"></i>
        </button>

        <div class="zoom-indicator">{{ Math.round(zoomLevel * 100) }}%</div>

        <button @click="zoomIn" :disabled="zoomLevel >= maxZoom" class="zoom-btn" aria-label="Zoom in">
          <i class="fas fa-plus"></i>
        </button>

        <button @click="resetZoom" class="zoom-btn reset-btn" aria-label="Reset zoom">
          <i class="fas fa-undo"></i>
        </button>
      </div>

      <!-- Gesture Hints -->
      <div class="gesture-hints" v-if="showHints">
        <div class="hint">
          <i class="fas fa-hand-paper"></i>
          <span>Pinch to zoom</span>
        </div>
        <div class="hint">
          <i class="fas fa-arrows-alt"></i>
          <span>Drag to pan</span>
        </div>
      </div>
    </div>

    <!-- Fullscreen Overlay -->
    <div v-if="isFullscreen" class="fullscreen-overlay" @click="exitFullscreen">
      <div class="fullscreen-content" @click.stop>
        <div class="fullscreen-header">
          <h2>{{ title }}</h2>
          <button @click="exitFullscreen" class="close-btn">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <div class="fullscreen-chart">
          <slot name="fullscreen" />
        </div>
      </div>
    </div>
  </div>
</template>

<script>
  import { ref, computed, onMounted, onUnmounted } from 'vue'

  export default {
    name: 'MobileChartWrapper',
    props: {
      title: {
        type: String,
        required: true
      },
      subtitle: {
        type: String,
        default: ''
      },
      allowZoom: {
        type: Boolean,
        default: true
      },
      showShare: {
        type: Boolean,
        default: false
      },
      showDownload: {
        type: Boolean,
        default: false
      },
      showHints: {
        type: Boolean,
        default: true
      },
      minZoom: {
        type: Number,
        default: 0.5
      },
      maxZoom: {
        type: Number,
        default: 3
      }
    },
    emits: ['share', 'download', 'zoom-change'],
    setup(props, { emit }) {
      // Reactive state
      const isFullscreen = ref(false)
      const zoomLevel = ref(1)
      const isMobile = ref(false)
      const isPanning = ref(false)
      const lastTouchDistance = ref(0)
      const panOffset = ref({ x: 0, y: 0 })
      const lastPanPoint = ref({ x: 0, y: 0 })

      // Mobile detection
      const detectMobile = () => {
        isMobile.value = window.innerWidth <= 768 || 'ontouchstart' in window
      }

      // Touch gesture handlers
      const handleTouchStart = event => {
        if (event.touches.length === 2) {
          // Two finger touch - prepare for pinch zoom
          const touch1 = event.touches[0]
          const touch2 = event.touches[1]
          lastTouchDistance.value = Math.hypot(touch2.clientX - touch1.clientX, touch2.clientY - touch1.clientY)
        } else if (event.touches.length === 1) {
          // Single finger touch - prepare for pan
          isPanning.value = true
          lastPanPoint.value = {
            x: event.touches[0].clientX,
            y: event.touches[0].clientY
          }
        }
      }

      const handleTouchMove = event => {
        event.preventDefault()

        if (event.touches.length === 2 && props.allowZoom) {
          // Pinch zoom
          const touch1 = event.touches[0]
          const touch2 = event.touches[1]
          const currentDistance = Math.hypot(touch2.clientX - touch1.clientX, touch2.clientY - touch1.clientY)

          if (lastTouchDistance.value > 0) {
            const scale = currentDistance / lastTouchDistance.value
            const newZoom = Math.max(props.minZoom, Math.min(props.maxZoom, zoomLevel.value * scale))
            setZoom(newZoom)
          }

          lastTouchDistance.value = currentDistance
        } else if (event.touches.length === 1 && isPanning.value) {
          // Pan gesture
          const currentPoint = {
            x: event.touches[0].clientX,
            y: event.touches[0].clientY
          }

          panOffset.value = {
            x: panOffset.value.x + (currentPoint.x - lastPanPoint.value.x),
            y: panOffset.value.y + (currentPoint.y - lastPanPoint.value.y)
          }

          lastPanPoint.value = currentPoint
        }
      }

      const handleTouchEnd = () => {
        isPanning.value = false
        lastTouchDistance.value = 0
      }

      // Mouse wheel zoom
      const handleWheel = event => {
        if (!props.allowZoom) return

        event.preventDefault()
        const delta = event.deltaY > 0 ? -0.1 : 0.1
        const newZoom = Math.max(props.minZoom, Math.min(props.maxZoom, zoomLevel.value + delta))
        setZoom(newZoom)
      }

      // Zoom methods
      const setZoom = newZoom => {
        zoomLevel.value = newZoom
        emit('zoom-change', newZoom)
      }

      const zoomIn = () => {
        const newZoom = Math.min(props.maxZoom, zoomLevel.value + 0.2)
        setZoom(newZoom)
      }

      const zoomOut = () => {
        const newZoom = Math.max(props.minZoom, zoomLevel.value - 0.2)
        setZoom(newZoom)
      }

      const resetZoom = () => {
        setZoom(1)
        panOffset.value = { x: 0, y: 0 }
      }

      // Fullscreen methods
      const toggleFullscreen = () => {
        isFullscreen.value = !isFullscreen.value
      }

      const exitFullscreen = () => {
        isFullscreen.value = false
      }

      // Action methods
      const shareChart = () => {
        emit('share')
      }

      const downloadChart = () => {
        emit('download')
      }

      // Lifecycle
      onMounted(() => {
        detectMobile()
        window.addEventListener('resize', detectMobile)
      })

      onUnmounted(() => {
        window.removeEventListener('resize', detectMobile)
      })

      return {
        // State
        isFullscreen,
        zoomLevel,
        isMobile,
        isPanning,
        panOffset,

        // Methods
        handleTouchStart,
        handleTouchMove,
        handleTouchEnd,
        handleWheel,
        zoomIn,
        zoomOut,
        resetZoom,
        toggleFullscreen,
        exitFullscreen,
        shareChart,
        downloadChart
      }
    }
  }
</script>

<style scoped>
  .mobile-chart-wrapper {
    position: relative;
    width: 100%;
    height: 100%;
    overflow: hidden;
  }

  .mobile-chart-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
    padding: 0 0.5rem;
  }

  .chart-info {
    flex: 1;
  }

  .chart-title {
    font-size: 1.125rem;
    font-weight: 700;
    color: #1f2937;
    margin: 0 0 0.25rem 0;
  }

  .chart-subtitle {
    font-size: 0.875rem;
    color: #6b7280;
    margin: 0;
  }

  .mobile-actions {
    display: flex;
    gap: 0.5rem;
    flex-shrink: 0;
  }

  .action-btn {
    width: 36px;
    height: 36px;
    border: none;
    border-radius: 8px;
    background: rgba(102, 126, 234, 0.1);
    color: #667eea;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 0.875rem;
  }

  .action-btn:hover {
    background: rgba(102, 126, 234, 0.2);
    transform: translateY(-1px);
  }

  .action-btn:active {
    transform: translateY(0) scale(0.95);
  }

  .chart-container {
    width: 100%;
    height: 300px;
    transition: transform 0.3s ease;
    transform-origin: center center;
    touch-action: none;
    user-select: none;
  }

  .mobile-controls {
    margin-top: 1rem;
    padding: 0 0.5rem;
  }

  .zoom-controls {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    padding: 0.75rem 1rem;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
    margin-bottom: 1rem;
  }

  .zoom-btn {
    width: 32px;
    height: 32px;
    border: none;
    border-radius: 8px;
    background: #667eea;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 0.75rem;
  }

  .zoom-btn:hover:not(:disabled) {
    background: #5a6fd8;
    transform: translateY(-1px);
  }

  .zoom-btn:disabled {
    background: #d1d5db;
    cursor: not-allowed;
  }

  .zoom-btn.reset-btn {
    background: #6b7280;
  }

  .zoom-btn.reset-btn:hover:not(:disabled) {
    background: #4b5563;
  }

  .zoom-indicator {
    font-size: 0.875rem;
    font-weight: 600;
    color: #374151;
    min-width: 50px;
    text-align: center;
  }

  .gesture-hints {
    display: flex;
    justify-content: center;
    gap: 1.5rem;
    opacity: 0.7;
  }

  .hint {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.75rem;
    color: #6b7280;
    font-weight: 500;
  }

  .hint i {
    font-size: 0.875rem;
    color: #9ca3af;
  }

  /* Fullscreen styles */
  .fullscreen {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 1000;
    background: rgba(0, 0, 0, 0.95);
  }

  .fullscreen-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.95);
    backdrop-filter: blur(20px);
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
  }

  .fullscreen-content {
    background: white;
    border-radius: 20px;
    width: 100%;
    max-width: 1200px;
    max-height: 90vh;
    overflow: hidden;
    display: flex;
    flex-direction: column;
  }

  .fullscreen-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem 2rem;
    border-bottom: 1px solid #e5e7eb;
  }

  .fullscreen-header h2 {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 700;
    color: #1f2937;
  }

  .close-btn {
    width: 40px;
    height: 40px;
    border: none;
    border-radius: 10px;
    background: #f3f4f6;
    color: #6b7280;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
  }

  .close-btn:hover {
    background: #e5e7eb;
    color: #374151;
  }

  .fullscreen-chart {
    flex: 1;
    padding: 2rem;
    min-height: 400px;
  }

  /* Mobile-specific styles */
  @media (max-width: 768px) {
    .mobile-chart-header {
      flex-direction: column;
      gap: 1rem;
      align-items: stretch;
    }

    .mobile-actions {
      justify-content: center;
    }

    .action-btn {
      width: 44px;
      height: 44px;
      font-size: 1rem;
    }

    .chart-container {
      height: 250px;
    }

    .zoom-controls {
      gap: 1rem;
      padding: 1rem 1.25rem;
    }

    .zoom-btn {
      width: 40px;
      height: 40px;
      font-size: 0.875rem;
    }

    .gesture-hints {
      flex-direction: column;
      gap: 0.75rem;
      align-items: center;
    }

    .fullscreen-overlay {
      padding: 1rem;
    }

    .fullscreen-header {
      padding: 1rem 1.5rem;
    }

    .fullscreen-chart {
      padding: 1.5rem;
      min-height: 300px;
    }
  }

  @media (max-width: 480px) {
    .chart-title {
      font-size: 1rem;
    }

    .chart-subtitle {
      font-size: 0.8rem;
    }

    .chart-container {
      height: 200px;
    }

    .zoom-controls {
      gap: 0.75rem;
      padding: 0.75rem 1rem;
    }

    .zoom-btn {
      width: 36px;
      height: 36px;
    }

    .zoom-indicator {
      font-size: 0.8rem;
      min-width: 45px;
    }
  }

  /* Dark mode support */
  @media (prefers-color-scheme: dark) {
    .chart-title {
      color: #f9fafb;
    }

    .chart-subtitle {
      color: #d1d5db;
    }

    .zoom-controls {
      background: rgba(30, 41, 59, 0.9);
    }

    .zoom-indicator {
      color: #e5e7eb;
    }

    .hint {
      color: #9ca3af;
    }

    .fullscreen-content {
      background: #1f2937;
    }

    .fullscreen-header {
      border-bottom-color: #374151;
    }

    .fullscreen-header h2 {
      color: #f9fafb;
    }

    .close-btn {
      background: #374151;
      color: #9ca3af;
    }

    .close-btn:hover {
      background: #4b5563;
      color: #d1d5db;
    }
  }
</style>
