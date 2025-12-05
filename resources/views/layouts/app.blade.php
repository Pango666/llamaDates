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
  </style>
</head>
<body class="bg-slate-100 min-h-screen" style="font-family: 'Inter', sans-serif;">
  <div class="min-h-screen">
    {{-- SIDEBAR PANEL (ADMIN / ASISTENTE / ODONTÓLOGO / ETC) --}}
    <aside class="hidden md:block fixed inset-y-0 left-0 w-72 bg-white border-r overflow-y-auto">
      <div class="p-4 border-b">
        <div class="flex items-center gap-3">
          <div class="flex-shrink-0">
            <img src="{{ asset('images/logo.png') }}" alt="CEOT DATES"
                 class="w-14 h-14 object-contain rounded-lg shadow-sm"
                 onerror="this.style.display='none'">
          </div>
          <div>
            <h1 class="brand-font">CEOT DATES</h1>
            @auth
              @php
                $u = auth()->user();
                $roleLabel = null;

                if ($u) {
                    // si usas relación roles()
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
               class="nav-item {{ request()->routeIs('admin.dashboard') ? 'nav-active' : '' }}">
              <span class="inline-flex items-center gap-2">
                <i class="w-4 h-4 fas fa-chart-pie"></i>
                Dashboard
              </span>
            </a>
          @endcan

          {{-- CITAS --}}
          @canany(['appointments.manage','agenda.view','appointments.request'])
            <a href="{{ route('admin.appointments.index') }}"
               class="nav-item {{ request()->routeIs('admin.appointments.*') ? 'nav-active' : '' }}">
              <span class="inline-flex items-center gap-2">
                <i class="w-4 h-4 fas fa-calendar-check"></i>
                Citas
              </span>
            </a>
          @endcanany

          {{-- PACIENTES --}}
          @can('patients.manage')
            <a href="{{ route('admin.patients.index') }}"
               class="nav-item {{ request()->routeIs('admin.patients.*') ? 'nav-active' : '' }}">
              <span class="inline-flex items-center gap-2">
                <i class="w-4 h-4 fas fa-user-injured"></i>
                Pacientes
              </span>
            </a>
          @endcan

          {{-- ODONTÓLOGOS --}}
          @can('users.manage')
            <a href="{{ route('admin.dentists') }}"
               class="nav-item {{ request()->routeIs('admin.dentists*') ? 'nav-active' : '' }}">
              <span class="inline-flex items-center gap-2">
                <i class="w-4 h-4 fas fa-user-md"></i>
                Dentistas
              </span>
            </a>
          @endcan

          {{-- SERVICIOS --}}
          @can('appointments.manage')
            <a href="{{ route('admin.services') }}"
               class="nav-item {{ request()->routeIs('admin.services*') ? 'nav-active' : '' }}">
              <span class="inline-flex items-center gap-2">
                <i class="w-4 h-4 fas fa-teeth"></i>
                Servicios
              </span>
            </a>
          @endcan

          {{-- HORARIOS --}}
          @can('appointments.manage')
            <a href="{{ route('admin.schedules') }}"
               class="nav-item {{ request()->routeIs('admin.schedules*') ? 'nav-active' : '' }}">
              <span class="inline-flex items-center gap-2">
                <i class="w-4 h-4 fas fa-clock"></i>
                Horarios
              </span>
            </a>
          @endcan

          {{-- PAGOS / CAJA --}}
          @canany(['billing.manage','payments.view_status'])
            <a href="{{ route('admin.billing') }}"
               class="nav-item {{ request()->routeIs('admin.billing*') || request()->routeIs('admin.invoices.*') ? 'nav-active' : '' }}">
              <span class="inline-flex items-center gap-2">
                <i class="w-4 h-4 fas fa-credit-card"></i>
                Pagos
              </span>
            </a>
          @endcanany

          {{-- CONSENTIMIENTOS --}}
          @can('medical_history.manage')
            <a href="{{ route('admin.consents.templates') }}"
               class="nav-item {{ request()->routeIs('admin.consents.*') ? 'nav-active' : '' }}">
              <span class="inline-flex items-center gap-2">
                <i class="w-4 h-4 fas fa-file-contract"></i>
                Plantillas de consentimiento
              </span>
            </a>
          @endcan

          {{-- CONSULTORIOS --}}
          @can('appointments.manage')
            <a href="{{ route('admin.chairs.index') }}"
               class="nav-item {{ request()->routeIs('admin.chairs.*') ? 'nav-active' : '' }}">
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
               class="nav-item {{ request()->routeIs('admin.inv.products.*') ? 'nav-active' : '' }}">
              <span class="inline-flex items-center gap-2">
                <i class="w-4 h-4 fas fa-boxes"></i>
                Productos
              </span>
            </a>

            <a href="{{ route('admin.inv.suppliers.index') }}"
               class="nav-item {{ request()->routeIs('admin.inv.suppliers.*') ? 'nav-active' : '' }}">
              <span class="inline-flex items-center gap-2">
                <i class="w-4 h-4 fas fa-truck"></i>
                Proveedores
              </span>
            </a>

            <a href="{{ route('admin.inv.measurement_units.index') }}"
               class="nav-item {{ request()->routeIs('admin.inv.measurement_units.*') ? 'nav-active' : '' }}">
              <span class="inline-flex items-center gap-2">
                <i class="w-4 h-4 fas fa-ruler"></i>
                Unidades de medida
              </span>
            </a>

            <a href="{{ route('admin.inv.presentation_units.index') }}"
               class="nav-item {{ request()->routeIs('admin.inv.presentation_units.*') ? 'nav-active' : '' }}">
              <span class="inline-flex items-center gap-2">
                <i class="w-4 h-4 fas fa-layer-group"></i>
                Unidades de presentación
              </span>
            </a>

            <a href="{{ route('admin.inv.movs.index') }}"
               class="nav-item {{ request()->routeIs('admin.inv.movs.*') ? 'nav-active' : '' }}">
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
               class="nav-item {{ request()->routeIs('admin.users.*') ? 'nav-active' : '' }}">
              <span class="inline-flex items-center gap-2">
                <i class="w-4 h-4 fas fa-users"></i>
                Usuarios
              </span>
            </a>
          @endcan

          @can('roles.manage')
            <a href="{{ route('admin.roles.index') }}"
               class="nav-item {{ request()->routeIs('admin.roles.*') ? 'nav-active' : '' }}">
              <span class="inline-flex items-center gap-2">
                <i class="w-4 h-4 fas fa-user-shield"></i>
                Roles
              </span>
            </a>
          @endcan

          @can('permissions.manage')
            <a href="{{ route('admin.permissions.index') }}"
               class="nav-item {{ request()->routeIs('admin.permissions.*') ? 'nav-active' : '' }}">
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
          <button class="w-full text-left btn btn-ghost">
            <i class="w-4 h-4 fas fa-sign-out-alt"></i>
            Cerrar sesión
          </button>
        </form>
      @endauth
    </aside>

    {{-- MAIN --}}
    <main class="w-full md:pl-72">
      <header class="sticky top-0 z-10 bg-white/95 backdrop-blur border-b px-4 py-3 flex items-center justify-between">
        <div>
          <h2 class="text-lg font-semibold leading-none" style="font-family: 'Outfit', sans-serif;">
            @yield('title','Dashboard')
          </h2>
          @auth
            <p class="text-[11px] text-slate-500">Hola, {{ auth()->user()->name }}</p>
          @endauth
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

  @yield('scripts')
  @stack('scripts')
</body>
</html>
