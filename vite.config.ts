import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import tailwindcss from '@tailwindcss/vite'
import { resolve } from 'node:path'

export default defineConfig({
  plugins: [react(), tailwindcss()],
  build: {
    outDir: 'dist',
    emptyOutDir: true,
    cssCodeSplit: false,
    manifest: false,
    rollupOptions: {
      input: resolve(__dirname, 'resources/main.tsx'),
      output: {
        entryFileNames: 'bolt.js',
        chunkFileNames: 'bolt-[name].js',
        assetFileNames: (asset) =>
          asset.names?.some((n) => n.endsWith('.css')) ? 'bolt.css' : 'bolt-[name][extname]',
      },
    },
  },
})
