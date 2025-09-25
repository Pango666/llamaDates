module.exports = {
  content: [
    './resources/views/**/*.blade.php',
    './resources/js/**/*.js',
    './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
    './storage/framework/views/**/*.php',
  ],
  theme: { extend: {} },
  plugins: [],
  // para forzar estilos visibles en la prueba
  safelist: ['bg-emerald-600','text-white','p-10','rounded-xl','text-3xl']
}
