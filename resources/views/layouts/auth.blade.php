<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Iniciar Sesión · DentalCare</title>

  {{-- Tailwind CSS --}}
  <script src="https://cdn.tailwindcss.com"></script>
  
  {{-- Configuración personalizada de Tailwind --}}
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            primary: {
              50: '#f0f9ff',
              100: '#e0f2fe',
              200: '#bae6fd',
              300: '#7dd3fc',
              400: '#38bdf8',
              500: '#0ea5e9',
              600: '#0284c7',
              700: '#0369a1',
              800: '#075985',
              900: '#0c4a6e',
            }
          },
          fontFamily: {
            sans: ['Inter', 'system-ui', 'sans-serif'],
          }
        }
      }
    }
  </script>

  {{-- Inter Font --}}
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <style>
    .auth-background {
      background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 50%, #bae6fd 100%);
    }
    .card-shadow {
      box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }
    .input-focus:focus {
      box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.1);
      border-color: #0ea5e9;
    }
  </style>
</head>
<body class="min-h-screen auth-background flex items-center justify-center p-4">
  <div class="w-full max-w-md">
    {{-- Logo y Header --}}
    <div class="text-center mb-8">
      <div class="inline-flex items-center justify-center w-16 h-16 bg-white rounded-2xl shadow-lg mb-4">
        <svg class="w-8 h-8 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2C13.1 2 14 2.9 14 4C14 5.1 13.1 6 12 6C10.9 6 10 5.1 10 4C10 2.9 10.9 2 12 2ZM21 9V7L15 5.5V7H9V5.5L3 7V9L9 10.5V12L5 13V15L9 13.5V15H15V13.5L21 15V13L15 11.5V10.5L21 9Z"/>
        </svg>
      </div>
      <h1 class="text-2xl font-bold text-slate-800 mb-2">DentalCare</h1>
      <p class="text-slate-600">Accede a tu cuenta</p>
    </div>

    {{-- Card de Contenido --}}
    <div class="bg-white rounded-2xl card-shadow p-8">
      {{-- Alertas --}}
      @if ($errors->any())
        <div class="mb-6 p-4 rounded-lg bg-red-50 border border-red-200 flex items-start gap-3">
          <svg class="w-5 h-5 text-red-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <div class="text-sm text-red-700">
            Credenciales incorrectas. Por favor, intenta nuevamente.
          </div>
        </div>
      @endif

      {{-- Formulario de Login --}}
      <form method="POST" action="{{ route('login') }}" class="space-y-6">
        @csrf

        {{-- Email --}}
        <div class="space-y-2">
          <label for="email" class="block text-sm font-medium text-slate-700 flex items-center gap-2">
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
            Correo Electrónico
          </label>
          <input
            id="email"
            type="email"
            name="email"
            value="{{ old('email') }}"
            required
            autocomplete="email"
            autofocus
            class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-colors input-focus"
            placeholder="tu@email.com"
          >
        </div>

        {{-- Password --}}
        <div class="space-y-2">
          <label for="password" class="block text-sm font-medium text-slate-700 flex items-center gap-2">
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
            Contraseña
          </label>
          <input
            id="password"
            type="password"
            name="password"
            required
            autocomplete="current-password"
            class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-colors input-focus"
            placeholder="Tu contraseña"
          >
        </div>

        {{-- Recordarme y Olvidé contraseña --}}
        <div class="flex items-center justify-between">
          <label class="flex items-center">
            <input type="checkbox" name="remember" class="rounded border-slate-300 text-primary-600 focus:ring-primary-500">
            <span class="ms-2 text-sm text-slate-600">Recordarme</span>
          </label>

          <a href="{{ route('password.request') }}" class="text-sm text-primary-600 hover:text-primary-500 transition-colors">
            ¿Olvidaste tu contraseña?
          </a>
        </div>

        {{-- Botón de Login --}}
        <button type="submit" class="w-full bg-primary-600 text-white py-3 px-4 rounded-lg hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-colors font-medium flex items-center justify-center gap-2">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
          </svg>
          Iniciar Sesión
        </button>
      </form>

      {{-- Separador --}}
      {{-- <div class="mt-8 pt-6 border-t border-slate-200">
        <p class="text-center text-sm text-slate-600">
          ¿No tienes una cuenta?
          <a href="{{ route('register') }}" class="font-medium text-primary-600 hover:text-primary-500 transition-colors">
            Regístrate aquí
          </a>
        </p>
      </div> --}}
    </div>

    {{-- Footer --}}
    <div class="text-center mt-6">
      <p class="text-sm text-slate-500">
        &copy; {{ date('Y') }} DentalCare. Todos los derechos reservados.
      </p>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const forms = document.querySelectorAll('form');
      forms.forEach(form => {
        form.addEventListener('submit', function() {
          const submitBtn = this.querySelector('button[type="submit"]');
          if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Iniciando sesión...';
          }
        });
      });
    });
  </script>
</body>
</html>