<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import AppInput from '@/components/AppInput.vue'
import AppButton from '@/components/AppButton.vue'

const router = useRouter()
const auth = useAuthStore()

const name = ref('')
const email = ref('')
const password = ref('')
const passwordConfirmation = ref('')
const error = ref('')
const loading = ref(false)

async function submit() {
  error.value = ''
  loading.value = true
  try {
    await auth.register({
      name: name.value,
      email: email.value,
      password: password.value,
      password_confirmation: passwordConfirmation.value,
    })
    router.push('/dashboard')
  } catch (e: any) {
    error.value = e.response?.data?.message ?? 'Registration failed'
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
      <h1 class="text-2xl font-bold text-[var(--color-ink)] m-0">Регистрация</h1>
      <AppInput v-model="name" placeholder="Имя" :required="true" />
      <AppInput v-model="email" type="email" placeholder="Email" :required="true" />
      <AppInput v-model="password" type="password" placeholder="Пароль" :required="true" />
      <AppInput
        v-model="passwordConfirmation"
        type="password"
        placeholder="Повторите пароль"
        :required="true"
      />
      <p v-if="error" class="text-sm text-red-600 m-0">{{ error }}</p>
      <AppButton type="submit" :disabled="loading">
        {{ loading ? 'Создаём…' : 'Создать аккаунт' }}
      </AppButton>
      <p class="text-sm text-center text-[var(--color-ink-muted)] m-0">
        Уже есть аккаунт?
        <router-link to="/login" class="text-[var(--color-brand-500)] font-semibold">
          Войти
        </router-link>
      </p>
    </form>
  </div>
</template>
