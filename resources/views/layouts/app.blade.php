<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title','Panel') · CEOT DATES</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
  <script src="{{ asset('js/app.js') }}"></script>

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">

  <style>
    /* ========================================
       PREMIUM DESIGN SYSTEM - CEOT DATES
       ======================================== */
    
    :root {
      --primary-50: #eff6ff;
      --primary-100: #dbeafe;
      --primary-500: #3b82f6;
      --primary-600: #2563eb;
      --primary-700: #1d4ed8;
      --accent-500: #8b5cf6;
      --accent-600: #7c3aed;
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
    
    /* Cards con glassmorphism sutil */
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
    }
    
    .card:hover {
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05),
                  0 10px 20px rgba(0, 0, 0, 0.08);
    }
    
    /* Botones modernos con gradientes */
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
      box-shadow: 0 2px 8px rgba(37, 99, 235, 0.25);
    }
    
    .btn-primary:hover {
      background: linear-gradient(135deg, var(--primary-700) 0%, var(--accent-600) 100%);
      box-shadow: 0 4px 12px rgba(37, 99, 235, 0.35);
      transform: translateY(-1px);
    }
    
    .btn-danger {
      background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
      color: white;
      box-shadow: 0 2px 8px rgba(239, 68, 68, 0.25);
    }
    
    .btn-danger:hover {
      background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
      box-shadow: 0 4px 12px rgba(239, 68, 68, 0.35);
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
    
    .btn-success {
      background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
      color: white;
      box-shadow: 0 2px 8px rgba(34, 197, 94, 0.25);
    }
    
    .btn-success:hover {
      background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
      transform: translateY(-1px);
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
    
    /* Badges modernos */
    .badge {
      font-size: 0.75rem;
      font-weight: 600;
      padding: 0.25rem 0.625rem;
      border-radius: 9999px;
      display: inline-flex;
      align-items: center;
      gap: 0.25rem;
    }
    
    .badge-primary {
      background: var(--primary-100);
      color: var(--primary-700);
    }
    
    .badge-success {
      background: #dcfce7;
      color: #15803d;
    }
    
    .badge-warning {
      background: #fef3c7;
      color: #b45309;
    }
    
    .badge-danger {
      background: #fee2e2;
      color: #b91c1c;
    }
    
    /* Branding premium */
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
      color: var(--slate-500);
      letter-spacing: 0.1em;
      text-transform: uppercase;
    }
    
    /* Sidebar premium con glassmorphism */
    .desktop-sidebar {
      background: linear-gradient(180deg, rgba(255,255,255,0.98) 0%, rgba(248,250,252,0.98) 100%);
      backdrop-filter: blur(20px);
      border-right: 1px solid rgba(226, 232, 240, 0.8);
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .desktop-sidebar::before {
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
    
    /* Secciones de menú */
    .nav-section-title {
      font-size: 0.65rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.1em;
      color: var(--slate-400);
      padding: 1rem 1rem 0.5rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
    
    .nav-section-title::after {
      content: '';
      flex: 1;
      height: 1px;
      background: linear-gradient(90deg, var(--slate-200) 0%, transparent 100%);
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
    
    /* Menú móvil premium */
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
    
    /* Sidebar colapsable */
    .desktop-sidebar.collapsed {
      width: 72px !important;
    }
    
    .desktop-sidebar.collapsed .hide-when-collapsed {
      display: none !important;
    }
    
    .desktop-sidebar.collapsed .nav-item {
      padding: 0.75rem;
      display: flex;
      justify-content: center;
      position: relative;
    }
    
    .desktop-sidebar.collapsed .nav-item span {
      justify-content: center;
    }
    
    .desktop-sidebar.collapsed .nav-item .nav-text {
      display: none;
    }
    
    /* Tooltips elegantes para modo contraído */
    .desktop-sidebar.collapsed .nav-item:hover::after {
      content: attr(data-title);
      position: absolute;
      left: 100%;
      top: 50%;
      transform: translateY(-50%);
      background: var(--slate-800);
      color: white;
      padding: 0.5rem 0.875rem;
      border-radius: 0.5rem;
      font-size: 0.8125rem;
      font-weight: 500;
      white-space: nowrap;
      margin-left: 12px;
      z-index: 100;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
      animation: tooltipFade 0.2s ease;
    }
    
    @keyframes tooltipFade {
      from { opacity: 0; transform: translateY(-50%) translateX(-4px); }
      to { opacity: 1; transform: translateY(-50%) translateX(0); }
    }
    
    .desktop-sidebar.collapsed .nav-item:hover::before {
      content: '';
      position: absolute;
      left: 100%;
      top: 50%;
      transform: translateY(-50%);
      border-width: 6px;
      border-style: solid;
      border-color: transparent var(--slate-800) transparent transparent;
      margin-left: 6px;
      z-index: 101;
    }
    
    /* Botón toggle del sidebar - diseño moderno */
    .toggle-sidebar-btn {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      width: 100%;
      padding: 0.625rem 1rem;
      background: linear-gradient(135deg, var(--slate-100) 0%, var(--slate-50) 100%);
      border: 1px solid var(--slate-200);
      border-radius: 0.75rem;
      cursor: pointer;
      font-size: 0.8125rem;
      font-weight: 500;
      color: var(--slate-600);
      transition: all 0.25s ease;
      white-space: nowrap;
    }
    
    .toggle-sidebar-btn:hover {
      background: linear-gradient(135deg, var(--primary-50) 0%, var(--primary-100) 100%);
      border-color: var(--primary-200);
      color: var(--primary-700);
      box-shadow: 0 4px 12px rgba(37, 99, 235, 0.15);
    }
    
    .toggle-sidebar-btn i {
      font-size: 0.75rem;
      transition: transform 0.3s ease;
    }
    
    .toggle-sidebar-btn .toggle-text {
      transition: all 0.2s ease;
    }
    
    .desktop-sidebar.collapsed .toggle-sidebar-btn {
      padding: 0.625rem;
      width: 40px;
      height: 40px;
      border-radius: 50%;
      margin: 0 auto;
    }
    
    .desktop-sidebar.collapsed .toggle-sidebar-btn .toggle-text {
      display: none;
    }
    
    .desktop-sidebar.collapsed .toggle-sidebar-btn i {
      transform: rotate(180deg);
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
    
    /* Animaciones de entrada */
    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(10px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    .card {
      animation: fadeInUp 0.3s ease;
    }
    
    /* Focus states accesibles */
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

    {{-- SIDEBAR PANEL PARA DESKTOP --}}
    <aside id="desktopSidebar" class="hidden md:block fixed inset-y-0 left-0 bg-white border-r overflow-y-auto desktop-sidebar w-64">
      {{-- Botón de toggle - Ahora en el footer del sidebar --}}
      
      <div class="p-4 border-b">
        <div class="flex items-center gap-3">
          <div class="flex-shrink-0">
            <img src="{{ asset('images/logo.png') }}" alt="CEOT DATES"
                 class="w-14 h-14 object-contain rounded-lg shadow-sm hide-when-collapsed"
                 onerror="this.style.display='none'">
          </div>
          <div class="hide-when-collapsed">
            <h1 class="brand-font">CEOT DATES</h1>
            @auth
              @php
                $u = auth()->user();
                $roleLabel = null;

                if ($u) {
                    if (method_exists($u, 'roles')) {
                        $firstRole = $u->roles()->first();
                        if ($firstRole) {
                            $roleLabel = strtoupper($firstRole->name);
                        }
                    } elseif (isset($u->role)) {
                        $roleLabel = strtoupper($u->role);
                    }
                }
              @endphp

              @if($roleLabel)
                <p class="brand-subtitle">{{ $roleLabel }}</p>
              @endif
            @endauth
          </div>
        </div>
      </div>

      <nav class="p-3 space-y-1">
        @auth
          {{-- DASHBOARD --}}
          @can('dashboard.view')
            <a href="{{ route('admin.dashboard') }}"
               class="nav-item {{ request()->routeIs('admin.dashboard') ? 'nav-active' : '' }}"
               data-title="Dashboard">
              <span class="inline-flex items-center gap-2">
                <i class="w-4 h-4 fas fa-chart-pie"></i>
                <span class="nav-text">Dashboard</span>
              </span>
            </a>
          @endcan

          {{-- CITAS --}}
          @canany(['appointments.index','agenda.view','appointments.manage'])
            <a href="{{ route('admin.appointments.index') }}"
               class="nav-item {{ request()->routeIs('admin.appointments.*') ? 'nav-active' : '' }}"
               data-title="Citas">
              <span class="inline-flex items-center gap-2">
                <i class="w-4 h-4 fas fa-calendar-check"></i>
                <span class="nav-text">Citas</span>
              </span>
            </a>
          @endcanany

          {{-- PACIENTES --}}
          @canany(['patients.index','patients.manage'])
            <a href="{{ route('admin.patients.index') }}"
               class="nav-item {{ request()->routeIs('admin.patients.*') ? 'nav-active' : '' }}"
               data-title="Pacientes">
              <span class="inline-flex items-center gap-2">
                <i class="w-4 h-4 fas fa-user-injured"></i>
                <span class="nav-text">Pacientes</span>
              </span>
            </a>
          @endcanany

          {{-- ODONTÓLOGOS --}}
          @can('users.manage')
            <a href="{{ route('admin.dentists') }}"
               class="nav-item {{ request()->routeIs('admin.dentists*') ? 'nav-active' : '' }}"
               data-title="Dentistas">
              <span class="inline-flex items-center gap-2">
                <i class="w-4 h-4 fas fa-user-md"></i>
                <span class="nav-text">Dentistas</span>
              </span>
            </a>
          @endcan

          {{-- SERVICIOS --}}
          @canany(['services.index', 'services.view'])
            <a href="{{ route('admin.services') }}"
               class="nav-item {{ request()->routeIs('admin.services*') ? 'nav-active' : '' }}"
               data-title="Servicios">
              <span class="inline-flex items-center gap-2">
                <i class="w-4 h-4 fas fa-teeth"></i>
                <span class="nav-text">Servicios</span>
              </span>
            </a>
          @endcanany

          {{-- HORARIOS --}}
          @canany(['schedules.index', 'schedules.view'])
            <a href="{{ route('admin.schedules') }}"
               class="nav-item {{ request()->routeIs('admin.schedules*') ? 'nav-active' : '' }}"
               data-title="Horarios">
              <span class="inline-flex items-center gap-2">
                <i class="w-4 h-4 fas fa-clock"></i>
                <span class="nav-text">Horarios</span>
              </span>
            </a>
          @endcanany

          {{-- PAGOS / CAJA --}}
          @canany(['billing.manage', 'billing.index', 'payments.view_status'])
            <a href="{{ route('admin.billing') }}"
               class="nav-item {{ request()->routeIs('admin.billing*') || request()->routeIs('admin.invoices.*') ? 'nav-active' : '' }}"
               data-title="Pagos">
              <span class="inline-flex items-center gap-2">
                <i class="w-4 h-4 fas fa-credit-card"></i>
                <span class="nav-text">Pagos</span>
              </span>
            </a>
          @endcanany

          {{-- CONSENTIMIENTOS --}}
          @can('medical_history.manage')
            <a href="{{ route('admin.consents.templates') }}"
               class="nav-item {{ request()->routeIs('admin.consents.*') ? 'nav-active' : '' }}"
               data-title="Plantillas de consentimiento">
              <span class="inline-flex items-center gap-2">
                <i class="w-4 h-4 fas fa-file-contract"></i>
                <span class="nav-text">Plantillas de consentimiento</span>
              </span>
            </a>
          @endcan

          {{-- CONSULTORIOS --}}
          @canany(['chairs.index', 'chairs.view'])
            <a href="{{ route('admin.chairs.index') }}"
               class="nav-item {{ request()->routeIs('admin.chairs.*') ? 'nav-active' : '' }}"
               data-title="Consultorios">
              <span class="inline-flex items-center gap-2">
                <i class="w-4 h-4 fas fa-chair"></i>
                <span class="nav-text">Consultorios</span>
              </span>
            </a>
          @endcanany

          {{-- INVENTARIO --}}
          @can('inventory.manage')
            <div class="pt-2 text-xs uppercase text-slate-400 font-medium hide-when-collapsed">
              <span class="nav-text">Inventario</span>
            </div>

            <a href="{{ route('admin.inv.products.index') }}"
               class="nav-item {{ request()->routeIs('admin.inv.products.*') ? 'nav-active' : '' }}"
               data-title="Productos">
              <span class="inline-flex items-center gap-2">
                <i class="w-4 h-4 fas fa-boxes"></i>
                <span class="nav-text">Productos</span>
              </span>
            </a>

            <a href="{{ route('admin.inv.suppliers.index') }}"
               class="nav-item {{ request()->routeIs('admin.inv.suppliers.*') ? 'nav-active' : '' }}"
               data-title="Proveedores">
              <span class="inline-flex items-center gap-2">
                <i class="w-4 h-4 fas fa-truck"></i>
                <span class="nav-text">Proveedores</span>
              </span>
            </a>

            <a href="{{ route('admin.inv.measurement_units.index') }}"
               class="nav-item {{ request()->routeIs('admin.inv.measurement_units.*') ? 'nav-active' : '' }}"
               data-title="Unidades de medida">
              <span class="inline-flex items-center gap-2">
                <i class="w-4 h-4 fas fa-ruler"></i>
                <span class="nav-text">Unidades de medida</span>
              </span>
            </a>

            <a href="{{ route('admin.inv.presentation_units.index') }}"
               class="nav-item {{ request()->routeIs('admin.inv.presentation_units.*') ? 'nav-active' : '' }}"
               data-title="Unidades de presentación">
              <span class="inline-flex items-center gap-2">
                <i class="w-4 h-4 fas fa-layer-group"></i>
                <span class="nav-text">Unidades de presentación</span>
              </span>
            </a>

            <a href="{{ route('admin.inv.movs.index') }}"
               class="nav-item {{ request()->routeIs('admin.inv.movs.*') ? 'nav-active' : '' }}"
               data-title="Movimientos">
              <span class="inline-flex items-center gap-2">
                <i class="w-4 h-4 fas fa-exchange-alt"></i>
                <span class="nav-text">Movimientos</span>
              </span>
            </a>
          @endcan

          {{-- SEGURIDAD --}}
          @canany(['users.manage','roles.manage','permissions.manage'])
            <div class="pt-2 text-xs uppercase text-slate-400 font-medium hide-when-collapsed">
              <span class="nav-text">Seguridad</span>
            </div>
          @endcanany

          @can('users.manage')
            <a href="{{ route('admin.users.index') }}"
               class="nav-item {{ request()->routeIs('admin.users.*') ? 'nav-active' : '' }}"
               data-title="Usuarios">
              <span class="inline-flex items-center gap-2">
                <i class="w-4 h-4 fas fa-users"></i>
                <span class="nav-text">Usuarios</span>
              </span>
            </a>
          @endcan

          @can('roles.manage')
            <a href="{{ route('admin.roles.index') }}"
               class="nav-item {{ request()->routeIs('admin.roles.*') ? 'nav-active' : '' }}"
               data-title="Roles">
              <span class="inline-flex items-center gap-2">
                <i class="w-4 h-4 fas fa-user-shield"></i>
                <span class="nav-text">Roles</span>
              </span>
            </a>
          @endcan

          @can('permissions.manage')
            <a href="{{ route('admin.permissions.index') }}"
               class="nav-item {{ request()->routeIs('admin.permissions.*') ? 'nav-active' : '' }}"
               data-title="Permisos">
              <span class="inline-flex items-center gap-2">
                <i class="w-4 h-4 fas fa-key"></i>
                <span class="nav-text">Permisos</span>
              </span>
            </a>
          @endcan

          @can('users.manage')
            <div class="pt-2 text-xs uppercase text-slate-400 font-medium hide-when-collapsed">
               <span class="nav-text">Sistema</span>
            </div>
            <a href="{{ route('admin.emails.logs') }}"
               class="nav-item {{ request()->routeIs('admin.emails.*') ? 'nav-active' : '' }}"
               data-title="Historial de Correos">
              <span class="inline-flex items-center gap-2">
                <i class="w-4 h-4 fas fa-envelope"></i>
                <span class="nav-text">Historial de Correos</span>
              </span>
            </a>
          @endcan
        @endauth
      </nav>

      @auth
        <div class="p-3 space-y-2">
          {{-- Botón de colapsar/expandir --}}
          <button class="toggle-sidebar-btn w-full" id="toggleSidebar" title="Contraer/Expandir menú">
            <i class="fas fa-chevron-left"></i>
            <span class="toggle-text">Contraer menú</span>
          </button>
          
          <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="w-full text-left btn btn-ghost"
                    data-title="Cerrar sesión">
              <i class="w-4 h-4 fas fa-sign-out-alt"></i>
              <span class="nav-text hide-when-collapsed">Cerrar sesión</span>
            </button>
          </form>
        </div>
      @endauth
    </aside>

    {{-- MENÚ MÓVIL --}}
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
            @auth
              @php
                $u = auth()->user();
                $roleLabel = null;

                if ($u) {
                    if (method_exists($u, 'roles')) {
                        $firstRole = $u->roles()->first();
                        if ($firstRole) {
                            $roleLabel = strtoupper($firstRole->name);
                        }
                    } elseif (isset($u->role)) {
                        $roleLabel = strtoupper($u->role);
                    }
                }
              @endphp

              @if($roleLabel)
                <p class="brand-subtitle text-xs">{{ $roleLabel }}</p>
              @endif
            @endauth
          </div>
        </div>
        {{-- BOTÓN CERRAR MENÚ MÓVIL --}}
        <button class="p-2 rounded-lg hover:bg-slate-100" id="closeMenuButton">
          <i class="fas fa-times text-slate-600"></i>
        </button>
      </div>

      <nav class="p-3 space-y-1">
        @auth
          {{-- DASHBOARD --}}
          @can('dashboard.view')
            <a href="{{ route('admin.dashboard') }}"
               class="nav-item {{ request()->routeIs('admin.dashboard') ? 'nav-active' : '' }} mobile-nav-link">
              <span class="inline-flex items-center gap-2">
                <i class="w-4 h-4 fas fa-chart-pie"></i>
                Dashboard
              </span>
            </a>
          @endcan

          {{-- CITAS --}}
          @canany(['appointments.manage','agenda.view','appointments.request'])
            <a href="{{ route('admin.appointments.index') }}"
               class="nav-item {{ request()->routeIs('admin.appointments.*') ? 'nav-active' : '' }} mobile-nav-link">
              <span class="inline-flex items-center gap-2">
                <i class="w-4 h-4 fas fa-calendar-check"></i>
                Citas
              </span>
            </a>
          @endcanany

          {{-- PACIENTES --}}
          @can('patients.manage')
            <a href="{{ route('admin.patients.index') }}"
               class="nav-item {{ request()->routeIs('admin.patients.*') ? 'nav-active' : '' }} mobile-nav-link">
              <span class="inline-flex items-center gap-2">
                <i class="w-4 h-4 fas fa-user-injured"></i>
                Pacientes
              </span>
            </a>
          @endcan

          {{-- ODONTÓLOGOS --}}
          @can('users.manage')
            <a href="{{ route('admin.dentists') }}"
               class="nav-item {{ request()->routeIs('admin.dentists*') ? 'nav-active' : '' }} mobile-nav-link">
              <span class="inline-flex items-center gap-2">
                <i class="w-4 h-4 fas fa-user-md"></i>
                Dentistas
              </span>
            </a>
          @endcan

          {{-- SERVICIOS --}}
          @can('appointments.manage')
            <a href="{{ route('admin.services') }}"
               class="nav-item {{ request()->routeIs('admin.services*') ? 'nav-active' : '' }} mobile-nav-link">
              <span class="inline-flex items-center gap-2">
                <i class="w-4 h-4 fas fa-teeth"></i>
                Servicios
              </span>
            </a>
          @endcan

          {{-- HORARIOS --}}
          @can('appointments.manage')
            <a href="{{ route('admin.schedules') }}"
               class="nav-item {{ request()->routeIs('admin.schedules*') ? 'nav-active' : '' }} mobile-nav-link">
              <span class="inline-flex items-center gap-2">
                <i class="w-4 h-4 fas fa-clock"></i>
                Horarios
              </span>
            </a>
          @endcan

          {{-- PAGOS / CAJA --}}
          @canany(['billing.manage','payments.view_status'])
            <a href="{{ route('admin.billing') }}"
               class="nav-item {{ request()->routeIs('admin.billing*') || request()->routeIs('admin.invoices.*') ? 'nav-active' : '' }} mobile-nav-link">
              <span class="inline-flex items-center gap-2">
                <i class="w-4 h-4 fas fa-credit-card"></i>
                Pagos
              </span>
            </a>
          @endcanany

          {{-- CONSENTIMIENTOS --}}
          @can('medical_history.manage')
            <a href="{{ route('admin.consents.templates') }}"
               class="nav-item {{ request()->routeIs('admin.consents.*') ? 'nav-active' : '' }} mobile-nav-link">
              <span class="inline-flex items-center gap-2">
                <i class="w-4 h-4 fas fa-file-contract"></i>
                Plantillas de consentimiento
              </span>
            </a>
          @endcan

          {{-- CONSULTORIOS --}}
          @can('appointments.manage')
            <a href="{{ route('admin.chairs.index') }}"
               class="nav-item {{ request()->routeIs('admin.chairs.*') ? 'nav-active' : '' }} mobile-nav-link">
              <span class="inline-flex items-center gap-2">
                <i class="w-4 h-4 fas fa-chair"></i>
                Consultorios
              </span>
            </a>
          @endcan

          {{-- INVENTARIO --}}
          @can('inventory.manage')
            <div class="pt-2 text-xs uppercase text-slate-400 font-medium">Inventario</div>

            <a href="{{ route('admin.inv.products.index') }}"
               class="nav-item {{ request()->routeIs('admin.inv.products.*') ? 'nav-active' : '' }} mobile-nav-link">
              <span class="inline-flex items-center gap-2">
                <i class="w-4 h-4 fas fa-boxes"></i>
                Productos
              </span>
            </a>

            <a href="{{ route('admin.inv.suppliers.index') }}"
               class="nav-item {{ request()->routeIs('admin.inv.suppliers.*') ? 'nav-active' : '' }} mobile-nav-link">
              <span class="inline-flex items-center gap-2">
                <i class="w-4 h-4 fas fa-truck"></i>
                Proveedores
              </span>
            </a>

            <a href="{{ route('admin.inv.measurement_units.index') }}"
               class="nav-item {{ request()->routeIs('admin.inv.measurement_units.*') ? 'nav-active' : '' }} mobile-nav-link">
              <span class="inline-flex items-center gap-2">
                <i class="w-4 h-4 fas fa-ruler"></i>
                Unidades de medida
              </span>
            </a>

            <a href="{{ route('admin.inv.presentation_units.index') }}"
               class="nav-item {{ request()->routeIs('admin.inv.presentation_units.*') ? 'nav-active' : '' }} mobile-nav-link">
              <span class="inline-flex items-center gap-2">
                <i class="w-4 h-4 fas fa-layer-group"></i>
                Unidades de presentación
              </span>
            </a>

            <a href="{{ route('admin.inv.movs.index') }}"
               class="nav-item {{ request()->routeIs('admin.inv.movs.*') ? 'nav-active' : '' }} mobile-nav-link">
              <span class="inline-flex items-center gap-2">
                <i class="w-4 h-4 fas fa-exchange-alt"></i>
                Movimientos
              </span>
            </a>
          @endcan

          {{-- SEGURIDAD --}}
          @canany(['users.manage','roles.manage','permissions.manage'])
            <div class="pt-2 text-xs uppercase text-slate-400 font-medium">Seguridad</div>
          @endcanany

          @can('users.manage')
            <a href="{{ route('admin.users.index') }}"
               class="nav-item {{ request()->routeIs('admin.users.*') ? 'nav-active' : '' }} mobile-nav-link">
              <span class="inline-flex items-center gap-2">
                <i class="w-4 h-4 fas fa-users"></i>
                Usuarios
              </span>
            </a>
          @endcan

          @can('roles.manage')
            <a href="{{ route('admin.roles.index') }}"
               class="nav-item {{ request()->routeIs('admin.roles.*') ? 'nav-active' : '' }} mobile-nav-link">
              <span class="inline-flex items-center gap-2">
                <i class="w-4 h-4 fas fa-user-shield"></i>
                Roles
              </span>
            </a>
          @endcan

          @can('permissions.manage')
            <a href="{{ route('admin.permissions.index') }}"
               class="nav-item {{ request()->routeIs('admin.permissions.*') ? 'nav-active' : '' }} mobile-nav-link">
              <span class="inline-flex items-center gap-2">
                <i class="w-4 h-4 fas fa-key"></i>
                Permisos
              </span>
            </a>
          @endcan
        @endauth
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
    <main id="mainContent" class="w-full transition-all duration-300 md:pl-64">
      <header class="sticky top-0 z-10 bg-white/95 backdrop-blur border-b px-4 py-3 flex items-center justify-between">
        <div class="flex items-center gap-3">
          {{-- BOTÓN MENÚ MÓVIL EN HEADER -- AHORA VISIBLE --}}
          <button id="mobileMenuButtonHeader" class="p-2 rounded-lg hover:bg-slate-100 border border-gray-200">
            <i class="fas fa-bars text-slate-600"></i>
          </button>
          <div>
            <h2 class="text-lg font-semibold leading-none" style="font-family: 'Outfit', sans-serif;">
              @yield('title','Dashboard')
            </h2>
            @auth
              <p class="text-[11px] text-slate-500">Hola, {{ auth()->user()->name }}</p>
            @endauth
          </div>
        </div>
        <div class="flex items-center gap-2">
          @hasSection('header-actions')
            @yield('header-actions')
          @else
            @auth
              {{-- Botón rápido sólo si tiene permiso de gestionar citas --}}
              @can('appointments.manage')
                @unless(request()->routeIs('admin.appointments.*'))
                  <a href="{{ route('admin.appointments.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Nueva cita
                  </a>
                @endunless
              @endcan
            @endauth
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

        @yield('content')
      </div>
    </main>
  </div>

  <script>
    // Control del menú móvil - CONFIRMADO
    document.addEventListener('DOMContentLoaded', function() {
      console.log('Configurando menú móvil...');
      
      const mobileMenuButtonHeader = document.getElementById('mobileMenuButtonHeader');
      const closeMenuButton = document.getElementById('closeMenuButton');
      const mobileOverlay = document.getElementById('mobileOverlay');
      const mobileMenu = document.querySelector('.mobile-menu');
      const mobileNavLinks = document.querySelectorAll('.mobile-nav-link');
      
      function openMobileMenu() {
        console.log('Abriendo menú móvil');
        mobileMenu.classList.add('open');
        mobileOverlay.classList.add('active');
        document.body.style.overflow = 'hidden';
      }
      
      function closeMobileMenu() {
        console.log('Cerrando menú móvil');
        mobileMenu.classList.remove('open');
        mobileOverlay.classList.remove('active');
        document.body.style.overflow = '';
      }
      
      // Abrir menú móvil
      if (mobileMenuButtonHeader) {
        console.log('Botón de menú encontrado');
        mobileMenuButtonHeader.addEventListener('click', openMobileMenu);
      } else {
        console.error('Botón de menú NO encontrado');
      }
      
      // Cerrar menú móvil
      if (closeMenuButton) {
        closeMenuButton.addEventListener('click', closeMobileMenu);
      }
      if (mobileOverlay) {
        mobileOverlay.addEventListener('click', closeMobileMenu);
      }
      
      // Cerrar menú al hacer clic en un enlace
      if (mobileNavLinks.length > 0) {
        mobileNavLinks.forEach(link => {
          link.addEventListener('click', closeMobileMenu);
        });
      }
      
      // Cerrar menú con tecla ESC
      document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
          closeMobileMenu();
        }
      });
      
      // Control del menú desktop
      const desktopSidebar = document.getElementById('desktopSidebar');
      const toggleSidebarBtn = document.getElementById('toggleSidebar');
      const mainContent = document.getElementById('mainContent');
      const toggleText = toggleSidebarBtn ? toggleSidebarBtn.querySelector('.toggle-text') : null;
      
      // Función para actualizar texto del botón
      function updateToggleText(collapsed) {
        if (toggleText) {
          toggleText.textContent = collapsed ? 'Expandir' : 'Contraer menú';
        }
      }
      
      // Verificar estado guardado
      const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
      
      if (isCollapsed && desktopSidebar) {
        desktopSidebar.classList.add('collapsed');
        mainContent.classList.remove('md:pl-64');
        mainContent.classList.add('md:pl-20');
        updateToggleText(true);
      }
      
      // Toggle del menú desktop
      if (toggleSidebarBtn) {
        toggleSidebarBtn.addEventListener('click', function(e) {
          e.preventDefault();
          e.stopPropagation();
          
          const isCurrentlyCollapsed = desktopSidebar.classList.contains('collapsed');
          
          if (isCurrentlyCollapsed) {
            // Expandir
            desktopSidebar.classList.remove('collapsed');
            mainContent.classList.remove('md:pl-20');
            mainContent.classList.add('md:pl-64');
            localStorage.setItem('sidebarCollapsed', 'false');
            updateToggleText(false);
          } else {
            // Contraer
            desktopSidebar.classList.add('collapsed');
            mainContent.classList.remove('md:pl-64');
            mainContent.classList.add('md:pl-20');
            localStorage.setItem('sidebarCollapsed', 'true');
            updateToggleText(true);
          }
        });
      }
      
      // Opcional: contraer/expandir con doble clic en logo
      const logoArea = desktopSidebar ? desktopSidebar.querySelector('.p-4.border-b') : null;
      if (logoArea) {
        logoArea.addEventListener('dblclick', function() {
          if (toggleSidebarBtn) {
            toggleSidebarBtn.click();
          }
        });
      }
    });
  </script>

  @yield('scripts')
  @stack('scripts')
</body>
</html>