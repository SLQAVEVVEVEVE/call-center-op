import { configureEcho } from '@laravel/echo-vue'
import { useAuthStore } from '@/stores/auth'

/**
 * Resolve the Reverb WebSocket host. If the env value is empty or the literal
 * "auto", fall back to whatever host the page was loaded from. This lets the
 * same build run from localhost, home WiFi, the phone hotspot, or the APK
 * WebView without rebuilding.
 */
function resolveReverbHost(): string {
  const envHost = import.meta.env.VITE_REVERB_HOST
  if (!envHost || envHost === 'auto') {
    return window.location.hostname
  }
  return envHost
}

export function bootEcho() {
  const auth = useAuthStore()
  configureEcho({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: resolveReverbHost(),
    wsPort: Number(import.meta.env.VITE_REVERB_PORT ?? 80),
    wssPort: Number(import.meta.env.VITE_REVERB_PORT ?? 443),
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
    authorizer: (channel: { name: string }) => ({
      authorize: (socketId: string, callback: (err: Error | null, data: any) => void) => {
        fetch(`${import.meta.env.VITE_API_URL}/broadcasting/auth`, {
          method: 'POST',
          headers: {
            Authorization: `Bearer ${auth.token}`,
            Accept: 'application/json',
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({ socket_id: socketId, channel_name: channel.name }),
        })
          .then((r) =>
            r.ok
              ? r.json().then((d) => callback(null, d))
              : r.text().then((t) => callback(new Error(t), null)),
          )
          .catch((e) => callback(e, null))
      },
    }),
  })
}
