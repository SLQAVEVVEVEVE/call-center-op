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
    router.push('/dashboard')
  } catch (e: any) {
    error.value = e.response?.data?.message ?? 'Login failed'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div
    class="min-h-dvh flex items-center justify-center px-4 bg-gradient-to-br from-slate-100 to-slate-50"
  >
    <form class="soft-card w-full max-w-sm flex flex-col gap-4" @submit.prevent="submit">
      <h1 class="text-2xl font-bold text-[var(--color-ink)] m-0">Вход</h1>
      <AppInput v-model="email" type="email" placeholder="Email" :required="true" />
      <AppInput v-model="password" type="password" placeholder="Пароль" :required="true" />
      <p v-if="error" class="text-sm text-red-600 m-0">{{ error }}</p>
      <AppButton type="submit" :disabled="loading">
        {{ loading ? 'Входим…' : 'Войти' }}
      </AppButton>
      <p class="text-sm text-center text-[var(--color-ink-muted)] m-0">
        Нет аккаунта?
        <router-link to="/register" class="text-[var(--color-brand-500)] font-semibold">
          Зарегистрироваться
        </router-link>
      </p>
    </form>
  </div>
</template>
