import axios from 'axios'
import { Preferences } from '@capacitor/preferences'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL,
  headers: { Accept: 'application/json' },
})

api.interceptors.request.use(async (config) => {
  const { value: token } = await Preferences.get({ key: 'auth_token' })
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

api.interceptors.response.use(
  (response) => response,
  async (error) => {
    if (error.response?.status === 401) {
      await Preferences.remove({ key: 'auth_token' })
      import('../router').then((m) => m.default.push('/login'))
    }
    return Promise.reject(error)
  },
)

export default api
