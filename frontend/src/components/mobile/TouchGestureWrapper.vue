<template>
  <div 
    class="touch-gesture-wrapper"
    @touchstart="handleTouchStart"
    @touchmove="handleTouchMove"
    @touchend="handleTouchEnd"
    @mousedown="handleMouseDown"
    @mousemove="handleMouseMove"
    @mouseup="handleMouseUp"
    :class="{ 'gesture-active': isGestureActive }"
  >
    <slot />
    
    <!-- Swipe Indicators -->
    <div v-if="showSwipeIndicators" class="swipe-indicators">
      <div 
        v-for="indicator in swipeIndicators"
        :key="indicator.direction"
        :class="['swipe-indicator', indicator.direction, { 'active': activeSwipeDirection === indicator.direction }]"
      >
        <i :class="indicator.icon"></i>
        <span>{{ indicator.label }}</span>
      </div>
    </div>
    
    <!-- Pull to Refresh -->
    <div 
      v-if="enablePullToRefresh && pullDistance > 0"
      class="pull-to-refresh"
      :class="{ 'triggered': pullTriggered }"
      :style="{ transform: `translateY(${Math.min(pullDistance, maxPullDistance)}px)` }"
    >
      <div class="pull-icon">
        <i v-if="!pullTriggered" class="fas fa-arrow-down"></i>
        <i v-else class="fas fa-sync-alt fa-spin"></i>
      </div>
      <div class="pull-text">
        {{ pullTriggered ? 'Release to refresh' : 'Pull to refresh' }}
      </div>
    </div>
    
    <!-- Long Press Indicator -->
    <div 
      v-if="showLongPressIndicator && longPressProgress > 0"
      class="long-press-indicator"
      :style="{ 
        left: `${longPressPosition.x}px`, 
        top: `${longPressPosition.y}px`,
        '--progress': longPressProgress
      }"
    >
      <div class="progress-ring">
        <svg class="progress-circle" width="60" height="60">
          <circle
            cx="30"
            cy="30"
            r="25"
            stroke="currentColor"
            stroke-width="3"
            fill="none"
            stroke-dasharray="157"
            :stroke-dashoffset="157 - (157 * longPressProgress)"
          />
        </svg>
        <i class="fas fa-hand-pointer"></i>
      </div>
    </div>
    
    <!-- Haptic Feedback Indicator -->
    <div 
      v-if="showHapticFeedback"
      class="haptic-feedback"
      :class="hapticType"
    >
      <div class="haptic-ripple"></div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted, onUnmounted } from 'vue'

export default {
  name: 'TouchGestureWrapper',
  props: {
    enableSwipe: {
      type: Boolean,
      default: true
    },
    enablePullToRefresh: {
      type: Boolean,
      default: false
    },
    enableLongPress: {
      type: Boolean,
      default: true
    },
    enableHapticFeedback: {
      type: Boolean,
      default: true
    },
    swipeThreshold: {
      type: Number,
      default: 50
    },
    longPressDelay: {
      type: Number,
      default: 500
    },
    pullRefreshThreshold: {
      type: Number,
      default: 80
    },
    showIndicators: {
      type: Boolean,
      default: true
    }
  },
  emits: [
    'swipe-left',
    'swipe-right', 
    'swipe-up',
    'swipe-down',
    'pull-refresh',
    'long-press',
    'tap',
    'double-tap',
    'pinch',
    'rotate'
  ],
  setup(props, { emit }) {
    // Touch state
    const isGestureActive = ref(false)
    const touchStartPos = ref({ x: 0, y: 0 })
    const touchCurrentPos = ref({ x: 0, y: 0 })
    const touchStartTime = ref(0)
    const isPointerDown = ref(false)
    const gestureType = ref('')
    
    // Swipe detection
    const activeSwipeDirection = ref('')
    const showSwipeIndicators = ref(false)
    
    // Pull to refresh
    const pullDistance = ref(0)
    const pullTriggered = ref(false)
    const maxPullDistance = 120
    const isPulling = ref(false)
    
    // Long press
    const longPressTimer = ref(null)
    const longPressProgress = ref(0)
    const longPressPosition = ref({ x: 0, y: 0 })
    const showLongPressIndicator = ref(false)
    const longPressAnimationFrame = ref(null)
    
    // Double tap
    const lastTapTime = ref(0)
    const tapCount = ref(0)
    
    // Haptic feedback
    const showHapticFeedback = ref(false)
    const hapticType = ref('')
    
    // Multi-touch
    const touches = ref([])
    const initialDistance = ref(0)
    const currentDistance = ref(0)
    const initialAngle = ref(0)
    const currentAngle = ref(0)
    
    // Swipe indicators configuration
    const swipeIndicators = [
      { direction: 'left', icon: 'fas fa-chevron-left', label: 'Previous' },
      { direction: 'right', icon: 'fas fa-chevron-right', label: 'Next' },
      { direction: 'up', icon: 'fas fa-chevron-up', label: 'More' },
      { direction: 'down', icon: 'fas fa-chevron-down', label: 'Refresh' }
    ]

    // Utility functions
    const getDistance = (p1, p2) => {
      return Math.sqrt(Math.pow(p2.x - p1.x, 2) + Math.pow(p2.y - p1.y, 2))
    }

    const getAngle = (p1, p2) => {
      return Math.atan2(p2.y - p1.y, p2.x - p1.x) * 180 / Math.PI
    }

    const getTouchPoint = (event) => {
      const touch = event.touches ? event.touches[0] : event
      return {
        x: touch.clientX,
        y: touch.clientY
      }
    }

    const triggerHaptic = (type = 'light') => {
      if (!props.enableHapticFeedback) return
      
      // Trigger device haptic feedback if available
      if (navigator.vibrate) {
        const patterns = {
          light: [10],
          medium: [20],
          heavy: [30],
          success: [10, 50, 10],
          error: [50, 50, 50]
        }
        navigator.vibrate(patterns[type] || patterns.light)
      }
      
      // Visual haptic feedback
      hapticType.value = type
      showHapticFeedback.value = true
      setTimeout(() => {
        showHapticFeedback.value = false
      }, 300)
    }

    // Touch event handlers
    const handleTouchStart = (event) => {
      if (!event.touches) return
      
      const point = getTouchPoint(event)
      touchStartPos.value = point
      touchCurrentPos.value = point
      touchStartTime.value = Date.now()
      isPointerDown.value = true
      isGestureActive.value = true
      
      touches.value = Array.from(event.touches).map(touch => ({
        id: touch.identifier,
        x: touch.clientX,
        y: touch.clientY
      }))
      
      // Multi-touch gesture detection
      if (touches.value.length === 2) {
        const [touch1, touch2] = touches.value
        initialDistance.value = getDistance(touch1, touch2)
        initialAngle.value = getAngle(touch1, touch2)
      }
      
      // Start long press detection
      if (props.enableLongPress && touches.value.length === 1) {
        startLongPressDetection(point)
      }
      
      // Show swipe indicators
      if (props.enableSwipe && props.showIndicators) {
        showSwipeIndicators.value = true
        setTimeout(() => {
          if (isPointerDown.value) {
            showSwipeIndicators.value = false
          }
        }, 2000)
      }
    }

    const handleTouchMove = (event) => {
      if (!isPointerDown.value || !event.touches) return
      
      event.preventDefault()
      
      const point = getTouchPoint(event)
      touchCurrentPos.value = point
      
      touches.value = Array.from(event.touches).map(touch => ({
        id: touch.identifier,
        x: touch.clientX,
        y: touch.clientY
      }))
      
      // Multi-touch gestures
      if (touches.value.length === 2) {
        handleMultiTouchGesture()
      } else if (touches.value.length === 1) {
        // Single touch gestures
        const deltaX = point.x - touchStartPos.value.x
        const deltaY = point.y - touchStartPos.value.y
        const distance = getDistance(touchStartPos.value, point)
        
        // Pull to refresh
        if (props.enablePullToRefresh && deltaY > 0 && touchStartPos.value.y < 100) {
          handlePullToRefresh(deltaY)
        }
        
        // Swipe direction indication
        if (distance > 20) {
          const absX = Math.abs(deltaX)
          const absY = Math.abs(deltaY)
          
          if (absX > absY) {
            activeSwipeDirection.value = deltaX > 0 ? 'right' : 'left'
          } else {
            activeSwipeDirection.value = deltaY > 0 ? 'down' : 'up'
          }
        }
        
        // Cancel long press if moved too much
        if (distance > 10) {
          cancelLongPress()
        }
      }
    }

    const handleTouchEnd = (event) => {
      if (!isPointerDown.value) return
      
      const endTime = Date.now()
      const duration = endTime - touchStartTime.value
      const point = touchCurrentPos.value
      const deltaX = point.x - touchStartPos.value.x
      const deltaY = point.y - touchStartPos.value.y
      const distance = getDistance(touchStartPos.value, point)
      
      // Reset states
      isPointerDown.value = false
      isGestureActive.value = false
      activeSwipeDirection.value = ''
      showSwipeIndicators.value = false
      
      // Handle pull to refresh
      if (isPulling.value) {
        handlePullToRefreshEnd()
      }
      
      // Cancel long press
      cancelLongPress()
      
      // Detect gesture type
      if (distance < 10 && duration < 300) {
        handleTap()
      } else if (distance > props.swipeThreshold) {
        handleSwipe(deltaX, deltaY)
      }
      
      touches.value = []
    }

    // Mouse event handlers (for desktop testing)
    const handleMouseDown = (event) => {
      if (event.touches) return // Ignore if touch event
      
      const point = { x: event.clientX, y: event.clientY }
      touchStartPos.value = point
      touchCurrentPos.value = point
      touchStartTime.value = Date.now()
      isPointerDown.value = true
      isGestureActive.value = true
      
      if (props.enableLongPress) {
        startLongPressDetection(point)
      }
    }

    const handleMouseMove = (event) => {
      if (!isPointerDown.value || event.touches) return
      
      const point = { x: event.clientX, y: event.clientY }
      touchCurrentPos.value = point
      
      const distance = getDistance(touchStartPos.value, point)
      if (distance > 10) {
        cancelLongPress()
      }
    }

    const handleMouseUp = (event) => {
      if (!isPointerDown.value || event.touches) return
      
      const endTime = Date.now()
      const duration = endTime - touchStartTime.value
      const point = { x: event.clientX, y: event.clientY }
      const deltaX = point.x - touchStartPos.value.x
      const deltaY = point.y - touchStartPos.value.y
      const distance = getDistance(touchStartPos.value, point)
      
      isPointerDown.value = false
      isGestureActive.value = false
      cancelLongPress()
      
      if (distance < 10 && duration < 300) {
        handleTap()
      } else if (distance > props.swipeThreshold) {
        handleSwipe(deltaX, deltaY)
      }
    }

    // Gesture handlers
    const handleSwipe = (deltaX, deltaY) => {
      const absX = Math.abs(deltaX)
      const absY = Math.abs(deltaY)
      
      if (absX > absY) {
        if (deltaX > 0) {
          emit('swipe-right', { distance: absX })
          triggerHaptic('light')
        } else {
          emit('swipe-left', { distance: absX })
          triggerHaptic('light')
        }
      } else {
        if (deltaY > 0) {
          emit('swipe-down', { distance: absY })
          triggerHaptic('light')
        } else {
          emit('swipe-up', { distance: absY })
          triggerHaptic('light')
        }
      }
    }

    const handleTap = () => {
      const now = Date.now()
      const timeSinceLastTap = now - lastTapTime.value
      
      if (timeSinceLastTap < 300) {
        // Double tap
        tapCount.value++
        if (tapCount.value === 2) {
          emit('double-tap', touchCurrentPos.value)
          triggerHaptic('medium')
          tapCount.value = 0
        }
      } else {
        // Single tap
        tapCount.value = 1
        setTimeout(() => {
          if (tapCount.value === 1) {
            emit('tap', touchCurrentPos.value)
            tapCount.value = 0
          }
        }, 300)
      }
      
      lastTapTime.value = now
    }

    const handleMultiTouchGesture = () => {
      if (touches.value.length !== 2) return
      
      const [touch1, touch2] = touches.value
      currentDistance.value = getDistance(touch1, touch2)
      currentAngle.value = getAngle(touch1, touch2)
      
      // Pinch detection
      const distanceDelta = currentDistance.value - initialDistance.value
      if (Math.abs(distanceDelta) > 10) {
        const scale = currentDistance.value / initialDistance.value
        emit('pinch', { scale, delta: distanceDelta })
      }
      
      // Rotation detection
      const angleDelta = currentAngle.value - initialAngle.value
      if (Math.abs(angleDelta) > 5) {
        emit('rotate', { angle: angleDelta, delta: angleDelta })
      }
    }

    // Pull to refresh
    const handlePullToRefresh = (deltaY) => {
      isPulling.value = true
      pullDistance.value = Math.min(deltaY * 0.5, maxPullDistance)
      
      if (pullDistance.value > props.pullRefreshThreshold && !pullTriggered.value) {
        pullTriggered.value = true
        triggerHaptic('medium')
      } else if (pullDistance.value <= props.pullRefreshThreshold && pullTriggered.value) {
        pullTriggered.value = false
      }
    }

    const handlePullToRefreshEnd = () => {
      if (pullTriggered.value) {
        emit('pull-refresh')
        triggerHaptic('success')
      }
      
      // Animate back to original position
      const startDistance = pullDistance.value
      const animateBack = () => {
        pullDistance.value = Math.max(0, pullDistance.value - 5)
        if (pullDistance.value > 0) {
          requestAnimationFrame(animateBack)
        } else {
          isPulling.value = false
          pullTriggered.value = false
        }
      }
      animateBack()
    }

    // Long press
    const startLongPressDetection = (position) => {
      longPressPosition.value = position
      showLongPressIndicator.value = true
      longPressProgress.value = 0
      
      const startTime = Date.now()
      const animate = () => {
        if (!isPointerDown.value) {
          cancelLongPress()
          return
        }
        
        const elapsed = Date.now() - startTime
        longPressProgress.value = Math.min(elapsed / props.longPressDelay, 1)
        
        if (longPressProgress.value >= 1) {
          handleLongPress()
        } else {
          longPressAnimationFrame.value = requestAnimationFrame(animate)
        }
      }
      
      longPressAnimationFrame.value = requestAnimationFrame(animate)
    }

    const handleLongPress = () => {
      emit('long-press', longPressPosition.value)
      triggerHaptic('heavy')
      cancelLongPress()
    }

    const cancelLongPress = () => {
      if (longPressAnimationFrame.value) {
        cancelAnimationFrame(longPressAnimationFrame.value)
        longPressAnimationFrame.value = null
      }
      showLongPressIndicator.value = false
      longPressProgress.value = 0
    }

    // Lifecycle
    onMounted(() => {
      // Prevent default touch behaviors that might interfere
      document.addEventListener('touchmove', (e) => {
        if (isGestureActive.value) {
          e.preventDefault()
        }
      }, { passive: false })
    })

    onUnmounted(() => {
      cancelLongPress()
    })

    return {
      // State
      isGestureActive,
      activeSwipeDirection,
      showSwipeIndicators,
      pullDistance,
      pullTriggered,
      maxPullDistance,
      longPressProgress,
      longPressPosition,
      showLongPressIndicator,
      showHapticFeedback,
      hapticType,
      
      // Data
      swipeIndicators,
      
      // Methods
      handleTouchStart,
      handleTouchMove,
      handleTouchEnd,
      handleMouseDown,
      handleMouseMove,
      handleMouseUp
    }
  }
}
</script>

<style scoped>
.touch-gesture-wrapper {
  position: relative;
  width: 100%;
  height: 100%;
  overflow: hidden;
  user-select: none;
  -webkit-user-select: none;
  -webkit-touch-callout: none;
}

.touch-gesture-wrapper.gesture-active {
  cursor: grabbing;
}

/* Swipe Indicators */
.swipe-indicators {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  display: flex;
  gap: 2rem;
  pointer-events: none;
  z-index: 100;
  opacity: 0.7;
  animation: indicatorsFadeIn 0.3s ease-out;
}

@keyframes indicatorsFadeIn {
  from {
    opacity: 0;
    transform: translate(-50%, -50%) scale(0.8);
  }
  to {
    opacity: 0.7;
    transform: translate(-50%, -50%) scale(1);
  }
}

.swipe-indicator {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
  padding: 1rem;
  background: rgba(0, 0, 0, 0.7);
  backdrop-filter: blur(10px);
  border-radius: 12px;
  color: white;
  font-size: 0.8rem;
  font-weight: 500;
  transition: all 0.2s ease;
  opacity: 0.5;
}

.swipe-indicator.active {
  opacity: 1;
  transform: scale(1.1);
  background: rgba(102, 126, 234, 0.8);
}

.swipe-indicator i {
  font-size: 1.5rem;
}

.swipe-indicator.left {
  transform: translateX(-2rem);
}

.swipe-indicator.right {
  transform: translateX(2rem);
}

.swipe-indicator.up {
  transform: translateY(-2rem);
}

.swipe-indicator.down {
  transform: translateY(2rem);
}

/* Pull to Refresh */
.pull-to-refresh {
  position: absolute;
  top: -60px;
  left: 50%;
  transform: translateX(-50%);
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
  padding: 1rem;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border-radius: 0 0 12px 12px;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
  z-index: 101;
  transition: all 0.3s ease;
}

.pull-to-refresh.triggered {
  background: rgba(102, 126, 234, 0.95);
  color: white;
}

.pull-icon {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: rgba(102, 126, 234, 0.1);
  display: flex;
  align-items: center;
  justify-content: center;
  color: #667eea;
  font-size: 1rem;
  transition: all 0.3s ease;
}

.pull-to-refresh.triggered .pull-icon {
  background: rgba(255, 255, 255, 0.2);
  color: white;
}

.pull-text {
  font-size: 0.875rem;
  font-weight: 500;
  color: #374151;
  transition: color 0.3s ease;
}

.pull-to-refresh.triggered .pull-text {
  color: white;
}

/* Long Press Indicator */
.long-press-indicator {
  position: fixed;
  pointer-events: none;
  z-index: 102;
  transform: translate(-50%, -50%);
}

.progress-ring {
  position: relative;
  width: 60px;
  height: 60px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.progress-circle {
  position: absolute;
  top: 0;
  left: 0;
  transform: rotate(-90deg);
  color: #667eea;
  transition: stroke-dashoffset 0.1s ease;
}

.progress-ring i {
  color: #667eea;
  font-size: 1.25rem;
  z-index: 1;
}

/* Haptic Feedback */
.haptic-feedback {
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 80px;
  height: 80px;
  border-radius: 50%;
  pointer-events: none;
  z-index: 103;
  display: flex;
  align-items: center;
  justify-content: center;
}

.haptic-feedback.light {
  background: rgba(102, 126, 234, 0.2);
}

.haptic-feedback.medium {
  background: rgba(245, 158, 11, 0.2);
}

.haptic-feedback.heavy {
  background: rgba(239, 68, 68, 0.2);
}

.haptic-feedback.success {
  background: rgba(16, 185, 129, 0.2);
}

.haptic-feedback.error {
  background: rgba(239, 68, 68, 0.2);
}

.haptic-ripple {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  background: currentColor;
  opacity: 0.3;
  animation: hapticRipple 0.3s ease-out;
}

@keyframes hapticRipple {
  0% {
    transform: scale(0);
    opacity: 0.5;
  }
  50% {
    opacity: 0.3;
  }
  100% {
    transform: scale(1.5);
    opacity: 0;
  }
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .swipe-indicators {
    gap: 1.5rem;
  }
  
  .swipe-indicator {
    padding: 0.75rem;
    font-size: 0.75rem;
  }
  
  .swipe-indicator i {
    font-size: 1.25rem;
  }
  
  .pull-to-refresh {
    padding: 0.75rem;
  }
  
  .pull-icon {
    width: 28px;
    height: 28px;
    font-size: 0.875rem;
  }
  
  .pull-text {
    font-size: 0.8rem;
  }
  
  .progress-ring {
    width: 50px;
    height: 50px;
  }
  
  .progress-circle {
    width: 50px;
    height: 50px;
  }
  
  .progress-ring i {
    font-size: 1rem;
  }
  
  .haptic-feedback {
    width: 60px;
    height: 60px;
  }
}

/* High contrast mode */
@media (prefers-contrast: high) {
  .swipe-indicator {
    border: 2px solid white;
  }
  
  .pull-to-refresh {
    border: 2px solid #374151;
  }
  
  .progress-circle {
    stroke-width: 4;
  }
}

/* Reduced motion */
@media (prefers-reduced-motion: reduce) {
  .swipe-indicator,
  .pull-to-refresh,
  .haptic-ripple {
    animation: none;
    transition: none;
  }
  
  .progress-circle {
    transition: none;
  }
}

/* Dark mode */
@media (prefers-color-scheme: dark) {
  .swipe-indicator {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
  }
  
  .pull-to-refresh {
    background: rgba(30, 41, 59, 0.95);
    color: #f1f5f9;
  }
  
  .pull-text {
    color: #cbd5e1;
  }
  
  .pull-to-refresh.triggered .pull-text {
    color: white;
  }
}
</style>