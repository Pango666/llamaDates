<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title','Paciente') · CEOT DATES</title>
  <link rel="icon" href="{{ asset('images/logo.png') }}">

  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
  <script src="{{ asset('js/app.js') }}"></script>

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">

  <style>
    /* ========================================
       PREMIUM DESIGN SYSTEM - PORTAL PACIENTE
       ======================================== */
    
    :root {
      --primary-50: #f0fdfa;
      --primary-100: #ccfbf1;
      --primary-500: #14b8a6;
      --primary-600: #0d9488;
      --primary-700: #0f766e;
      --accent-500: #06b6d4;
      --accent-600: #0891b2;
      --success-500: #22c55e;
      --warning-500: #f59e0b;
      --danger-500: #ef4444;
      --slate-50: #f8fafc;
      --slate-100: #f1f5f9;
      --slate-200: #e2e8f0;
      --slate-300: #cbd5e1;
      --slate-400: #94a3b8;
      --slate-500: #64748b;
      --slate-600: #475569;
      --slate-700: #334155;
      --slate-800: #1e293b;
      --slate-900: #0f172a;
    }
    
    /* Cards con glassmorphism */
    .card {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      border-radius: 1rem;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04), 
                  0 4px 6px rgba(0, 0, 0, 0.04),
                  0 8px 16px rgba(0, 0, 0, 0.04);
      padding: 1.25rem;
      border: 1px solid rgba(255, 255, 255, 0.8);
      transition: box-shadow 0.3s ease, transform 0.2s ease;
      animation: fadeInUp 0.3s ease;
    }
    
    .card:hover {
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05),
                  0 10px 20px rgba(0, 0, 0, 0.08);
    }
    
    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    /* Botones modernos */
    .btn {
      padding: 0.625rem 1rem;
      border-radius: 0.625rem;
      font-size: 0.875rem;
      font-weight: 500;
      line-height: 1.25rem;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      transition: all 0.2s ease;
      cursor: pointer;
      border: none;
    }
    
    .btn-primary {
      background: linear-gradient(135deg, var(--primary-600) 0%, var(--accent-600) 100%);
      color: white;
      box-shadow: 0 2px 8px rgba(13, 148, 136, 0.25);
    }
    
    .btn-primary:hover {
      background: linear-gradient(135deg, var(--primary-700) 0%, var(--accent-600) 100%);
      box-shadow: 0 4px 12px rgba(13, 148, 136, 0.35);
      transform: translateY(-1px);
    }
    
    .btn-danger {
      background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
      color: white;
    }
    
    .btn-danger:hover {
      background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
      transform: translateY(-1px);
    }
    
    .btn-ghost {
      background: var(--slate-50);
      color: var(--slate-700);
      border: 1px solid var(--slate-200);
    }
    
    .btn-ghost:hover {
      background: var(--slate-100);
      border-color: var(--slate-300);
    }
    
    /* Navegación premium */
    .nav-item {
      display: flex;
      align-items: center;
      padding: 0.75rem 1rem;
      border-radius: 0.75rem;
      color: var(--slate-600);
      font-weight: 500;
      transition: all 0.2s ease;
      position: relative;
      overflow: hidden;
    }
    
    .nav-item::before {
      content: '';
      position: absolute;
      left: 0;
      top: 0;
      bottom: 0;
      width: 3px;
      background: linear-gradient(180deg, var(--primary-500) 0%, var(--accent-500) 100%);
      opacity: 0;
      transform: scaleY(0);
      transition: all 0.2s ease;
      border-radius: 0 3px 3px 0;
    }
    
    .nav-item:hover {
      background: linear-gradient(90deg, var(--primary-50) 0%, transparent 100%);
      color: var(--primary-700);
    }
    
    .nav-item:hover i {
      transform: scale(1.1);
    }
    
    .nav-item i {
      transition: transform 0.2s ease;
    }
    
    .nav-active {
      background: linear-gradient(90deg, var(--primary-100) 0%, var(--primary-50) 100%);
      color: var(--primary-700);
      font-weight: 600;
    }
    
    .nav-active::before {
      opacity: 1;
      transform: scaleY(1);
    }
    
    /* Badges */
    .badge {
      font-size: 0.75rem;
      font-weight: 600;
      padding: 0.25rem 0.625rem;
      border-radius: 9999px;
    }

    /* Branding premium - Portal Paciente */
    .brand-font {
      font-family: 'Outfit', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
      font-weight: 700;
      font-size: 1.5rem;
      background: linear-gradient(135deg, var(--primary-600) 0%, var(--accent-500) 50%, var(--primary-500) 100%);
      background-size: 200% 200%;
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      letter-spacing: -0.025em;
      animation: shimmer 3s ease-in-out infinite;
    }
    
    @keyframes shimmer {
      0%, 100% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
    }
    
    .brand-subtitle {
      font-family: 'Inter', sans-serif;
      font-weight: 600;
      font-size: 0.65rem;
      color: var(--primary-600);
      letter-spacing: 0.1em;
      text-transform: uppercase;
      background: var(--primary-50);
      padding: 0.125rem 0.5rem;
      border-radius: 9999px;
      display: inline-block;
    }
    
    /* Sidebar premium */
    aside.fixed {
      background: linear-gradient(180deg, rgba(255,255,255,0.98) 0%, rgba(248,250,252,0.98) 100%);
      backdrop-filter: blur(20px);
      border-right: 1px solid rgba(226, 232, 240, 0.8);
    }
    
    aside.fixed::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(90deg, var(--primary-500) 0%, var(--accent-500) 100%);
    }
    
    /* Header premium */
    header {
      background: rgba(255, 255, 255, 0.85) !important;
      backdrop-filter: blur(20px) saturate(180%) !important;
      border-bottom: 1px solid rgba(226, 232, 240, 0.6) !important;
    }

    /* Botón hamburguesa móvil */
    #mobileMenuButtonHeader {
      display: none !important;
    }
    
    @media (max-width: 768px) {
      #mobileMenuButtonHeader {
        display: flex !important;
        align-items: center;
        justify-content: center;
        width: 44px;
        height: 44px;
        background: white;
        border: 1px solid var(--slate-200);
        border-radius: 0.75rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        z-index: 20;
        transition: all 0.2s ease;
      }
      
      #mobileMenuButtonHeader:hover {
        background: var(--slate-50);
        box-shadow: 0 4px 12px rgba(0,0,0,0.12);
      }
      
      header {
        padding-left: 1rem !important;
      }
    }

    /* Menú móvil */
    .mobile-menu-overlay {
      display: none;
    }
    
    .mobile-menu {
      display: none;
    }
    
    @media (max-width: 768px) {
      .mobile-menu-overlay {
        display: block;
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(4px);
        z-index: 40;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
      }
      
      .mobile-menu-overlay.active {
        opacity: 1;
        visibility: visible;
      }
      
      .mobile-menu {
        display: block;
        position: fixed;
        top: 0;
        left: 0;
        bottom: 0;
        width: 300px;
        background: linear-gradient(180deg, rgba(255,255,255,0.98) 0%, rgba(248,250,252,0.98) 100%);
        z-index: 50;
        transform: translateX(-100%);
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        overflow-y: auto;
        box-shadow: 0 0 40px rgba(0, 0, 0, 0.15);
      }
      
      .mobile-menu::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--primary-500) 0%, var(--accent-500) 100%);
      }
      
      .mobile-menu.open {
        transform: translateX(0);
      }
    }
    
    /* Scrollbar personalizado */
    ::-webkit-scrollbar {
      width: 6px;
      height: 6px;
    }
    
    ::-webkit-scrollbar-track {
      background: transparent;
    }
    
    ::-webkit-scrollbar-thumb {
      background: var(--slate-300);
      border-radius: 999px;
    }
    
    ::-webkit-scrollbar-thumb:hover {
      background: var(--slate-400);
    }
    
    /* Focus states */
    .btn:focus-visible,
    .nav-item:focus-visible {
      outline: 2px solid var(--primary-500);
      outline-offset: 2px;
    }
  </style>
</head>

<body class="bg-slate-100 min-h-screen" style="font-family: 'Inter', sans-serif;">
  <div class="min-h-screen">

    {{-- OVERLAY PARA MÓVIL --}}
    <div class="mobile-menu-overlay" id="mobileOverlay"></div>

    {{-- SIDEBAR PACIENTE (DESKTOP) --}}
    <aside class="hidden md:block fixed inset-y-0 left-0 bg-white border-r overflow-y-auto w-64">
      <div class="p-4 border-b">
        <div class="flex items-center gap-3">
          <div class="flex-shrink-0">
            <img src="{{ asset('images/logo.png') }}" alt="CEOT DATES"
                 class="w-14 h-14 object-contain rounded-lg shadow-sm"
                 onerror="this.style.display='none'">
          </div>
          <div>
            <h1 class="brand-font">CEOT DATES</h1>
            <p class="brand-subtitle">PACIENTE</p>
          </div>
        </div>
      </div>

      <nav class="p-3 space-y-1">
        <a href="{{ route('app.dashboard') }}"
           class="nav-item {{ request()->routeIs('app.dashboard') ? 'nav-active' : '' }}">
          <span class="inline-flex items-center gap-2">
            <i class="w-4 h-4 fas fa-house"></i>
            <span>Inicio</span>
          </span>
        </a>

        <a href="{{ route('app.appointments.index') }}"
           class="nav-item {{ request()->routeIs('app.appointments.*') ? 'nav-active' : '' }}">
          <span class="inline-flex items-center gap-2">
            <i class="w-4 h-4 fas fa-calendar-check"></i>
            <span>Citas</span>
          </span>
        </a>

        <a href="{{ route('app.invoices.index') }}"
           class="nav-item {{ request()->routeIs('app.invoices.*') ? 'nav-active' : '' }}">
          <span class="inline-flex items-center gap-2">
            <i class="w-4 h-4 fas fa-credit-card"></i>
            <span>Pagos</span>
          </span>
        </a>

        <a href="{{ route('app.profile') }}"
           class="nav-item {{ request()->routeIs('app.profile') ? 'nav-active' : '' }}">
          <span class="inline-flex items-center gap-2">
            <i class="w-4 h-4 fas fa-user"></i>
            <span>Perfil</span>
          </span>
        </a>

        <a href="{{ route('app.odontogram') }}"
           class="nav-item {{ request()->routeIs('app.odontogram') ? 'nav-active' : '' }}">
          <span class="inline-flex items-center gap-2">
            <i class="w-4 h-4 fas fa-teeth"></i>
            <span>Mi Odontograma</span>
          </span>
        </a>
      </nav>

      @auth
        <div class="p-3 mt-2">
          <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="w-full text-left btn btn-ghost">
              <i class="w-4 h-4 fas fa-sign-out-alt"></i>
              Cerrar sesión
            </button>
          </form>
        </div>
      @endauth
    </aside>

    {{-- MENÚ MÓVIL (PACIENTE) --}}
    <aside class="mobile-menu">
      <div class="p-4 border-b flex justify-between items-center">
        <div class="flex items-center gap-3">
          <div class="flex-shrink-0">
            <img src="{{ asset('images/logo.png') }}" alt="CEOT DATES"
                 class="w-12 h-12 object-contain rounded-lg shadow-sm"
                 onerror="this.style.display='none'">
          </div>
          <div>
            <h1 class="brand-font text-lg">CEOT DATES</h1>
            <p class="brand-subtitle text-xs">PACIENTE</p>
          </div>
        </div>

        <button class="p-2 rounded-lg hover:bg-slate-100" id="closeMenuButton">
          <i class="fas fa-times text-slate-600"></i>
        </button>
      </div>

      <nav class="p-3 space-y-1">
        <a href="{{ route('app.dashboard') }}"
           class="nav-item {{ request()->routeIs('app.dashboard') ? 'nav-active' : '' }} mobile-nav-link">
          <span class="inline-flex items-center gap-2">
            <i class="w-4 h-4 fas fa-house"></i> Inicio
          </span>
        </a>

        <a href="{{ route('app.appointments.index') }}"
           class="nav-item {{ request()->routeIs('app.appointments.*') ? 'nav-active' : '' }} mobile-nav-link">
          <span class="inline-flex items-center gap-2">
            <i class="w-4 h-4 fas fa-calendar-check"></i> Citas
          </span>
        </a>

        <a href="{{ route('app.invoices.index') }}"
           class="nav-item {{ request()->routeIs('app.invoices.*') ? 'nav-active' : '' }} mobile-nav-link">
          <span class="inline-flex items-center gap-2">
            <i class="w-4 h-4 fas fa-credit-card"></i> Pagos
          </span>
        </a>

        <a href="{{ route('app.profile') }}"
           class="nav-item {{ request()->routeIs('app.profile') ? 'nav-active' : '' }} mobile-nav-link">
          <span class="inline-flex items-center gap-2">
            <i class="w-4 h-4 fas fa-user"></i> Perfil
          </span>
        </a>

        <a href="{{ route('app.odontogram') }}"
           class="nav-item {{ request()->routeIs('app.odontogram') ? 'nav-active' : '' }} mobile-nav-link">
          <span class="inline-flex items-center gap-2">
            <i class="w-4 h-4 fas fa-teeth"></i> Mi Odontograma
          </span>
        </a>
      </nav>

      @auth
        <form method="POST" action="{{ route('logout') }}" class="p-3">
          @csrf
          <button class="w-full text-left btn btn-ghost mobile-nav-link">
            <i class="w-4 h-4 fas fa-sign-out-alt"></i>
            Cerrar sesión
          </button>
        </form>
      @endauth
    </aside>

    {{-- MAIN --}}
    <main id="mainContent" class="w-full md:pl-64">
      <header class="sticky top-0 z-10 bg-white/95 backdrop-blur border-b px-4 py-3 flex items-center justify-between">
        <div class="flex items-center gap-3">
          <button id="mobileMenuButtonHeader" class="p-2 rounded-lg hover:bg-slate-100 border border-gray-200">
            <i class="fas fa-bars text-slate-600"></i>
          </button>

          <div>
            <h2 class="text-lg font-semibold leading-none" style="font-family: 'Outfit', sans-serif;">
              @yield('title','Mi panel')
            </h2>
            @auth
              <p class="text-[11px] text-slate-500">Hola, {{ auth()->user()->name }}</p>
            @endauth
          </div>
        </div>

        {{-- ACCIONES EN HEADER (derecha) --}}
        <div class="flex items-center gap-2 flex-wrap justify-end">
          @hasSection('header-actions')
            @yield('header-actions')
          @else
            <a href="{{ route('app.dashboard') }}" class="btn btn-ghost">
              <i class="fas fa-house"></i> Inicio
            </a>
            <a href="{{ route('app.appointments.index') }}" class="btn btn-ghost">
              <i class="fas fa-calendar-check"></i> Mis citas
            </a>
            <a href="{{ route('app.invoices.index') }}" class="btn btn-ghost">
              <i class="fas fa-credit-card"></i> Mis pagos
            </a>
            <a href="{{ route('app.profile') }}" class="btn btn-ghost">
              <i class="fas fa-user"></i> Perfil
            </a>
          @endif
        </div>
      </header>

      <div class="p-4">
        @if (session('ok'))
          <div class="mb-4 p-3 rounded-lg bg-green-50 text-green-800 text-sm border border-green-200 flex items-start">
            <i class="fas fa-check-circle mt-0.5 mr-2 flex-shrink-0 text-green-500"></i>
            {{ session('ok') }}
          </div>
        @endif

        @if (session('warn'))
          <div class="mb-4 p-3 rounded-lg bg-amber-50 text-amber-800 text-sm border border-amber-200 flex items-start">
            <i class="fas fa-exclamation-triangle mt-0.5 mr-2 flex-shrink-0 text-amber-500"></i>
            {{ session('warn') }}
          </div>
        @endif

        @if ($errors->any())
          <div class="mb-4 p-3 rounded-lg bg-rose-50 text-rose-800 text-sm border border-rose-200">
            <div class="flex items-start mb-2">
              <i class="fas fa-times-circle mt-0.5 mr-2 flex-shrink-0 text-rose-500"></i>
              <span class="font-medium">Se encontraron los siguientes errores:</span>
            </div>
            <ul class="list-disc ms-8">
              @foreach($errors->all() as $e)
                <li>{{ $e }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        {{-- COMPATIBLE CON @section('pt') o @section('content') --}}
        @hasSection('pt')
          @yield('pt')
        @else
          @yield('content')
        @endif
      </div>
    </main>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const mobileMenuButtonHeader = document.getElementById('mobileMenuButtonHeader');
      const closeMenuButton = document.getElementById('closeMenuButton');
      const mobileOverlay = document.getElementById('mobileOverlay');
      const mobileMenu = document.querySelector('.mobile-menu');
      const mobileNavLinks = document.querySelectorAll('.mobile-nav-link');

      function openMobileMenu() {
        if (!mobileMenu || !mobileOverlay) return;
        mobileMenu.classList.add('open');
        mobileOverlay.classList.add('active');
        document.body.style.overflow = 'hidden';
      }

      function closeMobileMenu() {
        if (!mobileMenu || !mobileOverlay) return;
        mobileMenu.classList.remove('open');
        mobileOverlay.classList.remove('active');
        document.body.style.overflow = '';
      }

      if (mobileMenuButtonHeader) mobileMenuButtonHeader.addEventListener('click', openMobileMenu);
      if (closeMenuButton) closeMenuButton.addEventListener('click', closeMobileMenu);
      if (mobileOverlay) mobileOverlay.addEventListener('click', closeMobileMenu);

      if (mobileNavLinks.length > 0) {
        mobileNavLinks.forEach(link => link.addEventListener('click', closeMobileMenu));
      }

      document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeMobileMenu();
      });
    });
  </script>

  @yield('scripts')
  @stack('scripts')
</body>
</html>
