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
    .card{background:#fff;border-radius:.75rem;box-shadow:0 1px 2px rgba(16,24,40,.05),0 1px 3px rgba(16,24,40,.1);padding:1rem}
    .btn{padding:.5rem .75rem;border-radius:.5rem;font-size:.875rem;line-height:1.25rem;display:inline-flex;align-items:center;gap:.4rem}
    .btn-primary{background:#2563eb;color:#fff}.btn-primary:hover{background:#1d4ed8}
    .btn-danger{background:#ef4444;color:#fff}.btn-danger:hover{background:#dc2626}
    .btn-ghost{background:#f8fafc}.btn-ghost:hover{background:#f1f5f9}
    .nav-item{display:block;padding:.5rem .75rem;border-radius:.5rem}.nav-item:hover{background:#f1f5f9}
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
      
      #mobileMenuButtonHeader:hover {
        background: #f9fafb;
      }
      
      /* Asegurar que el header tenga espacio para el botón */
      header {
        padding-left: 1rem !important;
      }
    }
    
    /* Estilos para el menú móvil */
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
    
    /* Estilos para menú desktop mejorado */
    .desktop-sidebar {
      transition: all 0.3s ease;
    }
    
    .desktop-sidebar.collapsed {
      width: 70px !important;
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
    
    /* Tooltips para modo contraído */
    .desktop-sidebar.collapsed .nav-item:hover::after {
      content: attr(data-title);
      position: absolute;
      left: 100%;
      top: 50%;
      transform: translateY(-50%);
      background: #1f2937;
      color: white;
      padding: 0.5rem 0.75rem;
      border-radius: 0.375rem;
      font-size: 0.875rem;
      white-space: nowrap;
      margin-left: 10px;
      z-index: 100;
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }
    
    .desktop-sidebar.collapsed .nav-item:hover::before {
      content: '';
      position: absolute;
      left: 100%;
      top: 50%;
      transform: translateY(-50%);
      border-width: 6px;
      border-style: solid;
      border-color: transparent #1f2937 transparent transparent;
      margin-left: 4px;
      z-index: 101;
    }
    
    /* Botón de toggle más visible */
    .toggle-btn {
      position: absolute;
      right: -12px;
      top: 20px;
      width: 24px;
      height: 24px;
      background: white;
      border: 2px solid #e5e7eb;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      transition: all 0.2s;
      z-index: 10;
    }
    
    .toggle-btn:hover {
      background: #f3f4f6;
      transform: scale(1.1);
    }
    
    .toggle-btn i {
      font-size: 12px;
      color: #6b7280;
      transition: transform 0.3s;
    }
    
    .desktop-sidebar.collapsed .toggle-btn i {
      transform: rotate(180deg);
    }
  </style>
</head>
<body class="bg-slate-100 min-h-screen" style="font-family: 'Inter', sans-serif;">
  <div class="min-h-screen">
    {{-- OVERLAY PARA MÓVIL --}}
    <div class="mobile-menu-overlay" id="mobileOverlay"></div>

    {{-- SIDEBAR PANEL PARA DESKTOP --}}
    <aside id="desktopSidebar" class="hidden md:block fixed inset-y-0 left-0 bg-white border-r overflow-y-auto desktop-sidebar w-64">
      {{-- Botón de toggle --}}
      <button class="toggle-btn" id="toggleSidebar" title="Contraer/Expandir menú">
        <i class="fas fa-chevron-left"></i>
      </button>
      
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
          @canany(['appointments.manage','agenda.view','appointments.request'])
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
          @can('patients.manage')
            <a href="{{ route('admin.patients.index') }}"
               class="nav-item {{ request()->routeIs('admin.patients.*') ? 'nav-active' : '' }}"
               data-title="Pacientes">
              <span class="inline-flex items-center gap-2">
                <i class="w-4 h-4 fas fa-user-injured"></i>
                <span class="nav-text">Pacientes</span>
              </span>
            </a>
          @endcan

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
          @can('appointments.manage')
            <a href="{{ route('admin.services') }}"
               class="nav-item {{ request()->routeIs('admin.services*') ? 'nav-active' : '' }}"
               data-title="Servicios">
              <span class="inline-flex items-center gap-2">
                <i class="w-4 h-4 fas fa-teeth"></i>
                <span class="nav-text">Servicios</span>
              </span>
            </a>
          @endcan

          {{-- HORARIOS --}}
          @can('appointments.manage')
            <a href="{{ route('admin.schedules') }}"
               class="nav-item {{ request()->routeIs('admin.schedules*') ? 'nav-active' : '' }}"
               data-title="Horarios">
              <span class="inline-flex items-center gap-2">
                <i class="w-4 h-4 fas fa-clock"></i>
                <span class="nav-text">Horarios</span>
              </span>
            </a>
          @endcan

          {{-- PAGOS / CAJA --}}
          @canany(['billing.manage','payments.view_status'])
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
          @can('appointments.manage')
            <a href="{{ route('admin.chairs.index') }}"
               class="nav-item {{ request()->routeIs('admin.chairs.*') ? 'nav-active' : '' }}"
               data-title="Consultorios">
              <span class="inline-flex items-center gap-2">
                <i class="w-4 h-4 fas fa-chair"></i>
                <span class="nav-text">Consultorios</span>
              </span>
            </a>
          @endcan

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
        @endauth
      </nav>

      @auth
        <div class="p-3">
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
      
      // Verificar estado guardado
      const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
      
      if (isCollapsed) {
        desktopSidebar.classList.add('collapsed');
        mainContent.classList.remove('md:pl-64');
        mainContent.classList.add('md:pl-20');
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
          } else {
            // Contraer
            desktopSidebar.classList.add('collapsed');
            mainContent.classList.remove('md:pl-64');
            mainContent.classList.add('md:pl-20');
            localStorage.setItem('sidebarCollapsed', 'true');
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