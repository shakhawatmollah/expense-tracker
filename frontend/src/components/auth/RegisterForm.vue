<template>
  <form @submit.prevent="handleSubmit" class="register-form">
    <!-- Error Alert -->
    <div v-if="error" class="error-alert">
      <div class="error-icon">
        <i class="fas fa-exclamation-triangle"></i>
      </div>
      <div class="error-content">
        <h4 class="error-title">Registration Failed</h4>
        <p class="error-message">{{ error }}</p>
      </div>
      <button @click="error = ''" class="error-close" type="button">
        <i class="fas fa-times"></i>
      </button>
    </div>

    <!-- Form Fields -->
    <div class="form-fields">
      <!-- Full Name Field -->
      <div class="form-group">
        <label for="name" class="form-label">
          <i class="fas fa-user label-icon"></i>
          Full Name
        </label>
        <div class="input-wrapper">
          <input
            id="name"
            v-model="form.name"
            type="text"
            required
            class="form-input"
            :class="{ 'input-error': validationErrors.name }"
            placeholder="Enter your full name"
            @blur="validateField('name')"
            @input="clearFieldError('name')"
          />
          <div class="input-icon">
            <i class="fas fa-user"></i>
          </div>
        </div>
        <div v-if="validationErrors.name" class="field-error">
          {{ validationErrors.name }}
        </div>
      </div>

      <!-- Email Field -->
      <div class="form-group">
        <label for="email" class="form-label">
          <i class="fas fa-envelope label-icon"></i>
          Email Address
        </label>
        <div class="input-wrapper">
          <input
            id="email"
            v-model="form.email"
            type="email"
            required
            class="form-input"
            :class="{ 'input-error': validationErrors.email }"
            placeholder="Enter your email address"
            @blur="validateField('email')"
            @input="clearFieldError('email')"
          />
          <div class="input-icon">
            <i class="fas fa-envelope"></i>
          </div>
        </div>
        <div v-if="validationErrors.email" class="field-error">
          {{ validationErrors.email }}
        </div>
      </div>

      <!-- Password Field -->
      <div class="form-group">
        <label for="password" class="form-label">
          <i class="fas fa-lock label-icon"></i>
          Password
        </label>
        <div class="input-wrapper">
          <input
            id="password"
            v-model="form.password"
            :type="showPassword ? 'text' : 'password'"
            required
            class="form-input"
            :class="{ 'input-error': validationErrors.password }"
            placeholder="Create a strong password"
            @blur="validateField('password')"
            @input="clearFieldError('password')"
          />
          <div class="input-icon">
            <i class="fas fa-lock"></i>
          </div>
          <button type="button" @click="showPassword = !showPassword" class="password-toggle">
            <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
          </button>
        </div>
        <div v-if="validationErrors.password" class="field-error">
          {{ validationErrors.password }}
        </div>
        <div class="password-strength">
          <div class="strength-bar">
            <div
              class="strength-fill"
              :class="`strength-${passwordStrength.level}`"
              :style="{ width: `${passwordStrength.percentage}%` }"
            ></div>
          </div>
          <span class="strength-text" :class="`strength-${passwordStrength.level}`">
            {{ passwordStrength.text }}
          </span>
        </div>
      </div>

      <!-- Confirm Password Field -->
      <div class="form-group">
        <label for="password_confirmation" class="form-label">
          <i class="fas fa-lock label-icon"></i>
          Confirm Password
        </label>
        <div class="input-wrapper">
          <input
            id="password_confirmation"
            v-model="form.password_confirmation"
            :type="showConfirmPassword ? 'text' : 'password'"
            required
            class="form-input"
            :class="{ 'input-error': validationErrors.password_confirmation }"
            placeholder="Confirm your password"
            @blur="validateField('password_confirmation')"
            @input="clearFieldError('password_confirmation')"
          />
          <div class="input-icon">
            <i class="fas fa-lock"></i>
          </div>
          <button type="button" @click="showConfirmPassword = !showConfirmPassword" class="password-toggle">
            <i :class="showConfirmPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
          </button>
        </div>
        <div v-if="validationErrors.password_confirmation" class="field-error">
          {{ validationErrors.password_confirmation }}
        </div>
      </div>
    </div>

    <!-- Submit Button -->
    <div class="form-actions">
      <button type="submit" :disabled="loading || !isFormValid" class="submit-btn" :class="{ 'btn-loading': loading }">
        <div v-if="loading" class="btn-spinner">
          <i class="fas fa-spinner fa-spin"></i>
        </div>
        <div v-else class="btn-content">
          <i class="fas fa-user-plus"></i>
          <span>Create Account</span>
        </div>
        <div class="btn-shine"></div>
      </button>
    </div>
  </form>
</template>

<script setup>
  import { reactive, ref, computed, watch } from 'vue'
  import { useRouter } from 'vue-router'
  import { useAuthStore } from '@/stores/auth'
  import { useToast } from '@/composables/useToast'

  const router = useRouter()
  const authStore = useAuthStore()
  const toast = useToast()

  const form = reactive({
    name: '',
    email: '',
    password: '',
    password_confirmation: ''
  })

  const loading = ref(false)
  const error = ref('')
  const showPassword = ref(false)
  const showConfirmPassword = ref(false)
  const validationErrors = ref({})

  // Form validation
  const validateField = field => {
    switch (field) {
      case 'name':
        if (!form.name.trim()) {
          validationErrors.value.name = 'Full name is required'
        } else if (form.name.trim().length < 2) {
          validationErrors.value.name = 'Name must be at least 2 characters'
        }
        break
      case 'email':
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
        if (!form.email) {
          validationErrors.value.email = 'Email is required'
        } else if (!emailRegex.test(form.email)) {
          validationErrors.value.email = 'Please enter a valid email address'
        }
        break
      case 'password':
        if (!form.password) {
          validationErrors.value.password = 'Password is required'
        } else if (form.password.length < 8) {
          validationErrors.value.password = 'Password must be at least 8 characters'
        }
        break
      case 'password_confirmation':
        if (!form.password_confirmation) {
          validationErrors.value.password_confirmation = 'Please confirm your password'
        } else if (form.password !== form.password_confirmation) {
          validationErrors.value.password_confirmation = 'Passwords do not match'
        }
        break
    }
  }

  const clearFieldError = field => {
    if (validationErrors.value[field]) {
      delete validationErrors.value[field]
    }
  }

  // Password strength calculation
  const passwordStrength = computed(() => {
    const password = form.password
    if (!password) return { level: 'none', percentage: 0, text: '' }

    let score = 0
    const checks = {
      length: password.length >= 8,
      lowercase: /[a-z]/.test(password),
      uppercase: /[A-Z]/.test(password),
      numbers: /\d/.test(password),
      special: /[!@#$%^&*(),.?":{}|<>]/.test(password)
    }

    score = Object.values(checks).filter(Boolean).length

    if (score <= 1) return { level: 'weak', percentage: 20, text: 'Weak' }
    if (score <= 2) return { level: 'fair', percentage: 40, text: 'Fair' }
    if (score <= 3) return { level: 'good', percentage: 60, text: 'Good' }
    if (score <= 4) return { level: 'strong', percentage: 80, text: 'Strong' }
    return { level: 'excellent', percentage: 100, text: 'Excellent' }
  })

  // Form validation state
  const isFormValid = computed(() => {
    return (
      form.name.trim() &&
      form.email &&
      form.password &&
      form.password_confirmation &&
      form.password === form.password_confirmation &&
      Object.keys(validationErrors.value).length === 0
    )
  })

  // Watch for password confirmation matching
  watch(
    () => form.password,
    () => {
      if (form.password_confirmation && form.password !== form.password_confirmation) {
        validationErrors.value.password_confirmation = 'Passwords do not match'
      } else if (validationErrors.value.password_confirmation) {
        delete validationErrors.value.password_confirmation
      }
    }
  )

  const handleSubmit = async () => {
    // Validate all fields
    Object.keys(form).forEach(validateField)

    if (Object.keys(validationErrors.value).length > 0) {
      return
    }

    loading.value = true
    error.value = ''

    try {
      await authStore.register(form)
      toast.success('Account created successfully! Welcome aboard!', 'Registration Complete')
      router.push('/')
    } catch (err) {
      const errorMessage = err.response?.data?.message || 'Registration failed. Please try again.'
      error.value = errorMessage
      toast.error(errorMessage, 'Registration Failed')
    } finally {
      loading.value = false
    }
  }
</script>

<style scoped>
  /* Modern Register Form Styles */
  .register-form {
    width: 100%;
  }

  /* Error Alert */
  .error-alert {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    padding: 1rem 1.25rem;
    background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
    border: 1px solid #fecaca;
    border-radius: 12px;
    margin-bottom: 1.5rem;
    animation: slideInDown 0.3s ease-out;
  }

  .error-icon {
    flex-shrink: 0;
    width: 20px;
    height: 20px;
    color: #dc2626;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .error-content {
    flex: 1;
  }

  .error-title {
    font-size: 0.875rem;
    font-weight: 600;
    color: #dc2626;
    margin: 0 0 0.25rem 0;
  }

  .error-message {
    font-size: 0.8125rem;
    color: #b91c1c;
    margin: 0;
    line-height: 1.4;
  }

  .error-close {
    flex-shrink: 0;
    width: 20px;
    height: 20px;
    border: none;
    background: none;
    color: #dc2626;
    cursor: pointer;
    border-radius: 4px;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .error-close:hover {
    background: rgba(220, 38, 38, 0.1);
    color: #b91c1c;
  }

  /* Form Fields */
  .form-fields {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    margin-bottom: 2rem;
  }

  .form-group {
    position: relative;
  }

  .form-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
    transition: color 0.3s ease;
  }

  .label-icon {
    font-size: 0.75rem;
    color: #6b7280;
  }

  .input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
  }

  .form-input {
    width: 100%;
    padding: 0.875rem 1rem 0.875rem 3rem;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    font-size: 1rem;
    color: #1f2937;
    background: white;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  }

  .form-input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow:
      0 0 0 3px rgba(102, 126, 234, 0.1),
      0 4px 12px rgba(0, 0, 0, 0.1);
    transform: translateY(-1px);
  }

  .form-input.input-error {
    border-color: #ef4444;
    box-shadow:
      0 0 0 3px rgba(239, 68, 68, 0.1),
      0 4px 12px rgba(239, 68, 68, 0.1);
  }

  .form-input::placeholder {
    color: #9ca3af;
    font-size: 0.9375rem;
  }

  .input-icon {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
    font-size: 0.875rem;
    pointer-events: none;
    transition: color 0.3s ease;
    z-index: 1;
  }

  .form-input:focus + .input-icon {
    color: #667eea;
  }

  .form-input.input-error + .input-icon {
    color: #ef4444;
  }

  .password-toggle {
    position: absolute;
    right: 1rem;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #6b7280;
    cursor: pointer;
    padding: 0.25rem;
    border-radius: 6px;
    transition: all 0.2s ease;
    z-index: 2;
  }

  .password-toggle:hover {
    color: #374151;
    background: rgba(107, 114, 128, 0.1);
  }

  .field-error {
    margin-top: 0.5rem;
    font-size: 0.8125rem;
    color: #ef4444;
    display: flex;
    align-items: center;
    gap: 0.375rem;
    animation: slideInLeft 0.3s ease-out;
  }

  .field-error::before {
    content: '⚠';
    font-size: 0.75rem;
  }

  /* Password Strength Indicator */
  .password-strength {
    margin-top: 0.75rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
  }

  .strength-bar {
    flex: 1;
    height: 6px;
    background: #e5e7eb;
    border-radius: 3px;
    overflow: hidden;
  }

  .strength-fill {
    height: 100%;
    border-radius: 3px;
    transition: all 0.3s ease;
    background: linear-gradient(90deg, #ef4444 0%, #f59e0b 25%, #eab308 50%, #22c55e 75%, #16a34a 100%);
  }

  .strength-fill.strength-weak {
    background: #ef4444;
  }

  .strength-fill.strength-fair {
    background: linear-gradient(90deg, #ef4444 0%, #f59e0b 100%);
  }

  .strength-fill.strength-good {
    background: linear-gradient(90deg, #f59e0b 0%, #eab308 100%);
  }

  .strength-fill.strength-strong {
    background: linear-gradient(90deg, #eab308 0%, #22c55e 100%);
  }

  .strength-fill.strength-excellent {
    background: linear-gradient(90deg, #22c55e 0%, #16a34a 100%);
  }

  .strength-text {
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.025em;
    min-width: 60px;
    text-align: right;
  }

  .strength-text.strength-weak {
    color: #ef4444;
  }
  .strength-text.strength-fair {
    color: #f59e0b;
  }
  .strength-text.strength-good {
    color: #eab308;
  }
  .strength-text.strength-strong {
    color: #22c55e;
  }
  .strength-text.strength-excellent {
    color: #16a34a;
  }

  /* Submit Button */
  .form-actions {
    margin-top: 2rem;
  }

  .submit-btn {
    position: relative;
    width: 100%;
    padding: 1rem 2rem;
    border: none;
    border-radius: 16px;
    font-size: 1rem;
    font-weight: 700;
    color: white;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    cursor: pointer;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow:
      0 8px 32px rgba(102, 126, 234, 0.3),
      0 4px 16px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .submit-btn:not(:disabled):hover {
    transform: translateY(-2px);
    box-shadow:
      0 12px 40px rgba(102, 126, 234, 0.4),
      0 6px 20px rgba(0, 0, 0, 0.15);
  }

  .submit-btn:not(:disabled):active {
    transform: translateY(0);
    box-shadow:
      0 4px 16px rgba(102, 126, 234, 0.3),
      0 2px 8px rgba(0, 0, 0, 0.1);
  }

  .submit-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
    box-shadow:
      0 4px 16px rgba(102, 126, 234, 0.2),
      0 2px 8px rgba(0, 0, 0, 0.05);
  }

  .btn-loading {
    pointer-events: none;
  }

  .btn-spinner {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    font-size: 1.125rem;
  }

  .btn-content {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    font-size: 0.9375rem;
  }

  .btn-shine {
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.6s ease;
  }

  .submit-btn:not(:disabled):hover .btn-shine {
    left: 100%;
  }

  /* Animations */
  @keyframes slideInDown {
    from {
      opacity: 0;
      transform: translateY(-10px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  @keyframes slideInLeft {
    from {
      opacity: 0;
      transform: translateX(-10px);
    }
    to {
      opacity: 1;
      transform: translateX(0);
    }
  }

  /* Mobile responsiveness */
  @media (max-width: 480px) {
    .form-input {
      padding: 0.75rem 0.875rem 0.75rem 2.75rem;
      font-size: 0.9375rem;
    }

    .input-icon {
      left: 0.875rem;
      font-size: 0.8125rem;
    }

    .password-toggle {
      right: 0.875rem;
    }

    .submit-btn {
      padding: 0.875rem 1.5rem;
      font-size: 0.9375rem;
    }

    .error-alert {
      padding: 0.875rem 1rem;
    }
  }
</style>
