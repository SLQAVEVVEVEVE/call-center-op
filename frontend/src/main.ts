import { createApp } from 'vue'
import { createPinia } from 'pinia'

import App from './App.vue'
import router from './router'
import { useAuthStore } from './stores/auth'
import { bootEcho } from './plugins/echo'
import './assets/main.css'

const app = createApp(App)
app.use(createPinia())
app.use(router)
await useAuthStore().restore()
bootEcho()
app.mount('#app')
