<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title','Paciente') · CEOT DATES</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
  <script src="{{ asset('js/app.js') }}"></script>

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">

  <style>
    .card{background:#fff;border-radius:.75rem;box-shadow:0 1px 2px rgba(16,24,40,.05),0 1px 3px rgba(16,24,40,.1);padding:1rem}
    .btn{padding:.5rem .75rem;border-radius:.5rem;font-size:.875rem;line-height:1.25rem;display:inline-flex;align-items:center;gap:.4rem}
    .btn-primary{background:#2563eb;color:#fff}.btn-primary:hover{background:#1d4ed8}
    .btn-danger{background:#ef4444;color:#fff}.btn-danger:hover{background:#dc2626}
    .btn-ghost{background:#f8fafc}.btn-ghost:hover{background:#f1f5f9}
    .nav-item{display:block;padding:.5rem .75rem;border-radius:.5rem}
    .nav-item:hover{background:#f1f5f9}
    .nav-active{background:#eef2ff;color:#4338ca;font-weight:600}
    .badge{font-size:.75rem;padding:.125rem .5rem;border-radius:.375rem}

    .brand-font {
      font-family: 'Outfit', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
      font-weight: 700;
      font-size: 1.5rem;
      background: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      letter-spacing: -0.025em;
    }
    .brand-subtitle {
      font-family: 'Inter', sans-serif;
      font-weight: 500;
      font-size: 0.75rem;
      color: #6b7280;
      letter-spacing: 0.05em;
    }

    /* Botón hamburguesa móvil SIEMPRE visible en móviles */
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
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        z-index: 20;
      }
      #mobileMenuButtonHeader:hover { background: #f9fafb; }
      header { padding-left: 1rem !important; }
    }

    /* Menú móvil */
    .mobile-menu-overlay { display: none; }
    .mobile-menu { display: none; }
    @media (max-width: 768px) {
      .mobile-menu-overlay {
        display: block;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 40;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.3s ease-in-out, visibility 0.3s;
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
        width: 280px;
        background: white;
        z-index: 50;
        transform: translateX(-100%);
        transition: transform 0.3s ease-in-out;
        overflow-y: auto;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
      }
      .mobile-menu.open {
        transform: translateX(0);
      }
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
