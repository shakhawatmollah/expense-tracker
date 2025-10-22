<template>
  <div class="form-field" :class="{ 'has-error': hasError, 'is-focused': isFocused, 'has-value': hasValue }">
    <div class="input-container">
      <!-- Prefix slot (e.g., currency symbol) -->
      <div v-if="$slots.prefix || prefix" class="input-prefix">
        <slot name="prefix">{{ prefix }}</slot>
      </div>

      <!-- Main input element -->
      <input
        :id="inputId"
        ref="inputRef"
        :type="type"
        :value="modelValue"
        :placeholder="floatingLabel ? '' : placeholder"
        :disabled="disabled"
        :readonly="readonly"
        :required="required"
        :min="min"
        :max="max"
        :step="step"
        :maxlength="maxlength"
        :autocomplete="autocomplete"
        class="form-input"
        :class="inputClasses"
        :style="(prefix || $slots.prefix) ? { paddingLeft: '1.5rem' } : {}"
        @input="handleInput"
        @focus="handleFocus"
        @blur="handleBlur"
        @keydown="handleKeydown"
        v-bind="$attrs"
      />

      <!-- Floating label -->
      <label v-if="floatingLabel && label" :for="inputId" class="floating-label" :class="labelClasses">
        {{ label }}
        <span v-if="required" class="required-asterisk">*</span>
      </label>

      <!-- Suffix slot (e.g., icons, buttons) -->
      <div v-if="$slots.suffix || suffix" class="input-suffix">
        <slot name="suffix">{{ suffix }}</slot>
      </div>

      <!-- Clear button -->
      <button
        v-if="clearable && hasValue && !disabled"
        type="button"
        class="clear-button"
        @click="clearInput"
        :aria-label="`Clear ${label || 'input'}`"
      >
        <i class="fas fa-times"></i>
      </button>

      <!-- Validation icon -->
      <div v-if="showValidationIcon" class="validation-icon">
        <i v-if="hasError" class="fas fa-exclamation-circle text-red-500"></i>
        <i v-else-if="isValid" class="fas fa-check-circle text-green-500"></i>
      </div>
    </div>

    <!-- Traditional label (when not floating) -->
    <label v-if="!floatingLabel && label" :for="inputId" class="traditional-label">
      {{ label }}
      <span v-if="required" class="required-asterisk">*</span>
    </label>

    <!-- Help text -->
    <div v-if="helpText && !hasError" class="help-text">
      {{ helpText }}
    </div>

    <!-- Error message -->
    <div v-if="hasError" class="error-message">
      <i class="fas fa-exclamation-triangle"></i>
      {{ errorMessage }}
    </div>

    <!-- Character count (for inputs with maxlength) -->
    <div v-if="showCharacterCount" class="character-count">{{ characterCount }}/{{ maxlength }}</div>
  </div>
</template>

<script setup>
  import { ref, computed, nextTick, watch } from 'vue'
  import { useId } from '@/composables/useId'

  const props = defineProps({
    modelValue: {
      type: [String, Number],
      default: ''
    },
    type: {
      type: String,
      default: 'text'
    },
    label: {
      type: String,
      default: ''
    },
    placeholder: {
      type: String,
      default: ''
    },
    disabled: {
      type: Boolean,
      default: false
    },
    readonly: {
      type: Boolean,
      default: false
    },
    required: {
      type: Boolean,
      default: false
    },
    error: {
      type: [String, Array],
      default: null
    },
    helpText: {
      type: String,
      default: ''
    },
    prefix: {
      type: String,
      default: ''
    },
    suffix: {
      type: String,
      default: ''
    },
    floatingLabel: {
      type: Boolean,
      default: true
    },
    clearable: {
      type: Boolean,
      default: false
    },
    showValidationIcon: {
      type: Boolean,
      default: true
    },
    showCharacterCount: {
      type: Boolean,
      default: false
    },
    // HTML input attributes
    min: [String, Number],
    max: [String, Number],
    step: [String, Number],
    maxlength: [String, Number],
    autocomplete: String,
    // Validation
    validateOnBlur: {
      type: Boolean,
      default: true
    },
    validateOnInput: {
      type: Boolean,
      default: false
    },
    // Styling
    variant: {
      type: String,
      default: 'default',
      validator: value => ['default', 'outline', 'filled', 'underline'].includes(value)
    },
    size: {
      type: String,
      default: 'md',
      validator: value => ['sm', 'md', 'lg'].includes(value)
    }
  })

  const emit = defineEmits(['update:modelValue', 'focus', 'blur', 'clear', 'validate'])

  // Refs
  const inputRef = ref(null)
  const isFocused = ref(false)
  const hasBeenFocused = ref(false)

  // Generate unique ID
  const inputId = useId('form-input')

  // Computed properties
  const hasValue = computed(() => {
    return props.modelValue !== null && props.modelValue !== undefined && props.modelValue !== ''
  })

  const hasError = computed(() => {
    if (Array.isArray(props.error)) {
      return props.error.length > 0
    }
    return !!props.error
  })

  const errorMessage = computed(() => {
    if (Array.isArray(props.error)) {
      return props.error[0] || ''
    }
    return props.error || ''
  })

  const isValid = computed(() => {
    return hasBeenFocused.value && !hasError.value && hasValue.value && props.required
  })

  const characterCount = computed(() => {
    return String(props.modelValue || '').length
  })

  const inputClasses = computed(() => {
    return [
      `input-${props.variant}`,
      `input-${props.size}`,
      {
        'has-prefix': props.prefix || props.$slots?.prefix,
        'has-suffix': props.suffix || props.$slots?.suffix,
        'has-clear': props.clearable && hasValue.value,
        'has-validation': props.showValidationIcon
      }
    ]
  })

  const labelClasses = computed(() => {
    return {
      'label-focused': isFocused.value,
      'label-filled': hasValue.value,
      'label-error': hasError.value
    }
  })

  // Methods
  const handleInput = event => {
    const value = event.target.value
    emit('update:modelValue', value)

    if (props.validateOnInput) {
      emit('validate', value)
    }
  }

  const handleFocus = event => {
    isFocused.value = true
    hasBeenFocused.value = true
    emit('focus', event)
  }

  const handleBlur = event => {
    isFocused.value = false
    emit('blur', event)

    if (props.validateOnBlur) {
      emit('validate', props.modelValue)
    }
  }

  const handleKeydown = event => {
    // Emit keydown for parent handling (e.g., Enter key)
    emit('keydown', event)
  }

  const clearInput = () => {
    emit('update:modelValue', '')
    emit('clear')
    focusInput()
  }

  const focusInput = async () => {
    await nextTick()
    if (inputRef.value) {
      inputRef.value.focus()
    }
  }

  // Expose methods for parent components
  defineExpose({
    focus: focusInput,
    blur: () => inputRef.value?.blur(),
    select: () => inputRef.value?.select()
  })
</script>

<style scoped>
  .form-field {
    position: relative;
    margin-bottom: 1.5rem;
  }

  /* Input Container */
  .input-container {
    position: relative;
    display: flex;
    align-items: center;
  }

  /* Base Input Styling */
  .form-input {
    width: 100%;
    font-size: 1rem;
    line-height: 1.5;
    color: #1f2937;
    background-color: transparent;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    padding: 1rem 1rem 1rem 1rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    outline: none;
    appearance: none;
  }

  /* Floating Label */
  .floating-label {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    font-size: 1rem;
    color: #6b7280;
    background-color: white;
    padding: 0 0.5rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    pointer-events: none;
    z-index: 1;
  }

  /* Floating label animations */
  .has-value .floating-label,
  .is-focused .floating-label,
  .label-focused,
  .label-filled {
    top: 0;
    transform: translateY(-50%);
    font-size: 0.875rem;
    color: #667eea;
    font-weight: 500;
  }

  .has-error .floating-label,
  .label-error {
    color: #ef4444;
  }

  /* Traditional Label */
  .traditional-label {
    display: block;
    font-size: 0.875rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
  }

  /* Input Variants */
  .input-default {
    border-color: #e5e7eb;
    background-color: #ffffff;
  }

  .input-outline {
    border-color: #d1d5db;
    background-color: transparent;
  }

  .input-filled {
    border-color: transparent;
    background-color: #f3f4f6;
  }

  .input-underline {
    border: none;
    border-bottom: 2px solid #e5e7eb;
    border-radius: 0;
    background-color: transparent;
    padding-left: 0;
    padding-right: 0;
  }

  /* Input Sizes */
  .input-sm {
    padding: 0.75rem 1rem;
    font-size: 0.875rem;
  }

  .input-md {
    padding: 1rem 1rem;
    font-size: 1rem;
  }

  .input-lg {
    padding: 1.25rem 1rem;
    font-size: 1.125rem;
  }

  /* Focus States */
  .form-input:focus,
  .is-focused .form-input {
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    background-color: #ffffff;
  }

  /* Error States */
  .has-error .form-input {
    border-color: #ef4444;
    background-color: #fef2f2;
  }

  .has-error.is-focused .form-input {
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
  }

  /* Disabled State */
  .form-input:disabled {
    background-color: #f9fafb;
    border-color: #e5e7eb;
    color: #9ca3af;
    cursor: not-allowed;
  }

  /* Prefix & Suffix */
  .input-prefix,
  .input-suffix {
    display: flex;
    align-items: center;
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    color: #6b7280;
    font-size: 0.875rem;
    font-weight: 500;
    z-index: 2;
  }

  .input-prefix {
    left: 1rem;
  }

  .input-suffix {
    right: 1rem;
  }

  .has-prefix .form-input {
    padding-left: 2.5rem;
  }

  .has-prefix .floating-label {
    left: 2.5rem;
  }

  .has-suffix .form-input {
    padding-right: 2.5rem;
  }

  .has-clear .form-input {
    padding-right: 3rem;
  }

  .has-validation .form-input {
    padding-right: 3rem;
  }

  .has-clear.has-validation .form-input {
    padding-right: 4rem;
  }

  /* Clear Button */
  .clear-button {
    position: absolute;
    right: 1rem;
    top: 50%;
    transform: translateY(-50%);
    width: 1.5rem;
    height: 1.5rem;
    border: none;
    background: none;
    color: #9ca3af;
    cursor: pointer;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    z-index: 2;
  }

  .clear-button:hover {
    color: #6b7280;
    background-color: #f3f4f6;
  }

  /* Validation Icon */
  .validation-icon {
    position: absolute;
    right: 1rem;
    top: 50%;
    transform: translateY(-50%);
    z-index: 2;
  }

  .has-clear .validation-icon {
    right: 3rem;
  }

  /* Required Asterisk */
  .required-asterisk {
    color: #ef4444;
    margin-left: 0.25rem;
  }

  /* Help Text */
  .help-text {
    font-size: 0.875rem;
    color: #6b7280;
    margin-top: 0.5rem;
    line-height: 1.4;
  }

  /* Error Message */
  .error-message {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    color: #ef4444;
    margin-top: 0.5rem;
    line-height: 1.4;
  }

  /* Character Count */
  .character-count {
    font-size: 0.75rem;
    color: #9ca3af;
    text-align: right;
    margin-top: 0.25rem;
  }

  /* Smooth Transitions */
  .form-input,
  .floating-label,
  .input-prefix,
  .input-suffix,
  .clear-button,
  .validation-icon {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  }

  /* Responsive Design */
  @media (max-width: 640px) {
    .input-lg {
      padding: 1rem;
      font-size: 1rem;
    }

    .floating-label {
      font-size: 0.875rem;
    }

    .has-value .floating-label,
    .is-focused .floating-label {
      font-size: 0.75rem;
    }
  }

  /* Dark mode support (if needed) */
  @media (prefers-color-scheme: dark) {
    .form-input {
      color: #f9fafb;
      border-color: #4b5563;
    }

    .floating-label {
      color: #9ca3af;
      background-color: #1f2937;
    }

    .traditional-label {
      color: #e5e7eb;
    }

    .input-filled {
      background-color: #374151;
    }

    .help-text {
      color: #9ca3af;
    }
  }
</style>
