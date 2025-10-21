<template>
  <form @submit.prevent="handleSubmit" class="login-form">
    <!-- Error Message -->
    <div v-if="error" class="error-message">
      <div class="error-icon">
        <i class="fas fa-exclamation-triangle"></i>
      </div>
      <div class="error-content">
        <p class="error-title">Authentication Failed</p>
        <p class="error-text">{{ error }}</p>
      </div>
    </div>

    <!-- Form Fields -->
    <div class="form-fields">
      <!-- Email Field -->
      <div class="form-group">
        <label for="email" class="modern-label">
          <i class="fas fa-envelope label-icon"></i>
          Email Address
        </label>
        <div class="input-container">
          <input
            id="email"
            v-model="form.email"
            type="email"
            required
            class="modern-input"
            placeholder="Enter your email address"
            :class="{ 'input-error': emailError }"
            @blur="validateEmail"
            @input="clearEmailError"
          />
          <div class="input-icon">
            <i class="fas fa-envelope"></i>
          </div>
          <div v-if="emailError" class="field-error">
            {{ emailError }}
          </div>
        </div>
      </div>

      <!-- Password Field -->
      <div class="form-group">
        <label for="password" class="modern-label">
          <i class="fas fa-lock label-icon"></i>
          Password
        </label>
        <div class="input-container">
          <input
            id="password"
            v-model="form.password"
            :type="showPassword ? 'text' : 'password'"
            required
            class="modern-input"
            placeholder="Enter your password"
            :class="{ 'input-error': passwordError }"
            @blur="validatePassword"
            @input="clearPasswordError"
          />
          <div class="input-icon">
            <i class="fas fa-lock"></i>
          </div>
          <button
            type="button"
            class="password-toggle"
            @click="togglePassword"
            :aria-label="showPassword ? 'Hide password' : 'Show password'"
          >
            <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
          </button>
          <div v-if="passwordError" class="field-error">
            {{ passwordError }}
          </div>
        </div>
      </div>

      <!-- Remember Me & Forgot Password -->
      <div class="form-options">
        <label class="remember-checkbox">
          <input type="checkbox" v-model="rememberMe" class="checkbox-input" />
          <span class="checkbox-custom">
            <i class="fas fa-check"></i>
          </span>
          <span class="checkbox-label">Remember me</span>
        </label>

        <a href="#" class="forgot-password" @click.prevent="handleForgotPassword">Forgot password?</a>
      </div>
    </div>

    <!-- Submit Button -->
    <div class="form-submit">
      <Button
        type="submit"
        :loading="loading"
        :disabled="loading || !isFormValid"
        variant="primary"
        size="lg"
        class="login-button"
        elevated
      >
        <template v-if="!loading">
          <i class="fas fa-sign-in-alt"></i>
          Sign In to Your Account
        </template>
        <template v-else>
          <i class="fas fa-spinner fa-spin"></i>
          Signing In...
        </template>
      </Button>
    </div>

    <!-- Additional options removed: social login buttons were intentionally omitted -->
  </form>
</template>

<script setup>
  import { reactive, ref, computed } from 'vue'
  import { useRouter } from 'vue-router'
  import { useAuthStore } from '@/stores/auth'
  import { useToast } from '@/composables/useToast'
  import Button from '@/components/common/Button.vue'

  const router = useRouter()
  const authStore = useAuthStore()
  const toast = useToast()

  // Form data
  const form = reactive({
    email: '',
    password: ''
  })

  // Form state
  const loading = ref(false)
  const error = ref('')
  const emailError = ref('')
  const passwordError = ref('')
  const showPassword = ref(false)
  const rememberMe = ref(false)

  // Computed properties
  const isFormValid = computed(() => {
    return form.email && form.password && !emailError.value && !passwordError.value && isValidEmail(form.email)
  })

  // Validation functions
  const isValidEmail = email => {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
    return emailRegex.test(email)
  }

  const validateEmail = () => {
    if (!form.email) {
      emailError.value = 'Email is required'
    } else if (!isValidEmail(form.email)) {
      emailError.value = 'Please enter a valid email address'
    } else {
      emailError.value = ''
    }
  }

  const validatePassword = () => {
    if (!form.password) {
      passwordError.value = 'Password is required'
    } else if (form.password.length < 6) {
      passwordError.value = 'Password must be at least 6 characters'
    } else {
      passwordError.value = ''
    }
  }

  const clearEmailError = () => {
    if (emailError.value) emailError.value = ''
  }

  const clearPasswordError = () => {
    if (passwordError.value) passwordError.value = ''
  }

  // Form handlers
  const togglePassword = () => {
    showPassword.value = !showPassword.value
  }

  const handleSubmit = async () => {
    // Validate form before submission
    validateEmail()
    validatePassword()

    if (!isFormValid.value) {
      toast.error('Please fix the form errors before submitting', 'Validation Error')
      return
    }

    loading.value = true
    error.value = ''

    try {
      const loginData = {
        email: form.email,
        password: form.password,
        remember: rememberMe.value
      }

      // Wait for login to complete and auth state to be fully set
      await authStore.login(loginData)

      toast.success('Welcome back!', 'Login Successful', { duration: 8000 })

      // Small delay to ensure auth state is updated before navigation
      await new Promise(resolve => setTimeout(resolve, 100))
      
      // Navigate to dashboard
      await router.push({ name: 'Dashboard' })
    } catch (err) {
      console.error('Login error:', err)

      if (err.response?.status === 422) {
        // Handle validation errors
        const errors = err.response.data.errors
        if (errors.email) emailError.value = errors.email[0]
        if (errors.password) passwordError.value = errors.password[0]
      } else if (err.response?.status === 401) {
        error.value = 'Invalid email or password. Please try again.'
      } else if (err.response?.status === 429) {
        error.value = 'Too many login attempts. Please try again later.'
      } else {
        error.value = err.response?.data?.message || 'Unable to sign in. Please check your connection and try again.'
      }

      toast.error(error.value || 'Login failed', 'Authentication Error')
    } finally {
      loading.value = false
    }
  }

  const handleForgotPassword = () => {
    toast.info('Password reset feature coming soon!', 'Feature Preview')
    // TODO: Implement forgot password functionality
  }

  const handleSocialLogin = provider => {
    toast.info(`${provider} login coming soon!`, 'Feature Preview')
    // TODO: Implement social login
  }
</script>

<style scoped>
  /* Login Form */
  .login-form {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
  }

  /* Error Message */
  .error-message {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    padding: 1rem 1.25rem;
    background: linear-gradient(135deg, rgba(239, 68, 68, 0.1), rgba(220, 38, 38, 0.05));
    border: 1px solid rgba(239, 68, 68, 0.3);
    border-radius: 12px;
    animation: errorSlideIn 0.4s ease-out;
  }

  @keyframes errorSlideIn {
    from {
      opacity: 0;
      transform: translateY(-10px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  .error-icon {
    width: 20px;
    height: 20px;
    color: #ef4444;
    flex-shrink: 0;
    margin-top: 2px;
  }

  .error-content {
    flex: 1;
  }

  .error-title {
    font-weight: 600;
    color: #dc2626;
    font-size: 0.875rem;
    margin-bottom: 0.25rem;
  }

  .error-text {
    color: #b91c1c;
    font-size: 0.8125rem;
    line-height: 1.4;
  }

  /* Form Fields */
  .form-fields {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
  }

  .form-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
  }

  /* Modern Label */
  .modern-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 600;
    color: #374151;
    font-size: 0.875rem;
    letter-spacing: 0.025em;
  }

  .label-icon {
    font-size: 0.8125rem;
    color: #667eea;
  }

  /* Input Container */
  .input-container {
    position: relative;
  }

  /* Modern Input */
  .modern-input {
    width: 100%;
    padding: 0.875rem 1rem 0.875rem 3rem;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    background: #fafafa;
    font-size: 0.9375rem;
    color: #1f2937;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    outline: none;
  }

  .modern-input::placeholder {
    color: #9ca3af;
    font-weight: 400;
  }

  .modern-input:focus {
    border-color: #667eea;
    background: white;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    transform: translateY(-1px);
  }

  .modern-input:hover:not(:focus) {
    border-color: #d1d5db;
    background: #f9fafb;
  }

  .modern-input.input-error {
    border-color: #ef4444;
    background: rgba(254, 242, 242, 0.5);
  }

  .modern-input.input-error:focus {
    border-color: #ef4444;
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
  }

  /* Input Icon */
  .input-icon {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
    font-size: 0.875rem;
    pointer-events: none;
    transition: color 0.3s ease;
  }

  .modern-input:focus + .input-icon {
    color: #667eea;
  }

  .modern-input.input-error + .input-icon {
    color: #ef4444;
  }

  /* Password Toggle */
  .password-toggle {
    position: absolute;
    right: 1rem;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #9ca3af;
    cursor: pointer;
    padding: 0.25rem;
    border-radius: 6px;
    transition: all 0.2s ease;
    font-size: 0.875rem;
  }

  .password-toggle:hover {
    color: #667eea;
    background: rgba(102, 126, 234, 0.1);
  }

  .password-toggle:focus {
    outline: 2px solid #667eea;
    outline-offset: 2px;
  }

  /* Field Error */
  .field-error {
    color: #ef4444;
    font-size: 0.8125rem;
    margin-top: 0.25rem;
    font-weight: 500;
    animation: errorFadeIn 0.3s ease-out;
  }

  @keyframes errorFadeIn {
    from {
      opacity: 0;
      transform: translateY(-5px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  /* Form Options */
  .form-options {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    margin-top: 0.5rem;
  }

  /* Remember Checkbox */
  .remember-checkbox {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    cursor: pointer;
    user-select: none;
  }

  .checkbox-input {
    display: none;
  }

  .checkbox-custom {
    width: 18px;
    height: 18px;
    border: 2px solid #d1d5db;
    border-radius: 4px;
    background: white;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    flex-shrink: 0;
  }

  .checkbox-custom i {
    font-size: 10px;
    color: white;
    opacity: 0;
    transform: scale(0);
    transition: all 0.2s ease;
  }

  .checkbox-input:checked + .checkbox-custom {
    background: linear-gradient(135deg, #667eea, #764ba2);
    border-color: #667eea;
  }

  .checkbox-input:checked + .checkbox-custom i {
    opacity: 1;
    transform: scale(1);
  }

  .checkbox-label {
    font-size: 0.875rem;
    color: #6b7280;
    font-weight: 500;
  }

  /* Forgot Password */
  .forgot-password {
    color: #667eea;
    text-decoration: none;
    font-size: 0.875rem;
    font-weight: 500;
    transition: all 0.2s ease;
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
  }

  .forgot-password:hover {
    color: #5a6fd8;
    background: rgba(102, 126, 234, 0.1);
  }

  /* Form Submit */
  .form-submit {
    margin-top: 1rem;
  }

  .login-button {
    width: 100%;
    font-size: 1rem !important;
    font-weight: 700 !important;
    padding: 1rem 1.5rem !important;
    border-radius: 12px !important;
    letter-spacing: 0.025em;
    gap: 0.75rem !important;
  }

  /* Additional Options */
  .additional-options {
    margin-top: 1.5rem;
  }

  .divider {
    position: relative;
    text-align: center;
    margin: 1.5rem 0;
  }

  .divider::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    height: 1px;
    background: linear-gradient(to right, transparent, #e5e7eb 20%, #e5e7eb 80%, transparent);
  }

  .divider-text {
    background: rgba(255, 255, 255, 0.95);
    padding: 0 1rem;
    color: #9ca3af;
    font-size: 0.8125rem;
    font-weight: 500;
  }

  /* Social Login */
  .social-login {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.75rem;
  }

  .social-button {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.75rem 1rem;
    border: 2px solid #e5e7eb;
    border-radius: 10px;
    background: white;
    color: #6b7280;
    font-weight: 600;
    font-size: 0.8125rem;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
  }

  .social-button:hover {
    border-color: #d1d5db;
    background: #f9fafb;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  }

  .social-button.google:hover {
    border-color: #ea4335;
    color: #ea4335;
    box-shadow: 0 4px 12px rgba(234, 67, 53, 0.2);
  }

  .social-button.github:hover {
    border-color: #24292e;
    color: #24292e;
    box-shadow: 0 4px 12px rgba(36, 41, 46, 0.2);
  }

  .social-button i {
    font-size: 0.875rem;
  }

  /* Responsive Design */
  @media (max-width: 480px) {
    .form-options {
      flex-direction: column;
      align-items: flex-start;
      gap: 0.75rem;
    }

    .social-login {
      grid-template-columns: 1fr;
    }

    .modern-input {
      padding: 0.8125rem 1rem 0.8125rem 2.75rem;
      font-size: 16px; /* Prevent zoom on iOS */
    }

    .input-icon {
      left: 0.875rem;
    }

    .password-toggle {
      right: 0.875rem;
    }
  }

  /* Focus improvements for accessibility */
  .remember-checkbox:focus-within .checkbox-custom {
    outline: 2px solid #667eea;
    outline-offset: 2px;
  }

  .forgot-password:focus-visible {
    outline: 2px solid #667eea;
    outline-offset: 2px;
  }

  .social-button:focus-visible {
    outline: 2px solid #667eea;
    outline-offset: 2px;
  }

  /* High contrast mode support */
  @media (prefers-contrast: high) {
    .modern-input {
      border-color: #000;
    }

    .modern-input:focus {
      border-color: #0066cc;
    }

    .checkbox-custom {
      border-color: #000;
    }

    .social-button {
      border-color: #000;
    }
  }

  /* Reduced motion support */
  @media (prefers-reduced-motion: reduce) {
    .error-message {
      animation: none;
    }

    .field-error {
      animation: none;
    }

    .modern-input {
      transition: none;
    }

    .checkbox-custom i {
      transition: none;
    }
  }
</style>
