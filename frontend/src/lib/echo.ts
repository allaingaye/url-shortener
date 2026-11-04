// src/lib/echo.ts

import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

declare global {
  interface Window {
    Pusher: typeof Pusher
  }
}
window.Pusher = Pusher

// In dev, frontend is on :5173, backend is on :8080 (proxied)
// In prod, frontend and backend share the same host
const isDev = import.meta.env.DEV
const wsHost = isDev ? 'localhost' : window.location.hostname
const wsPort = isDev ? 8080 : (window.location.port ? parseInt(window.location.port) : 80)

const reverbKey = import.meta.env.VITE_REVERB_APP_KEY

if (!reverbKey) {
  console.warn(
    '[echo] VITE_REVERB_APP_KEY is not set. WebSocket features will not work.'
  )
}

// Reverb speaks the Pusher protocol — use the `pusher` broadcaster.
// The `reverb` broadcaster alias only exists in Laravel 11+ docs and
// doesn't always work with older laravel-echo versions.
export const echo = new Echo({
  broadcaster: 'pusher',
  key: reverbKey,
  wsHost,
  wsPort,
  wssPort: wsPort,
  forceTLS: false,
  enabledTransports: ['ws'],
  disableStats: true,
  cluster: 'mt1', // required by pusher-js even if unused
  authEndpoint: '/broadcasting/auth',
  auth: {
    headers: {
      Accept: 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    },
  },
})

export default echo