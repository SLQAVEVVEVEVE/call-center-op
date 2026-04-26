import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import LoginView from '@/views/LoginView.vue'
import RegisterView from '@/views/RegisterView.vue'
import ChatsView from '@/views/ChatsView.vue'
import ChatView from '@/views/ChatView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    { path: '/login', component: LoginView, meta: { guest: true } },
    { path: '/register', component: RegisterView, meta: { guest: true } },
    { path: '/chats', component: ChatsView, meta: { auth: true } },
    { path: '/chats/:id', component: ChatView, meta: { auth: true } },
    { path: '/', redirect: '/chats' },
  ],
})

router.beforeEach(async (to) => {
  const auth = useAuthStore()
  if (!auth.restored) await auth.restore()
  if (to.meta.auth && !auth.token) return '/login'
  if (to.meta.guest && auth.token) return '/chats'
})

export default router
