<template>
  <div class="confirm-dialog-overlay" @click="handleOverlayClick">
    <div class="confirm-dialog" @click.stop>
      <!-- Icon -->
      <div class="dialog-icon">
        <div class="icon-container" :class="iconColorClass">
          <i :class="iconClass"></i>
        </div>
      </div>

      <!-- Content -->
      <div class="dialog-content">
        <h3 class="dialog-title">{{ title }}</h3>
        <p class="dialog-message">{{ message }}</p>
      </div>

      <!-- Actions -->
      <div class="dialog-actions">
        <button @click="$emit('cancel')" class="btn btn-secondary">
          {{ cancelText }}
        </button>
        <button @click="$emit('confirm')" class="btn" :class="confirmClass">
          {{ confirmText }}
        </button>
      </div>
    </div>
  </div>
</template>

<script>
  import { computed } from 'vue'

  export default {
    name: 'ConfirmDialog',
    props: {
      title: {
        type: String,
        default: 'Confirm Action'
      },
      message: {
        type: String,
        required: true
      },
      confirmText: {
        type: String,
        default: 'Confirm'
      },
      cancelText: {
        type: String,
        default: 'Cancel'
      },
      confirmClass: {
        type: String,
        default: 'btn-primary'
      },
      type: {
        type: String,
        default: 'warning', // warning, danger, info
        validator: value => ['warning', 'danger', 'info'].includes(value)
      }
    },
    emits: ['confirm', 'cancel'],
    setup(props, { emit }) {
      const iconClass = computed(() => {
        switch (props.type) {
          case 'danger':
            return 'fas fa-exclamation-triangle'
          case 'info':
            return 'fas fa-info-circle'
          default:
            return 'fas fa-question-circle'
        }
      })

      const iconColorClass = computed(() => {
        switch (props.type) {
          case 'danger':
            return 'text-red-600 bg-red-100'
          case 'info':
            return 'text-blue-600 bg-blue-100'
          default:
            return 'text-yellow-600 bg-yellow-100'
        }
      })

      const handleOverlayClick = event => {
        if (event.target === event.currentTarget) {
          emit('cancel')
        }
      }

      return {
        iconClass,
        iconColorClass,
        handleOverlayClick
      }
    }
  }
</script>

<style scoped>
  .confirm-dialog-overlay {
    @apply fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4;
  }

  .confirm-dialog {
    @apply bg-white rounded-lg shadow-xl max-w-md w-full p-6;
  }

  .dialog-icon {
    @apply flex justify-center mb-4;
  }

  .icon-container {
    @apply w-12 h-12 rounded-full flex items-center justify-center text-xl;
  }

  .dialog-content {
    @apply text-center mb-6;
  }

  .dialog-title {
    @apply text-lg font-semibold text-gray-900 mb-2;
  }

  .dialog-message {
    @apply text-gray-600;
  }

  .dialog-actions {
    @apply flex justify-center space-x-3;
  }

  .btn {
    @apply px-4 py-2 rounded-md font-medium text-sm transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2;
  }

  .btn-primary {
    @apply bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-500;
  }

  .btn-secondary {
    @apply bg-gray-200 text-gray-800 hover:bg-gray-300 focus:ring-gray-500;
  }

  .btn-danger {
    @apply bg-red-600 text-white hover:bg-red-700 focus:ring-red-500;
  }
</style>
