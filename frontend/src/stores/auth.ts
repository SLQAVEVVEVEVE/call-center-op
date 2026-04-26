import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import { Preferences } from '@capacitor/preferences'
import api from '@/api/axios'

interface User {
  id: number
  name: string
  email: string
}

export const useAuthStore = defineStore('auth', () => {
  const token = ref<string | null>(null)
  const user = ref<User | null>(null)
  const restored = ref(false)

  const isAuthed = computed(() => !!token.value)

  async function restore() {
    const { value } = await Preferences.get({ key: 'auth_token' })
    if (value) {
      token.value = value
      try {
        const { data } = await api.get('/me')
        user.value = data
      } catch {
        token.value = null
        await Preferences.remove({ key: 'auth_token' })
      }
    }
    restored.value = true
  }

  async function login(email: string, password: string) {
    const { data } = await api.post('/login', { email, password, device_name: 'web' })
    token.value = data.token
    user.value = data.user
    await Preferences.set({ key: 'auth_token', value: data.token })
  }

  async function register(payload: {
    name: string
    email: string
    password: string
    password_confirmation: string
  }) {
    const { data } = await api.post('/register', { ...payload, device_name: 'web' })
    token.value = data.token
    user.value = data.user
    await Preferences.set({ key: 'auth_token', value: data.token })
  }

  async function logout() {
    try {
      await api.post('/logout')
    } finally {
      await Preferences.remove({ key: 'auth_token' })
      token.value = null
      user.value = null
    }
  }

  return { token, user, restored, isAuthed, restore, login, register, logout }
})
