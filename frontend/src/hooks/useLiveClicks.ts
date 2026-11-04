// src/hooks/useLiveClicks.ts

import { useEffect, useRef, useState } from 'react'
import echo from '../lib/echo'
import type { ClickEvent } from '../types'

export function useLiveClicks(userId: number | null, maxItems = 20) {
  const [clicks, setClicks] = useState<ClickEvent[]>([])
  const [isConnected, setIsConnected] = useState(false)
  const [error, setError] = useState<string | null>(null)
  const channelRef = useRef<any>(null)

  useEffect(() => {
    if (!userId) {
      console.log('[useLiveClicks] No userId, skipping subscription')
      return
    }

    const channelName = `user.${userId}`
    console.log(`[useLiveClicks] Subscribing to private-${channelName}`)

    try {
      const channel = echo.private(channelName)

      channel
        .listen('.click.recorded', (event: ClickEvent) => {
          console.log('[useLiveClicks] 🎯 Received click:', event)
          setClicks((prev) => [event, ...prev].slice(0, maxItems))
        })
        .error((err: any) => {
          console.error('[useLiveClicks] ❌ Channel error:', err)
          setError('Failed to subscribe to channel')
        })

      channelRef.current = channel

      const pusher = (echo as any).connector?.pusher

      if (pusher) {
        // ── 1. Read the CURRENT state immediately ──
        // The connection may already be established by the time the hook
        // mounts, so we can't rely only on future events.
        const currentState = pusher.connection.state
        console.log('[useLiveClicks] Current Pusher state:', currentState)
        if (currentState === 'connected') {
          setIsConnected(true)
          setError(null)
        }

        // ── 2. Subscribe to FUTURE state changes ──
        pusher.connection.bind('state_change', (states: any) => {
          console.log('[useLiveClicks] State change:', states.previous, '→', states.current)
          setIsConnected(states.current === 'connected')
          if (states.current === 'connected') {
            setError(null)
          }
        })

        pusher.connection.bind('error', (err: any) => {
          console.error('[useLiveClicks] ❌ Pusher error:', err)
          setError(String(err))
        })
      }
    } catch (err) {
      console.error('[useLiveClicks] ❌ Setup error:', err)
      setError(String(err))
    }

    return () => {
      if (channelRef.current) {
        console.log(`[useLiveClicks] Leaving channel ${channelName}`)
        echo.leave(channelName)
        channelRef.current = null
      }
    }
  }, [userId, maxItems])

  return { clicks, isConnected, error }
}