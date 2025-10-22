<template>
  <Teleport to="body">
    <div :class="['toast-container', position]">
      <TransitionGroup name="toast" tag="div">
        <div
          v-for="notification in activeNotifications"
          :key="notification.id"
          :class="['toast-notification', notification.type, { 'with-progress': !notification.persistent }]"
          @click="handleClick(notification)"
          @mouseenter="pauseNotification(notification.id)"
          @mouseleave="resumeNotification(notification.id)"
        >
          <!-- Icon -->
          <div class="toast-icon">
            <span class="emoji">{{ notification.icon }}</span>
          </div>

          <!-- Content -->
          <div class="toast-content">
            <h4 v-if="notification.title" class="toast-title">{{ notification.title }}</h4>
            <p class="toast-message">{{ notification.message }}</p>
          </div>

          <!-- Close Button -->
          <button
            v-if="!notification.persistent"
            @click.stop="dismissNotification(notification.id)"
            class="toast-close"
          >
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>

          <!-- Progress Bar -->
          <div
            v-if="!notification.persistent && notification.duration"
            class="toast-progress"
            :class="{ paused: notification.paused }"
            :style="{
              animationDuration: `${notification.duration}ms`,
              animationPlayState: notification.paused ? 'paused' : 'running'
            }"
          ></div>
        </div>
      </TransitionGroup>
    </div>
  </Teleport>
</template>

<script setup>
  import { computed, watch } from 'vue'
  import { useNotificationsStore } from '@/stores/notifications'

  const props = defineProps({
    position: {
      type: String,
      default: 'top-right',
      validator: value =>
        ['top-right', 'top-left', 'bottom-right', 'bottom-left', 'top-center', 'bottom-center'].includes(value)
    }
  })

  const notificationsStore = useNotificationsStore()

  const activeNotifications = computed(() =>
    notificationsStore.activeNotifications.filter(n => n.position === props.position || !n.position)
  )

  const dismissNotification = id => {
    notificationsStore.dismissNotification(id)
  }

  const pauseNotification = id => {
    notificationsStore.pauseNotification(id)
  }

  const resumeNotification = id => {
    notificationsStore.resumeNotification(id)
  }

  const handleClick = notification => {
    if (notification.onClick) {
      notification.onClick()
    }
  }
</script>

<style scoped>
  .toast-container {
    position: fixed;
    z-index: 99999;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    pointer-events: none;
    padding: 1rem;
  }

  .toast-container > div {
    pointer-events: all;
  }

  /* Positions */
  .toast-container.top-right {
    top: 0;
    right: 0;
  }

  .toast-container.top-left {
    top: 0;
    left: 0;
  }

  .toast-container.bottom-right {
    bottom: 0;
    right: 0;
    flex-direction: column-reverse;
  }

  .toast-container.bottom-left {
    bottom: 0;
    left: 0;
    flex-direction: column-reverse;
  }

  .toast-container.top-center {
    top: 0;
    left: 50%;
    transform: translateX(-50%);
    align-items: center;
  }

  .toast-container.bottom-center {
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    align-items: center;
    flex-direction: column-reverse;
  }

  /* Toast Notification */
  .toast-notification {
    min-width: 320px;
    max-width: 420px;
    padding: 1rem 1.25rem;
    background: white;
    border-radius: 0.75rem;
    box-shadow:
      0 10px 40px rgba(0, 0, 0, 0.15),
      0 0 0 1px rgba(0, 0, 0, 0.05);
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    cursor: pointer;
    position: relative;
    overflow: hidden;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  }

  .toast-notification:hover {
    transform: translateY(-2px);
    box-shadow:
      0 12px 48px rgba(0, 0, 0, 0.2),
      0 0 0 1px rgba(0, 0, 0, 0.05);
  }

  .toast-notification::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 4px;
    transition: all 0.3s ease;
  }

  /* Type colors */
  .toast-notification.success::before {
    background: linear-gradient(180deg, #10b981 0%, #059669 100%);
  }

  .toast-notification.error::before {
    background: linear-gradient(180deg, #ef4444 0%, #dc2626 100%);
  }

  .toast-notification.warning::before {
    background: linear-gradient(180deg, #f59e0b 0%, #d97706 100%);
  }

  .toast-notification.info::before {
    background: linear-gradient(180deg, #3b82f6 0%, #2563eb 100%);
  }

  .toast-notification.expense::before {
    background: linear-gradient(180deg, #10b981 0%, #059669 100%);
  }

  .toast-notification.budget::before {
    background: linear-gradient(180deg, #8b5cf6 0%, #7c3aed 100%);
  }

  .toast-notification.achievement::before {
    background: linear-gradient(180deg, #f59e0b 0%, #d97706 100%);
  }

  /* Icon */
  .toast-icon {
    width: 2.5rem;
    height: 2.5rem;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.625rem;
    position: relative;
  }

  .toast-notification.success .toast-icon {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(5, 150, 105, 0.1) 100%);
  }

  .toast-notification.error .toast-icon {
    background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(220, 38, 38, 0.1) 100%);
  }

  .toast-notification.warning .toast-icon {
    background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(217, 119, 6, 0.1) 100%);
  }

  .toast-notification.info .toast-icon {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(37, 99, 235, 0.1) 100%);
  }

  .toast-notification.expense .toast-icon {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(5, 150, 105, 0.1) 100%);
  }

  .toast-notification.budget .toast-icon {
    background: linear-gradient(135deg, rgba(139, 92, 246, 0.1) 0%, rgba(124, 58, 237, 0.1) 100%);
  }

  .toast-notification.achievement .toast-icon {
    background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(217, 119, 6, 0.1) 100%);
  }

  .emoji {
    font-size: 1.5rem;
    line-height: 1;
  }

  /* Content */
  .toast-content {
    flex: 1;
    min-width: 0;
  }

  .toast-title {
    font-size: 0.875rem;
    font-weight: 600;
    color: #1f2937;
    margin: 0 0 0.25rem 0;
    line-height: 1.4;
  }

  .toast-message {
    font-size: 0.875rem;
    color: #6b7280;
    margin: 0;
    line-height: 1.5;
  }

  /* Close Button */
  .toast-close {
    width: 1.5rem;
    height: 1.5rem;
    padding: 0;
    background: transparent;
    border: none;
    border-radius: 0.375rem;
    cursor: pointer;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    color: #9ca3af;
  }

  .toast-close:hover {
    background: rgba(0, 0, 0, 0.05);
    color: #6b7280;
  }

  .toast-close svg {
    width: 1rem;
    height: 1rem;
  }

  /* Progress Bar */
  .toast-progress {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 3px;
    transform-origin: left;
    animation-name: shrink;
    animation-timing-function: linear;
    animation-fill-mode: forwards;
  }

  .toast-notification.success .toast-progress {
    background: linear-gradient(90deg, #10b981 0%, #059669 100%);
  }

  .toast-notification.error .toast-progress {
    background: linear-gradient(90deg, #ef4444 0%, #dc2626 100%);
  }

  .toast-notification.warning .toast-progress {
    background: linear-gradient(90deg, #f59e0b 0%, #d97706 100%);
  }

  .toast-notification.info .toast-progress {
    background: linear-gradient(90deg, #3b82f6 0%, #2563eb 100%);
  }

  .toast-notification.expense .toast-progress {
    background: linear-gradient(90deg, #10b981 0%, #059669 100%);
  }

  .toast-notification.budget .toast-progress {
    background: linear-gradient(90deg, #8b5cf6 0%, #7c3aed 100%);
  }

  .toast-notification.achievement .toast-progress {
    background: linear-gradient(90deg, #f59e0b 0%, #d97706 100%);
  }

  @keyframes shrink {
    from {
      transform: scaleX(1);
    }
    to {
      transform: scaleX(0);
    }
  }

  /* Transitions */
  .toast-enter-active {
    animation: toast-in 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  }

  .toast-leave-active {
    animation: toast-out 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  }

  @keyframes toast-in {
    from {
      opacity: 0;
      transform: translateX(100%) scale(0.9);
    }
    to {
      opacity: 1;
      transform: translateX(0) scale(1);
    }
  }

  @keyframes toast-out {
    from {
      opacity: 1;
      transform: translateX(0) scale(1);
    }
    to {
      opacity: 0;
      transform: translateX(100%) scale(0.9);
    }
  }

  /* Mobile Responsive */
  @media (max-width: 768px) {
    .toast-container {
      padding: 0.75rem;
    }

    .toast-notification {
      min-width: 0;
      max-width: calc(100vw - 1.5rem);
    }
  }
</style>
