<template>
  <div class="form-field" :class="{ 'has-error': hasError, 'is-focused': isFocused, 'has-value': hasValue }">
    <div class="select-container">
      <!-- Custom Select -->
      <div 
        ref="selectRef"
        class="custom-select"
        :class="selectClasses"
        @click="toggleDropdown"
        @keydown="handleKeydown"
        :tabindex="disabled ? -1 : 0"
        :aria-expanded="isOpen"
        :aria-haspopup="true"
        role="combobox"
      >
        <!-- Selected Value Display -->
        <div class="select-value">
          <span v-if="selectedOption" class="selected-text">
            <slot name="option" :option="selectedOption">
              {{ selectedOption[optionLabel] }}
            </slot>
          </span>
          <span v-else class="placeholder-text">
            {{ placeholder }}
          </span>
        </div>
        
        <!-- Dropdown Arrow -->
        <div class="select-arrow" :class="{ 'rotated': isOpen }">
          <i class="fas fa-chevron-down"></i>
        </div>
        
        <!-- Floating Label -->
        <label 
          v-if="floatingLabel && label" 
          :for="selectId" 
          class="floating-label"
          :class="labelClasses"
        >
          {{ label }}
          <span v-if="required" class="required-asterisk">*</span>
        </label>
      </div>
      
      <!-- Dropdown Menu -->
      <div 
        v-if="isOpen" 
        class="dropdown-menu"
        :class="dropdownClasses"
        role="listbox"
      >
        <!-- Search Input (if searchable) -->
        <div v-if="searchable" class="search-container">
          <input
            ref="searchInputRef"
            v-model="searchQuery"
            type="text"
            class="search-input"
            :placeholder="searchPlaceholder"
            @keydown.stop="handleSearchKeydown"
          />
          <i class="fas fa-search search-icon"></i>
        </div>
        
        <!-- Options List -->
        <div class="options-container" ref="optionsContainer">
          <!-- Loading State -->
          <div v-if="loading" class="option-loading">
            <i class="fas fa-spinner fa-spin"></i>
            <span>Loading options...</span>
          </div>
          
          <!-- No Options -->
          <div v-else-if="filteredOptions.length === 0" class="option-empty">
            <i class="fas fa-exclamation-circle"></i>
            <span>{{ noOptionsText }}</span>
          </div>
          
          <!-- Options -->
          <div
            v-else
            v-for="(option, index) in filteredOptions"
            :key="getOptionKey(option, index)"
            class="select-option"
            :class="{
              'option-selected': isSelected(option),
              'option-highlighted': highlightedIndex === index,
              'option-disabled': isOptionDisabled(option)
            }"
            @click="selectOption(option)"
            @mouseenter="highlightedIndex = index"
            role="option"
            :aria-selected="isSelected(option)"
          >
            <slot name="option" :option="option" :index="index">
              <div class="option-content">
                <span class="option-label">{{ option[optionLabel] }}</span>
                <span v-if="option[optionDescription]" class="option-description">
                  {{ option[optionDescription] }}
                </span>
              </div>
              <i v-if="isSelected(option)" class="fas fa-check option-check"></i>
            </slot>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Traditional Label -->
    <label 
      v-if="!floatingLabel && label" 
      :for="selectId" 
      class="traditional-label"
    >
      {{ label }}
      <span v-if="required" class="required-asterisk">*</span>
    </label>
    
    <!-- Help Text -->
    <div v-if="helpText && !hasError" class="help-text">
      {{ helpText }}
    </div>
    
    <!-- Error Message -->
    <div v-if="hasError" class="error-message">
      <i class="fas fa-exclamation-triangle"></i>
      {{ errorMessage }}
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted, onUnmounted } from 'vue'
import { useId } from '@/composables/useId'

const props = defineProps({
  modelValue: {
    type: [String, Number, Object],
    default: null
  },
  options: {
    type: Array,
    default: () => []
  },
  optionLabel: {
    type: String,
    default: 'label'
  },
  optionValue: {
    type: String,
    default: 'value'
  },
  optionDescription: {
    type: String,
    default: 'description'
  },
  optionDisabled: {
    type: String,
    default: 'disabled'
  },
  label: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: 'Select an option'
  },
  searchPlaceholder: {
    type: String,
    default: 'Search options...'
  },
  noOptionsText: {
    type: String,
    default: 'No options available'
  },
  disabled: {
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
  floatingLabel: {
    type: Boolean,
    default: true
  },
  searchable: {
    type: Boolean,
    default: false
  },
  loading: {
    type: Boolean,
    default: false
  },
  clearable: {
    type: Boolean,
    default: false
  },
  multiple: {
    type: Boolean,
    default: false
  },
  // Styling
  variant: {
    type: String,
    default: 'default',
    validator: (value) => ['default', 'outline', 'filled', 'underline'].includes(value)
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value)
  },
  maxHeight: {
    type: String,
    default: '200px'
  }
})

const emit = defineEmits(['update:modelValue', 'focus', 'blur', 'search', 'select'])

// Refs
const selectRef = ref(null)
const searchInputRef = ref(null)
const optionsContainer = ref(null)
const isOpen = ref(false)
const isFocused = ref(false)
const searchQuery = ref('')
const highlightedIndex = ref(-1)

// Generate unique ID
const selectId = useId('form-select')

// Computed properties
const hasValue = computed(() => {
  if (props.multiple) {
    return Array.isArray(props.modelValue) && props.modelValue.length > 0
  }
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

const selectedOption = computed(() => {
  if (!hasValue.value) return null
  
  if (props.multiple) {
    return props.options.filter(option => 
      props.modelValue.includes(getOptionValue(option))
    )
  }
  
  return props.options.find(option => 
    getOptionValue(option) === props.modelValue
  )
})

const filteredOptions = computed(() => {
  if (!props.searchable || !searchQuery.value) {
    return props.options
  }
  
  const query = searchQuery.value.toLowerCase()
  return props.options.filter(option => 
    option[props.optionLabel].toLowerCase().includes(query) ||
    (option[props.optionDescription] && 
     option[props.optionDescription].toLowerCase().includes(query))
  )
})

const selectClasses = computed(() => {
  return [
    `select-${props.variant}`,
    `select-${props.size}`,
    {
      'select-disabled': props.disabled,
      'select-open': isOpen.value,
      'select-error': hasError.value
    }
  ]
})

const dropdownClasses = computed(() => {
  return [
    `dropdown-${props.variant}`,
    {
      'dropdown-searchable': props.searchable
    }
  ]
})

const labelClasses = computed(() => {
  return {
    'label-focused': isFocused.value || isOpen.value,
    'label-filled': hasValue.value,
    'label-error': hasError.value
  }
})

// Methods
const getOptionValue = (option) => {
  return typeof option === 'object' ? option[props.optionValue] : option
}

const getOptionKey = (option, index) => {
  return typeof option === 'object' ? 
    (option.id || option[props.optionValue] || index) : 
    option
}

const isSelected = (option) => {
  const optionValue = getOptionValue(option)
  
  if (props.multiple) {
    return Array.isArray(props.modelValue) && 
           props.modelValue.includes(optionValue)
  }
  
  return props.modelValue === optionValue
}

const isOptionDisabled = (option) => {
  return typeof option === 'object' ? option[props.optionDisabled] : false
}

const toggleDropdown = () => {
  if (props.disabled) return
  
  isOpen.value = !isOpen.value
  
  if (isOpen.value) {
    isFocused.value = true
    emit('focus')
    nextTick(() => {
      if (props.searchable && searchInputRef.value) {
        searchInputRef.value.focus()
      }
    })
  } else {
    isFocused.value = false
    emit('blur')
    searchQuery.value = ''
  }
}

const selectOption = (option) => {
  if (isOptionDisabled(option)) return
  
  const optionValue = getOptionValue(option)
  
  if (props.multiple) {
    const currentValues = Array.isArray(props.modelValue) ? props.modelValue : []
    let newValues
    
    if (currentValues.includes(optionValue)) {
      newValues = currentValues.filter(v => v !== optionValue)
    } else {
      newValues = [...currentValues, optionValue]
    }
    
    emit('update:modelValue', newValues)
  } else {
    emit('update:modelValue', optionValue)
    closeDropdown()
  }
  
  emit('select', option)
}

const closeDropdown = () => {
  isOpen.value = false
  isFocused.value = false
  searchQuery.value = ''
  highlightedIndex.value = -1
  emit('blur')
}

const handleKeydown = (event) => {
  switch (event.key) {
    case 'Enter':
    case ' ':
      event.preventDefault()
      if (!isOpen.value) {
        toggleDropdown()
      } else if (highlightedIndex.value >= 0) {
        selectOption(filteredOptions.value[highlightedIndex.value])
      }
      break
    case 'Escape':
      closeDropdown()
      break
    case 'ArrowDown':
      event.preventDefault()
      if (!isOpen.value) {
        toggleDropdown()
      } else {
        highlightedIndex.value = Math.min(
          highlightedIndex.value + 1,
          filteredOptions.value.length - 1
        )
      }
      break
    case 'ArrowUp':
      event.preventDefault()
      if (isOpen.value) {
        highlightedIndex.value = Math.max(highlightedIndex.value - 1, 0)
      }
      break
  }
}

const handleSearchKeydown = (event) => {
  switch (event.key) {
    case 'Escape':
      closeDropdown()
      break
    case 'ArrowDown':
      event.preventDefault()
      highlightedIndex.value = 0
      break
    case 'Enter':
      event.preventDefault()
      if (highlightedIndex.value >= 0) {
        selectOption(filteredOptions.value[highlightedIndex.value])
      }
      break
  }
}

const handleClickOutside = (event) => {
  if (selectRef.value && !selectRef.value.contains(event.target)) {
    closeDropdown()
  }
}

// Watch for search query changes
watch(searchQuery, (newQuery) => {
  highlightedIndex.value = -1
  emit('search', newQuery)
})

// Lifecycle
onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})

// Expose methods
defineExpose({
  focus: () => selectRef.value?.focus(),
  blur: closeDropdown,
  open: () => { isOpen.value = true },
  close: closeDropdown
})
</script>

<style scoped>
.form-field {
  position: relative;
  margin-bottom: 1.5rem;
}

/* Select Container */
.select-container {
  position: relative;
}

/* Custom Select */
.custom-select {
  position: relative;
  width: 100%;
  min-height: 3.5rem;
  padding: 1rem 3rem 1rem 1rem;
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  background-color: #ffffff;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  outline: none;
  display: flex;
  align-items: center;
}

.custom-select:focus,
.select-open {
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.select-error {
  border-color: #ef4444;
  background-color: #fef2f2;
}

.select-disabled {
  background-color: #f9fafb;
  border-color: #e5e7eb;
  color: #9ca3af;
  cursor: not-allowed;
}

/* Select Value */
.select-value {
  flex: 1;
  display: flex;
  align-items: center;
}

.selected-text {
  color: #1f2937;
  font-weight: 500;
}

.placeholder-text {
  color: #9ca3af;
}

/* Select Arrow */
.select-arrow {
  position: absolute;
  right: 1rem;
  top: 50%;
  transform: translateY(-50%);
  color: #6b7280;
  transition: transform 0.3s ease;
}

.select-arrow.rotated {
  transform: translateY(-50%) rotate(180deg);
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

/* Dropdown Menu */
.dropdown-menu {
  position: absolute;
  top: calc(100% + 0.5rem);
  left: 0;
  right: 0;
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
  z-index: 1000;
  overflow: hidden;
  animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-10px) scale(0.95);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

/* Search Container */
.search-container {
  position: relative;
  padding: 1rem;
  border-bottom: 1px solid #e5e7eb;
}

.search-input {
  width: 100%;
  padding: 0.75rem 2.5rem 0.75rem 1rem;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 0.875rem;
  outline: none;
  transition: border-color 0.2s ease;
}

.search-input:focus {
  border-color: #667eea;
}

.search-icon {
  position: absolute;
  right: 1.5rem;
  top: 50%;
  transform: translateY(-50%);
  color: #9ca3af;
  font-size: 0.875rem;
}

/* Options Container */
.options-container {
  max-height: 200px;
  overflow-y: auto;
}

/* Select Option */
.select-option {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem;
  cursor: pointer;
  transition: background-color 0.2s ease;
  border-bottom: 1px solid #f3f4f6;
}

.select-option:last-child {
  border-bottom: none;
}

.select-option:hover,
.option-highlighted {
  background-color: #f8fafc;
}

.option-selected {
  background-color: #eff6ff;
  color: #1d4ed8;
}

.option-disabled {
  color: #9ca3af;
  cursor: not-allowed;
  background-color: #f9fafb;
}

/* Option Content */
.option-content {
  flex: 1;
}

.option-label {
  display: block;
  font-weight: 500;
  color: inherit;
}

.option-description {
  display: block;
  font-size: 0.875rem;
  color: #6b7280;
  margin-top: 0.25rem;
}

.option-check {
  color: #10b981;
  font-size: 0.875rem;
}

/* Loading/Empty States */
.option-loading,
.option-empty {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 2rem;
  color: #6b7280;
  font-size: 0.875rem;
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

/* Size Variants */
.select-sm {
  min-height: 2.75rem;
  padding: 0.75rem 2.5rem 0.75rem 0.75rem;
  font-size: 0.875rem;
}

.select-md {
  min-height: 3.5rem;
  padding: 1rem 3rem 1rem 1rem;
  font-size: 1rem;
}

.select-lg {
  min-height: 4rem;
  padding: 1.25rem 3.5rem 1.25rem 1.25rem;
  font-size: 1.125rem;
}

/* Responsive Design */
@media (max-width: 640px) {
  .dropdown-menu {
    left: -1rem;
    right: -1rem;
  }
  
  .select-lg {
    min-height: 3.5rem;
    padding: 1rem 3rem 1rem 1rem;
    font-size: 1rem;
  }
}
</style>