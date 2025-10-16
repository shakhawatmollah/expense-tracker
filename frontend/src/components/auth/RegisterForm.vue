<template>
  <form @submit.prevent="handleSubmit" class="mt-8 space-y-6">
    <div v-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
      {{ error }}
    </div>
    
    <div class="space-y-4">
      <div>
        <label for="name" class="form-label">Full Name</label>
        <input
          id="name"
          v-model="form.name"
          type="text"
          required
          class="form-input"
          placeholder="Enter your full name"
        />
      </div>
      
      <div>
        <label for="email" class="form-label">Email address</label>
        <input
          id="email"
          v-model="form.email"
          type="email"
          required
          class="form-input"
          placeholder="Enter your email"
        />
      </div>
      
      <div>
        <label for="password" class="form-label">Password</label>
        <input
          id="password"
          v-model="form.password"
          type="password"
          required
          class="form-input"
          placeholder="Enter your password"
        />
      </div>
      
      <div>
        <label for="password_confirmation" class="form-label">Confirm Password</label>
        <input
          id="password_confirmation"
          v-model="form.password_confirmation"
          type="password"
          required
          class="form-input"
          placeholder="Confirm your password"
        />
      </div>
    </div>

    <div>
      <Button
        type="submit"
        :loading="loading"
        :disabled="loading"
        class="w-full"
      >
        Create Account
      </Button>
    </div>
  </form>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import Button from '@/components/common/Button.vue'

const router = useRouter()
const authStore = useAuthStore()

const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: ''
})

const loading = ref(false)
const error = ref('')

const handleSubmit = async () => {
  loading.value = true
  error.value = ''
  
  if (form.password !== form.password_confirmation) {
    error.value = 'Passwords do not match'
    loading.value = false
    return
  }
  
  try {
    await authStore.register(form)
    router.push('/')
  } catch (err) {
    error.value = err.response?.data?.message || 'Registration failed'
  } finally {
    loading.value = false
  }
}
</script>