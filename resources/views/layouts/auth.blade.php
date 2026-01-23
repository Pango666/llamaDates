<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title', 'Iniciar Sesión') · CEOT DATES</title>

  <script src="https://cdn.tailwindcss.com"></script>
  
  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@500;600;700&display=swap" rel="stylesheet">

  <style>
    /* Premium Auth Design System */
    :root {
      --primary-500: #3b82f6;
      --primary-600: #2563eb;
      --primary-700: #1d4ed8;
      --accent-500: #8b5cf6;
      --accent-600: #7c3aed;
    }
    
    body {
      font-family: 'Inter', system-ui, sans-serif;
    }
    
    /* Animated gradient background */
    .auth-background {
      background: linear-gradient(-45deg, #667eea, #764ba2, #6B8DD6, #8E37D7);
      background-size: 400% 400%;
      animation: gradientShift 15s ease infinite;
      position: relative;
      overflow: hidden;
    }
    
    .auth-background::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: 
        radial-gradient(circle at 20% 80%, rgba(120, 119, 198, 0.3) 0%, transparent 50%),
        radial-gradient(circle at 80% 20%, rgba(131, 58, 180, 0.3) 0%, transparent 50%),
        radial-gradient(circle at 40% 40%, rgba(255, 255, 255, 0.1) 0%, transparent 30%);
      pointer-events: none;
    }
    
    @keyframes gradientShift {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }
    
    /* Floating shapes */
    .floating-shape {
      position: absolute;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.1);
      animation: float 20s infinite ease-in-out;
    }
    
    .shape-1 {
      width: 300px;
      height: 300px;
      top: -100px;
      left: -100px;
      animation-delay: 0s;
    }
    
    .shape-2 {
      width: 200px;
      height: 200px;
      bottom: -50px;
      right: -50px;
      animation-delay: -5s;
    }
    
    .shape-3 {
      width: 150px;
      height: 150px;
      top: 50%;
      right: 10%;
      animation-delay: -10s;
    }
    
    @keyframes float {
      0%, 100% { transform: translate(0, 0) rotate(0deg); }
      25% { transform: translate(20px, -20px) rotate(5deg); }
      50% { transform: translate(-10px, 20px) rotate(-5deg); }
      75% { transform: translate(15px, 10px) rotate(3deg); }
    }
    
    /* Glassmorphism card */
    .glass-card {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(20px);
      border: 1px solid rgba(255, 255, 255, 0.3);
      box-shadow: 
        0 25px 50px -12px rgba(0, 0, 0, 0.25),
        0 0 0 1px rgba(255, 255, 255, 0.1) inset;
      border-radius: 1.5rem;
    }
    
    /* Premium inputs */
    .premium-input {
      background: #f8fafc;
      border: 2px solid #e2e8f0;
      border-radius: 0.75rem;
      padding: 0.875rem 1rem 0.875rem 3rem;
      font-size: 0.9375rem;
      transition: all 0.2s ease;
      width: 100%;
    }
    
    .premium-input:focus {
      background: white;
      border-color: var(--primary-500);
      box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
      outline: none;
    }
    
    .premium-input::placeholder {
      color: #94a3b8;
    }
    
    /* Premium button */
    .premium-button {
      background: linear-gradient(135deg, var(--primary-600) 0%, var(--accent-600) 100%);
      color: white;
      font-weight: 600;
      padding: 0.875rem 1.5rem;
      border-radius: 0.75rem;
      width: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      transition: all 0.3s ease;
      border: none;
      cursor: pointer;
      box-shadow: 0 4px 15px rgba(37, 99, 235, 0.3);
    }
    
    .premium-button:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(37, 99, 235, 0.4);
    }
    
    .premium-button:active {
      transform: translateY(0);
    }
    
    .premium-button:disabled {
      opacity: 0.7;
      cursor: not-allowed;
      transform: none;
    }
    
    /* Brand styling */
    .brand-text {
      font-family: 'Outfit', sans-serif;
      font-weight: 700;
      font-size: 1.75rem;
      background: linear-gradient(135deg, var(--primary-600) 0%, var(--accent-500) 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      letter-spacing: -0.02em;
    }
    
    /* Input icon */
    .input-icon {
      position: absolute;
      left: 1rem;
      top: 50%;
      transform: translateY(-50%);
      color: #94a3b8;
      transition: color 0.2s ease;
    }
    
    .input-wrapper:focus-within .input-icon {
      color: var(--primary-500);
    }
    
    /* Checkbox styling */
    .premium-checkbox {
      width: 1.125rem;
      height: 1.125rem;
      border: 2px solid #cbd5e1;
      border-radius: 0.375rem;
      transition: all 0.2s ease;
      accent-color: var(--primary-600);
    }
    
    .premium-checkbox:checked {
      background-color: var(--primary-600);
      border-color: var(--primary-600);
    }
    
    /* Link styling */
    .premium-link {
      color: var(--primary-600);
      font-weight: 500;
      text-decoration: none;
      transition: color 0.2s ease;
    }
    
    .premium-link:hover {
      color: var(--accent-600);
      text-decoration: underline;
    }
    
    /* Logo container */
    .logo-container {
      width: 4.5rem;
      height: 4.5rem;
      background: linear-gradient(135deg, var(--primary-500) 0%, var(--accent-500) 100%);
      border-radius: 1.25rem;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 10px 30px rgba(37, 99, 235, 0.3);
      margin: 0 auto 1.5rem;
    }
    
    .logo-container img {
      width: 3rem;
      height: 3rem;
      object-fit: contain;
      filter: brightness(0) invert(1);
    }
    
    /* Dental icon fallback */
    .dental-icon {
      width: 2.5rem;
      height: 2.5rem;
      color: white;
    }
  </style>
</head>
<body class="min-h-screen auth-background flex items-center justify-center p-4">
  <!-- Floating shapes for visual interest -->
  <div class="floating-shape shape-1"></div>
  <div class="floating-shape shape-2"></div>
  <div class="floating-shape shape-3"></div>
  
  <div class="w-full max-w-md relative z-10">
    <!-- Glass Card -->
    <div class="glass-card p-8 md:p-10">
      <!-- Logo -->
      <div class="logo-container">
        <img src="{{ asset('images/logo.png') }}" alt="CEOT DATES" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
        <svg class="dental-icon" style="display:none;" viewBox="0 0 24 24" fill="currentColor">
          <path d="M7.5 2.75c-2.623 0-4.75 2.127-4.75 4.75 0 2.81 1.027 6.551 2.48 8.86.64 1.02 1.788 1.64 3.01 1.64 1.21 0 1.79-.61 2.37-1.67.41-.76.86-1.6 1.39-1.6.53 0 .98.84 1.39 1.6.58 1.06 1.16 1.67 2.37 1.67 1.222 0 2.37-.62 3.01-1.64 1.454-2.309 2.48-6.05 2.48-8.86 0-2.623-2.127-4.75-4.75-4.75-1.43 0-2.82.65-3.75 1.77-.93-1.12-2.32-1.77-3.75-1.77z"/>
        </svg>
      </div>
      
      <!-- Header -->
      <div class="text-center mb-8">
        <h1 class="brand-text mb-2">CEOT DATES</h1>
        <p class="text-slate-500 text-sm">Gestión dental inteligente</p>
      </div>

      <!-- Alerts -->
      @if ($errors->any())
        <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 flex items-start gap-3">
          <svg class="w-5 h-5 text-red-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <div class="text-sm text-red-700">
            Credenciales incorrectas. Por favor, verifica tu email y contraseña.
          </div>
        </div>
      @endif

      @if (session('status'))
        <div class="mb-6 p-4 rounded-xl bg-green-50 border border-green-200 flex items-start gap-3">
          <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <div class="text-sm text-green-700">{{ session('status') }}</div>
        </div>
      @endif

      <!-- Form -->
      <form method="POST" action="{{ route('login') }}" class="space-y-5" id="loginForm">
        @csrf

        <!-- Email -->
        <div class="space-y-2">
          <label for="email" class="block text-sm font-semibold text-slate-700">
            Correo electrónico
          </label>
          <div class="relative input-wrapper">
            <svg class="input-icon w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
            <input
              id="email"
              type="email"
              name="email"
              value="{{ old('email') }}"
              required
              autocomplete="email"
              autofocus
              class="premium-input"
              placeholder="tu@email.com"
            >
          </div>
        </div>

        <!-- Password -->
        <div class="space-y-2">
          <label for="password" class="block text-sm font-semibold text-slate-700">
            Contraseña
          </label>
          <div class="relative input-wrapper">
            <svg class="input-icon w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
            <input
              id="password"
              type="password"
              name="password"
              required
              autocomplete="current-password"
              class="premium-input"
              placeholder="••••••••"
            >
            <button 
              type="button" 
              id="togglePassword"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors p-1"
              aria-label="Mostrar contraseña"
            >
              <svg id="eyeIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
              </svg>
            </button>
          </div>
        </div>

        <!-- Remember & Forgot -->
        <div class="flex items-center justify-between">
          <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" name="remember" class="premium-checkbox">
            <span class="text-sm text-slate-600">Recuérdame</span>
          </label>
          <a href="{{ route('password.request') }}" class="premium-link text-sm">
            ¿Olvidaste tu contraseña?
          </a>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="premium-button" id="submitBtn">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
          </svg>
          Iniciar Sesión
        </button>
      </form>
    </div>

    <!-- Footer -->
    <div class="text-center mt-6">
      <p class="text-sm text-white/80">
        &copy; {{ date('Y') }} CEOT DATES. Todos los derechos reservados.
      </p>
    </div>
  </div>

  <script>
    // Toggle password visibility
    document.getElementById('togglePassword').addEventListener('click', function() {
      const password = document.getElementById('password');
      const eyeIcon = document.getElementById('eyeIcon');
      const isPassword = password.type === 'password';
      
      password.type = isPassword ? 'text' : 'password';
      
      if (isPassword) {
        eyeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>';
      } else {
        eyeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>';
      }
    });

    // Form submission loading state
    document.getElementById('loginForm').addEventListener('submit', function() {
      const btn = document.getElementById('submitBtn');
      btn.disabled = true;
      btn.innerHTML = '<svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Iniciando sesión...';
    });
  </script>
</body>
</html>