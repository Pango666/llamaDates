import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'

export default defineConfig({
  plugins: [
    laravel({
      input: ['resources/js/app.js'], // el CSS se importa dentro de app.js
      refresh: true,
    }),
  ],
  css: {
    postcss: './postcss.config.cjs', // <- clave
  },
})
