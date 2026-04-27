import { fileURLToPath, URL } from 'node:url'

import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import vueDevTools from 'vite-plugin-vue-devtools'
import tailwindcss from '@tailwindcss/vite'

// Backend reachable from inside the host running Vite. Docker maps nginx → 8000
// and Reverb → 8080 to 0.0.0.0, so localhost works regardless of which LAN the
// laptop is on. The browser only ever needs to reach Vite (port 5173); Vite
// proxies REST + broadcasting auth to the backend, so the same dev server
// works from localhost, home WiFi, hotspot, or APK WebView without .env edits.
const BACKEND = 'http://localhost:8000'

export default defineConfig({
  plugins: [tailwindcss(), vue(), vueDevTools()],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url)),
    },
  },
  server: {
    host: true,
    proxy: {
      '/api': { target: BACKEND, changeOrigin: true },
      '/broadcasting': { target: BACKEND, changeOrigin: true },
      '/storage': { target: BACKEND, changeOrigin: true },
    },
  },
})
