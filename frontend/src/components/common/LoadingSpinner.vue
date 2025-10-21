<template>
  <div class="loading-spinner-container" :class="[sizeClass, variantClass]">
    <!-- Professional Spinner -->
    <div class="spinner-wrapper">
      <div class="spinner-ring">
        <div class="spinner-circle spinner-circle-1"></div>
        <div class="spinner-circle spinner-circle-2"></div>
        <div class="spinner-circle spinner-circle-3"></div>
      </div>

      <!-- Pulse Effect -->
      <div class="spinner-pulse"></div>

      <!-- Center Dot -->
      <div class="spinner-center"></div>
    </div>

    <!-- Loading Icon (Optional) -->
    <div v-if="showIcon" class="loading-icon">
      <svg class="spinner-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path
          class="spinner-path"
          d="M12 2V6M12 18V22M4.93 4.93L7.76 7.76M16.24 16.24L19.07 19.07M2 12H6M18 12H22M4.93 19.07L7.76 16.24M16.24 7.76L19.07 4.93"
          stroke="currentColor"
          stroke-width="2"
          stroke-linecap="round"
          stroke-linejoin="round"
        />
      </svg>
    </div>

    <!-- Screen Reader Text -->
    <span class="sr-only">{{ text || 'Loading content, please wait...' }}</span>
  </div>
</template>

<script setup>
  import { computed } from 'vue'

  const props = defineProps({
    size: {
      type: String,
      default: 'md',
      validator: value => ['xs', 'sm', 'md', 'lg', 'xl'].includes(value)
    },
    variant: {
      type: String,
      default: 'primary',
      validator: value => ['primary', 'secondary', 'success', 'warning', 'danger', 'white'].includes(value)
    },
    text: {
      type: String,
      default: ''
    },
    showIcon: {
      type: Boolean,
      default: true
    }
  })

  const sizeClass = computed(() => `spinner-${props.size}`)
  const variantClass = computed(() => `spinner-${props.variant}`)
</script>

<style scoped>
  /* Loading Spinner Container */
  .loading-spinner-container {
    display: inline-flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    position: relative;
  }

  /* Size variants */
  .spinner-xs {
    --spinner-size: 20px;
    font-size: 0.75rem;
  }

  .spinner-sm {
    --spinner-size: 32px;
    font-size: 0.875rem;
  }

  .spinner-md {
    --spinner-size: 48px;
    font-size: 1rem;
  }

  .spinner-lg {
    --spinner-size: 64px;
    font-size: 1.125rem;
  }

  .spinner-xl {
    --spinner-size: 96px;
    font-size: 1.25rem;
  }

  /* Spinner Wrapper */
  .spinner-wrapper {
    position: relative;
    width: var(--spinner-size);
    height: var(--spinner-size);
    display: flex;
    align-items: center;
    justify-content: center;
  }

  /* Spinner Ring Container */
  .spinner-ring {
    position: absolute;
    width: 100%;
    height: 100%;
  }

  /* Individual Spinner Circles */
  .spinner-circle {
    position: absolute;
    width: 100%;
    height: 100%;
    border-radius: 50%;
    border: 2px solid transparent;
    animation: spinRotate 1.5s cubic-bezier(0.68, -0.55, 0.265, 1.55) infinite;
  }

  .spinner-circle-1 {
    animation-delay: 0s;
    animation-duration: 1.5s;
  }

  .spinner-circle-2 {
    animation-delay: -0.3s;
    animation-duration: 1.2s;
    transform: scale(0.8);
  }

  .spinner-circle-3 {
    animation-delay: -0.6s;
    animation-duration: 1.8s;
    transform: scale(0.6);
  }

  /* Color Variants */
  .spinner-primary .spinner-circle-1 {
    border-top-color: #3b82f6;
    border-right-color: #6366f1;
  }

  .spinner-primary .spinner-circle-2 {
    border-top-color: #6366f1;
    border-right-color: #8b5cf6;
  }

  .spinner-primary .spinner-circle-3 {
    border-top-color: #8b5cf6;
    border-right-color: #a855f7;
  }

  .spinner-secondary .spinner-circle-1 {
    border-top-color: #6b7280;
    border-right-color: #9ca3af;
  }

  .spinner-secondary .spinner-circle-2 {
    border-top-color: #9ca3af;
    border-right-color: #d1d5db;
  }

  .spinner-secondary .spinner-circle-3 {
    border-top-color: #d1d5db;
    border-right-color: #e5e7eb;
  }

  .spinner-success .spinner-circle-1 {
    border-top-color: #10b981;
    border-right-color: #34d399;
  }

  .spinner-success .spinner-circle-2 {
    border-top-color: #34d399;
    border-right-color: #6ee7b7;
  }

  .spinner-success .spinner-circle-3 {
    border-top-color: #6ee7b7;
    border-right-color: #9deccd;
  }

  .spinner-warning .spinner-circle-1 {
    border-top-color: #f59e0b;
    border-right-color: #fbbf24;
  }

  .spinner-warning .spinner-circle-2 {
    border-top-color: #fbbf24;
    border-right-color: #fcd34d;
  }

  .spinner-warning .spinner-circle-3 {
    border-top-color: #fcd34d;
    border-right-color: #fde68a;
  }

  .spinner-danger .spinner-circle-1 {
    border-top-color: #ef4444;
    border-right-color: #f87171;
  }

  .spinner-danger .spinner-circle-2 {
    border-top-color: #f87171;
    border-right-color: #fca5a5;
  }

  .spinner-danger .spinner-circle-3 {
    border-top-color: #fca5a5;
    border-right-color: #fecaca;
  }

  .spinner-white .spinner-circle-1 {
    border-top-color: rgba(255, 255, 255, 0.8);
    border-right-color: rgba(255, 255, 255, 0.6);
  }

  .spinner-white .spinner-circle-2 {
    border-top-color: rgba(255, 255, 255, 0.6);
    border-right-color: rgba(255, 255, 255, 0.4);
  }

  .spinner-white .spinner-circle-3 {
    border-top-color: rgba(255, 255, 255, 0.4);
    border-right-color: rgba(255, 255, 255, 0.2);
  }

  /* Pulse Effect */
  .spinner-pulse {
    position: absolute;
    width: 100%;
    height: 100%;
    border-radius: 50%;
    animation: pulseEffect 2s ease-in-out infinite;
  }

  .spinner-primary .spinner-pulse {
    background: radial-gradient(circle, rgba(59, 130, 246, 0.15) 0%, transparent 70%);
  }

  .spinner-secondary .spinner-pulse {
    background: radial-gradient(circle, rgba(107, 114, 128, 0.15) 0%, transparent 70%);
  }

  .spinner-success .spinner-pulse {
    background: radial-gradient(circle, rgba(16, 185, 129, 0.15) 0%, transparent 70%);
  }

  .spinner-warning .spinner-pulse {
    background: radial-gradient(circle, rgba(245, 158, 11, 0.15) 0%, transparent 70%);
  }

  .spinner-danger .spinner-pulse {
    background: radial-gradient(circle, rgba(239, 68, 68, 0.15) 0%, transparent 70%);
  }

  .spinner-white .spinner-pulse {
    background: radial-gradient(circle, rgba(255, 255, 255, 0.15) 0%, transparent 70%);
  }

  /* Center Dot */
  .spinner-center {
    position: absolute;
    width: 20%;
    height: 20%;
    border-radius: 50%;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    animation: centerPulse 1.5s ease-in-out infinite;
  }

  .spinner-primary .spinner-center {
    background: linear-gradient(45deg, #3b82f6, #6366f1);
    box-shadow: 0 0 10px rgba(59, 130, 246, 0.4);
  }

  .spinner-secondary .spinner-center {
    background: linear-gradient(45deg, #6b7280, #9ca3af);
    box-shadow: 0 0 10px rgba(107, 114, 128, 0.4);
  }

  .spinner-success .spinner-center {
    background: linear-gradient(45deg, #10b981, #34d399);
    box-shadow: 0 0 10px rgba(16, 185, 129, 0.4);
  }

  .spinner-warning .spinner-center {
    background: linear-gradient(45deg, #f59e0b, #fbbf24);
    box-shadow: 0 0 10px rgba(245, 158, 11, 0.4);
  }

  .spinner-danger .spinner-center {
    background: linear-gradient(45deg, #ef4444, #f87171);
    box-shadow: 0 0 10px rgba(239, 68, 68, 0.4);
  }

  .spinner-white .spinner-center {
    background: linear-gradient(45deg, rgba(255, 255, 255, 0.9), rgba(255, 255, 255, 0.7));
    box-shadow: 0 0 10px rgba(255, 255, 255, 0.4);
  }

  /* Loading Icon */
  .loading-icon {
    margin-top: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .spinner-icon {
    width: calc(var(--spinner-size) * 0.4);
    height: calc(var(--spinner-size) * 0.4);
    animation: iconSpin 2s linear infinite;
  }

  .spinner-path {
    opacity: 0.8;
  }

  /* Icon Colors */
  .spinner-primary .spinner-icon {
    color: #3b82f6;
  }

  .spinner-secondary .spinner-icon {
    color: #6b7280;
  }

  .spinner-success .spinner-icon {
    color: #10b981;
  }

  .spinner-warning .spinner-icon {
    color: #f59e0b;
  }

  .spinner-danger .spinner-icon {
    color: #ef4444;
  }

  .spinner-white .spinner-icon {
    color: rgba(255, 255, 255, 0.9);
  }

  /* Loading Text */
  .loading-text {
    margin-top: 1rem;
    display: flex;
    align-items: center;
    gap: 0.25rem;
    font-weight: 500;
    opacity: 0.8;
  }

  .loading-message {
    margin-right: 0.25rem;
  }

  .loading-dots {
    display: flex;
    gap: 0.125rem;
  }

  .dot {
    width: 4px;
    height: 4px;
    border-radius: 50%;
    animation: dotBounce 1.4s ease-in-out infinite both;
  }

  .dot-1 {
    animation-delay: -0.32s;
  }
  .dot-2 {
    animation-delay: -0.16s;
  }
  .dot-3 {
    animation-delay: 0s;
  }

  /* Text Colors */
  .spinner-primary .loading-text {
    color: #3b82f6;
  }

  .spinner-primary .dot {
    background-color: #3b82f6;
  }

  .spinner-secondary .loading-text {
    color: #6b7280;
  }

  .spinner-secondary .dot {
    background-color: #6b7280;
  }

  .spinner-success .loading-text {
    color: #10b981;
  }

  .spinner-success .dot {
    background-color: #10b981;
  }

  .spinner-warning .loading-text {
    color: #f59e0b;
  }

  .spinner-warning .dot {
    background-color: #f59e0b;
  }

  .spinner-danger .loading-text {
    color: #ef4444;
  }

  .spinner-danger .dot {
    background-color: #ef4444;
  }

  .spinner-white .loading-text {
    color: rgba(255, 255, 255, 0.9);
  }

  .spinner-white .dot {
    background-color: rgba(255, 255, 255, 0.9);
  }

  /* Screen Reader Only */
  .sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
  }

  @keyframes iconSpin {
    0% {
      transform: rotate(0deg);
    }
    100% {
      transform: rotate(360deg);
    }
  }

  /* Animations */
  @keyframes spinRotate {
    0% {
      transform: rotate(0deg);
    }
    100% {
      transform: rotate(360deg);
    }
  }

  @keyframes pulseEffect {
    0%,
    100% {
      opacity: 1;
      transform: scale(1);
    }
    50% {
      opacity: 0.3;
      transform: scale(1.1);
    }
  }

  @keyframes centerPulse {
    0%,
    100% {
      opacity: 1;
      transform: translate(-50%, -50%) scale(1);
    }
    50% {
      opacity: 0.6;
      transform: translate(-50%, -50%) scale(1.2);
    }
  }

  @keyframes dotBounce {
    0%,
    80%,
    100% {
      transform: scale(0);
    }
    40% {
      transform: scale(1);
    }
  }

  /* Responsive Design */
  @media (max-width: 640px) {
    .spinner-xl {
      --spinner-size: 64px;
      font-size: 1rem;
    }

    .loading-text {
      margin-top: 0.75rem;
      font-size: 0.875rem;
    }
  }

  @media (prefers-reduced-motion: reduce) {
    .spinner-circle {
      animation-duration: 3s;
    }

    .spinner-pulse {
      animation: none;
    }

    .spinner-center {
      animation: none;
    }

    .spinner-icon {
      animation-duration: 4s;
    }

    .dot {
      animation: none;
      opacity: 0.7;
    }
  }
</style>
