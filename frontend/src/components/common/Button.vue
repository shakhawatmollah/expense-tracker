<template>
  <button :class="buttonClasses" :disabled="disabled || loading" :type="type" @click="handleClick" v-bind="$attrs">
    <!-- Loading spinner -->
    <div v-if="loading" class="button-spinner">
      <svg class="animate-spin" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path
          class="opacity-75"
          fill="currentColor"
          d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
        ></path>
      </svg>
    </div>

    <!-- Icon (left) -->
    <i v-if="iconLeft && !loading" :class="iconLeft" class="button-icon button-icon-left"></i>

    <!-- Button content -->
    <span class="button-content">
      <slot />
    </span>

    <!-- Icon (right) -->
    <i v-if="iconRight && !loading" :class="iconRight" class="button-icon button-icon-right"></i>

    <!-- Ripple effect -->
    <span class="button-ripple"></span>
  </button>
</template>

<script setup>
  import { computed } from 'vue'

  const props = defineProps({
    variant: {
      type: String,
      default: 'primary',
      validator: value => ['primary', 'secondary', 'outline', 'ghost', 'danger', 'success', 'warning'].includes(value)
    },
    size: {
      type: String,
      default: 'md',
      validator: value => ['xs', 'sm', 'md', 'lg', 'xl'].includes(value)
    },
    type: {
      type: String,
      default: 'button',
      validator: value => ['button', 'submit', 'reset'].includes(value)
    },
    disabled: {
      type: Boolean,
      default: false
    },
    loading: {
      type: Boolean,
      default: false
    },
    iconLeft: {
      type: String,
      default: ''
    },
    iconRight: {
      type: String,
      default: ''
    },
    block: {
      type: Boolean,
      default: false
    },
    rounded: {
      type: Boolean,
      default: false
    },
    elevated: {
      type: Boolean,
      default: false
    }
  })

  const emit = defineEmits(['click'])

  const buttonClasses = computed(() => {
    const baseClasses = [
      'button-base',
      `button-${props.variant}`,
      `button-${props.size}`,
      {
        'button-disabled': props.disabled || props.loading,
        'button-loading': props.loading,
        'button-block': props.block,
        'button-rounded': props.rounded,
        'button-elevated': props.elevated,
        'button-with-icon': props.iconLeft || props.iconRight
      }
    ]

    return baseClasses
  })

  const handleClick = event => {
    if (!props.disabled && !props.loading) {
      // Add ripple effect
      const button = event.currentTarget
      const ripple = button.querySelector('.button-ripple')

      if (ripple) {
        const rect = button.getBoundingClientRect()
        const size = Math.max(rect.width, rect.height)
        const x = event.clientX - rect.left - size / 2
        const y = event.clientY - rect.top - size / 2

        ripple.style.width = ripple.style.height = size + 'px'
        ripple.style.left = x + 'px'
        ripple.style.top = y + 'px'
        ripple.classList.add('button-ripple-active')

        setTimeout(() => {
          ripple.classList.remove('button-ripple-active')
        }, 600)
      }

      emit('click', event)
    }
  }
</script>

<style scoped>
  /* Base Button Styles */
  .button-base {
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    font-family: inherit;
    font-weight: 600;
    text-align: center;
    text-decoration: none;
    white-space: nowrap;
    vertical-align: middle;
    user-select: none;
    border: 2px solid transparent;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    outline: none;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  }

  .button-base:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.3);
  }

  /* Button Variants */
  .button-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-color: transparent;
  }

  .button-primary:hover:not(.button-disabled) {
    background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 20px rgba(102, 126, 234, 0.4);
  }

  .button-secondary {
    background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
    color: white;
    border-color: transparent;
  }

  .button-secondary:hover:not(.button-disabled) {
    background: linear-gradient(135deg, #5b6470 0%, #3f4651 100%);
    transform: translateY(-1px);
  }

  .button-outline {
    background: transparent;
    color: #667eea;
    border-color: #667eea;
  }

  .button-outline:hover:not(.button-disabled) {
    background: #667eea;
    color: white;
    transform: translateY(-1px);
  }

  .button-ghost {
    background: transparent;
    color: #667eea;
    border-color: transparent;
    box-shadow: none;
  }

  .button-ghost:hover:not(.button-disabled) {
    background: rgba(102, 126, 234, 0.1);
    color: #5a6fd8;
  }

  .button-danger {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: white;
    border-color: transparent;
  }

  .button-danger:hover:not(.button-disabled) {
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 20px rgba(239, 68, 68, 0.4);
  }

  .button-success {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    border-color: transparent;
  }

  .button-success:hover:not(.button-disabled) {
    background: linear-gradient(135deg, #059669 0%, #047857 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 20px rgba(16, 185, 129, 0.4);
  }

  .button-warning {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: white;
    border-color: transparent;
  }

  .button-warning:hover:not(.button-disabled) {
    background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 20px rgba(245, 158, 11, 0.4);
  }

  /* Button Sizes */
  .button-xs {
    padding: 0.25rem 0.5rem;
    font-size: 0.625rem;
    line-height: 1rem;
    border-radius: 6px;
  }

  .button-sm {
    padding: 0.375rem 0.75rem;
    font-size: 0.75rem;
    line-height: 1.25rem;
    border-radius: 8px;
  }

  .button-md {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
    line-height: 1.5rem;
    border-radius: 10px;
  }

  .button-lg {
    padding: 0.75rem 1.5rem;
    font-size: 1rem;
    line-height: 1.75rem;
    border-radius: 12px;
  }

  .button-xl {
    padding: 1rem 2rem;
    font-size: 1.125rem;
    line-height: 1.75rem;
    border-radius: 14px;
  }

  /* Button States */
  .button-disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none !important;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1) !important;
  }

  .button-loading {
    cursor: wait;
  }

  .button-loading .button-content {
    opacity: 0.7;
  }

  /* Button Modifiers */
  .button-block {
    width: 100%;
    display: flex;
  }

  .button-rounded {
    border-radius: 9999px;
  }

  .button-elevated {
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
  }

  .button-elevated:hover:not(.button-disabled) {
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
  }

  /* Button Content */
  .button-content {
    transition: opacity 0.3s ease;
  }

  /* Button Icons */
  .button-icon {
    font-size: 0.875em;
    transition: transform 0.3s ease;
  }

  .button-icon-left {
    margin-right: 0.25rem;
    margin-left: -0.25rem;
  }

  .button-icon-right {
    margin-left: 0.25rem;
    margin-right: -0.25rem;
  }

  .button-with-icon:hover .button-icon {
    transform: scale(1.1);
  }

  /* Button Spinner */
  .button-spinner {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 1em;
    height: 1em;
    margin-right: 0.5rem;
  }

  .button-spinner svg {
    width: 100%;
    height: 100%;
  }

  .animate-spin {
    animation: spin 1s linear infinite;
  }

  @keyframes spin {
    from {
      transform: rotate(0deg);
    }
    to {
      transform: rotate(360deg);
    }
  }

  /* Ripple Effect */
  .button-ripple {
    position: absolute;
    background: rgba(255, 255, 255, 0.4);
    border-radius: 50%;
    transform: scale(0);
    pointer-events: none;
    opacity: 0;
  }

  .button-ripple-active {
    animation: ripple 0.6s linear;
  }

  @keyframes ripple {
    to {
      transform: scale(4);
      opacity: 0;
    }
  }

  /* Focus Styles */
  .button-primary:focus {
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.3);
  }

  .button-secondary:focus {
    box-shadow: 0 0 0 3px rgba(107, 114, 128, 0.3);
  }

  .button-outline:focus {
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.3);
  }

  .button-ghost:focus {
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.3);
  }

  .button-danger:focus {
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.3);
  }

  .button-success:focus {
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.3);
  }

  .button-warning:focus {
    box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.3);
  }

  /* Active States */
  .button-base:active:not(.button-disabled) {
    transform: translateY(1px) scale(0.98);
  }

  /* Responsive Design */
  @media (max-width: 640px) {
    .button-lg {
      padding: 0.5rem 1rem;
      font-size: 0.875rem;
    }

    .button-xl {
      padding: 0.75rem 1.5rem;
      font-size: 1rem;
    }
  }

  /* Dark mode support */
  @media (prefers-color-scheme: dark) {
    .button-outline {
      color: #a7b6ea;
      border-color: #a7b6ea;
    }

    .button-outline:hover:not(.button-disabled) {
      background: #a7b6ea;
      color: #1f2937;
    }

    .button-ghost {
      color: #a7b6ea;
    }

    .button-ghost:hover:not(.button-disabled) {
      background: rgba(167, 182, 234, 0.1);
      color: #c4d1f0;
    }
  }
</style>
