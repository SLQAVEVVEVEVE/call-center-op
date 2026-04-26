<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import AppInput from '@/components/AppInput.vue'
import AppButton from '@/components/AppButton.vue'

const router = useRouter()
const auth = useAuthStore()

const email = ref('')
const password = ref('')
const error = ref('')
const loading = ref(false)

async function submit() {
  error.value = ''
  loading.value = true
  try {
    await auth.login(email.value, password.value)
    router.push('/chats')
  } catch (e: any) {
    error.value = e.response?.data?.message ?? 'Login failed'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="auth-wrap">
    <form class="soft-card auth-card" @submit.prevent="submit">
      <h1 class="auth-title">Sign In</h1>
      <AppInput v-model="email" type="email" placeholder="Email" :required="true" />
      <AppInput v-model="password" type="password" placeholder="Password" :required="true" />
      <p v-if="error" class="auth-error">{{ error }}</p>
      <AppButton type="submit" :disabled="loading">{{ loading ? 'Signing in…' : 'Sign In' }}</AppButton>
      <p class="auth-link">No account? <router-link to="/register">Register</router-link></p>
    </form>
  </div>
</template>

<style scoped>
.auth-wrap {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #e8edf5 0%, #f0f4fb 100%);
}
.auth-card {
  width: 100%;
  max-width: 380px;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}
.auth-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0;
}
.auth-error {
  color: #dc2626;
  font-size: 0.875rem;
  margin: 0;
}
.auth-link {
  font-size: 0.875rem;
  text-align: center;
  color: #64748b;
  margin: 0;
}
.auth-link a {
  color: #2563eb;
  font-weight: 600;
  text-decoration: none;
}
</style>
