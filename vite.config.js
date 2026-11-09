import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { fileURLToPath, URL } from 'node:url'

// Builds to public/assets (served by PHP). `npm run dev` proxies /api to the PHP server on :8000.
export default defineConfig(({ command }) => ({
  plugins: [vue()],
  resolve: { alias: { '@': fileURLToPath(new URL('./resources/js', import.meta.url)) } },
  base: command === 'build' ? '/assets/' : '/',
  build: {
    outDir: 'public/assets', emptyOutDir: false, cssCodeSplit: false,
    rollupOptions: { input: 'resources/js/main.js', output: {
      entryFileNames: 'js/app.js', chunkFileNames: 'js/[name].js',
      assetFileNames: (a) => (a.name || '').endsWith('.css') ? 'css/app.css' : 'img/[name][extname]' } }
  },
  server: { port: 5173, proxy: { '/api': 'http://localhost:8000', '/uploads': 'http://localhost:8000' } }
}))