import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

export default defineConfig({
  plugins: [react()],
  server: {
    port: 5173,
    proxy: {
      '/api': {
        target: 'http://localhost:8080',
        changeOrigin: true,
        cookieDomainRewrite: 'localhost',
        headers: {
          Referer: 'http://localhost:5173',
          Origin: 'http://localhost:5173',
        },
      },
      '/sanctum': {
        target: 'http://localhost:8080',
        changeOrigin: true,
        cookieDomainRewrite: 'localhost',
        headers: {
          Referer: 'http://localhost:5173',
          Origin: 'http://localhost:5173',
        },
      },
      '/broadcasting': {
        target: 'http://localhost:8080',
        changeOrigin: true,
        cookieDomainRewrite: 'localhost',
        headers: {
          Referer: 'http://localhost:5173',
          Origin: 'http://localhost:5173',
        },
      },
      '/app': {
        target: 'ws://localhost:8080',
        ws: true,
        changeOrigin: true,
      },
      '/apps': {
        target: 'ws://localhost:8080',
        ws: true,
        changeOrigin: true,
      },
    },
  },
  build: {
    outDir: 'dist',
    sourcemap: false,
  },
})