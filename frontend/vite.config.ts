import { fileURLToPath, URL } from 'node:url'

import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import vueDevTools from 'vite-plugin-vue-devtools'
import tailwindcss from '@tailwindcss/vite'

// Backend target for Vite's dev-server proxy.
// - When Vite runs on the host: env is unset → defaults to localhost:8000
//   (Docker maps nginx → 8000 on 0.0.0.0).
// - When Vite runs inside Docker: docker-compose sets VITE_BACKEND_URL=
//   http://nginx:80 so the proxy targets the nginx container directly via
//   the shared appnet network.
// The browser only ever needs to reach Vite (port 5173); Vite proxies REST
// + broadcasting auth + storage URLs to the backend, so the same dev server
// works from localhost, home WiFi, hotspot, or APK WebView with no env edits.
const BACKEND = process.env.VITE_BACKEND_URL || 'http://localhost:8000'

// chokidar polling for bind-mounted source on Windows/WSL2 (set in compose).
const USE_POLLING = process.env.CHOKIDAR_USEPOLLING === 'true'

export default defineConfig({
  plugins: [tailwindcss(), vue(), vueDevTools()],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url)),
    },
  },
  server: {
    host: true,
    watch: USE_POLLING ? { usePolling: true, interval: 500 } : undefined,
    proxy: {
      '/api': { target: BACKEND, changeOrigin: true },
      '/broadcasting': { target: BACKEND, changeOrigin: true },
      '/storage': { target: BACKEND, changeOrigin: true },
    },
  },
})
