<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title','Panel') · llamaDates</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="{{ asset('js/app.js') }}"></script>
  <style>
    .card{background:#fff;border-radius:.75rem;box-shadow:0 1px 2px rgba(16,24,40,.05),0 1px 3px rgba(16,24,40,.1);padding:1rem}
    .btn{padding:.5rem .75rem;border-radius:.5rem;font-size:.875rem;line-height:1.25rem;display:inline-flex;align-items:center;gap:.4rem}
    .btn-primary{background:#2563eb;color:#fff}.btn-primary:hover{background:#1d4ed8}
    .btn-danger{background:#ef4444;color:#fff}.btn-danger:hover{background:#dc2626}
    .btn-ghost{background:#f8fafc}.btn-ghost:hover{background:#f1f5f9}
    .nav-item{display:block;padding:.5rem .75rem;border-radius:.5rem}.nav-item:hover{background:#f1f5f9}
    .nav-active{background:#eef2ff;color:#4338ca;font-weight:600}
    .badge{font-size:.75rem;padding:.125rem .5rem;border-radius:.375rem}
  </style>
</head>
<body class="bg-slate-100 min-h-screen">
  <div class="min-h-screen">
    {{-- Sidebar fijo --}}
    <aside class="hidden md:block fixed inset-y-0 left-0 w-72 bg-white border-r overflow-y-auto">
      <div class="p-4 border-b">
        <h1 class="font-semibold text-lg">llamaDates</h1>
        @auth
          @php
            $u = auth()->user();
            $roleLabel = method_exists($u, 'roles') && $u->roles->count()
              ? strtoupper($u->roles->first()->name)
              : strtoupper((string)($u->role ?? ''));
            $isAdmin = $u && (method_exists($u,'hasRole') ? $u->hasRole('admin') : (($u->role ?? null)==='admin'));
            $isPatient = $u && (method_exists($u,'hasRole') ? $u->hasRole('paciente') : (($u->role ?? null)==='paciente'));
          @endphp
          <p class="text-xs text-slate-500">{{ $roleLabel }}</p>
        @endauth
      </div>

      <nav class="p-3 space-y-1">
        {{-- ADMIN MENU --}}
        @if(!empty($isAdmin) && $isAdmin)
          <a href="{{ route('admin.dashboard') }}"
             class="nav-item {{ request()->routeIs('admin.dashboard') ? 'nav-active' : '' }}">
            <span class="inline-flex items-center gap-2">
              {{-- icon dashboard --}}
              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M3 13h8V3H3zM13 21h8v-8h-8zM13 3h8v6h-8zM3 21h8v-6H3z"/></svg>
              Dashboard
            </span>
          </a>
          <a href="{{ route('admin.appointments.index') }}"
             class="nav-item {{ request()->routeIs('admin.appointments.*') ? 'nav-active' : '' }}">
            <span class="inline-flex items-center gap-2">
              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <rect x="3" y="4" width="18" height="18" rx="2"/>
                <path d="M16 2v4M8 2v4M3 10h18"/>
              </svg>
              Citas
            </span>
          </a>
          <a href="{{ route('admin.patients.index') }}"
             class="nav-item {{ request()->routeIs('admin.patients*') ? 'nav-active' : '' }}">
            <span class="inline-flex items-center gap-2">
              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M16 14a4 4 0 1 0-8 0M12 7a4 4 0 1 0 0-8"/></svg>
              Pacientes
            </span>
          </a>
          <a href="{{ route('admin.dentists') }}"
             class="nav-item {{ request()->routeIs('admin.dentists*') ? 'nav-active' : '' }}">
            <span class="inline-flex items-center gap-2">
              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 2s4 2 4 6-2 6-4 6-4-2-4-6 4-6 4-6z"/><path d="M8 13v7M16 13v7"/></svg>
              Dentistas
            </span>
          </a>
          <a href="{{ route('admin.services') }}"
             class="nav-item {{ request()->routeIs('admin.services*') ? 'nav-active' : '' }}">
            <span class="inline-flex items-center gap-2">
              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M9 18V5l12-2v13"/></svg>
              Servicios
            </span>
          </a>
          <a href="{{ route('admin.schedules') }}"
             class="nav-item {{ request()->routeIs('admin.schedules*') ? 'nav-active' : '' }}">
            <span class="inline-flex items-center gap-2">
              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M3 3h18v18H3z"/><path d="M3 9h18"/></svg>
              Horarios
            </span>
          </a>
          <a href="{{ route('admin.billing') }}"
             class="nav-item {{ request()->routeIs('admin.billing') ? 'nav-active' : '' }}">
            <span class="inline-flex items-center gap-2">
              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M6 2h12v20l-3-2-3 2-3-2-3 2z"/></svg>
              Pagos
            </span>
          </a>
          <a href="{{ route('admin.consents.templates') }}"
             class="nav-item {{ request()->routeIs('admin.consents.templates*') ? 'nav-active' : '' }}">
            <span class="inline-flex items-center gap-2">
              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M4 4h16v16H4z"/><path d="M8 4v16"/></svg>
              Plantillas de consentimiento
            </span>
          </a>
          <a href="{{ route('admin.chairs.index') }}"
             class="nav-item {{ request()->routeIs('admin.chairs.*') ? 'nav-active' : '' }}">
            <span class="inline-flex items-center gap-2">
              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M6 12h12M6 18h12M9 22V2M15 22V2"/></svg>
              Sillas
            </span>
          </a>

          {{-- Inventario --}}
          <div class="pt-2 text-xs uppercase text-slate-400">Inventario</div>
          <a href="{{ route('admin.inv.products.index') }}"
             class="nav-item {{ request()->routeIs('admin.inv.products.*') ? 'nav-active' : '' }}">
            <span class="inline-flex items-center gap-2">
              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="3" width="18" height="7"/><rect x="3" y="14" width="18" height="7"/></svg>
              Productos
            </span>
          </a>
          <a href="{{ route('admin.inv.suppliers.index') }}"
             class="nav-item {{ request()->routeIs('admin.inv.suppliers.*') ? 'nav-active' : '' }}">
            <span class="inline-flex items-center gap-2">
              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M3 7h18M3 12h18M3 17h18"/></svg>
              Proveedores
            </span>
          </a>
          <a href="{{ route('admin.inv.locations.index') }}"
             class="nav-item {{ request()->routeIs('admin.inv.locations.*') ? 'nav-active' : '' }}">
            <span class="inline-flex items-center gap-2">
              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M21 10V3H3v7l9 11 9-11z"/></svg>
              Depósitos
            </span>
          </a>
          <a href="{{ route('admin.inv.movs.index') }}"
             class="nav-item {{ request()->routeIs('admin.inv.movs.*') ? 'nav-active' : '' }}">
            <span class="inline-flex items-center gap-2">
              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M4 19h16M4 5h16M7 10h10M7 14h10"/></svg>
              Movimientos
            </span>
          </a>

          {{-- Seguridad --}}
          <div class="pt-2 text-xs uppercase text-slate-400">Seguridad</div>
          <a href="{{ route('admin.users.index') }}"
             class="nav-item {{ request()->routeIs('admin.users.*') ? 'nav-active' : '' }}">
            <span class="inline-flex items-center gap-2">
              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M16 14a4 4 0 1 0-8 0M12 7a4 4 0 1 0 0-8"/></svg>
              Usuarios
            </span>
          </a>
          <a href="{{ route('admin.roles.index') }}"
             class="nav-item {{ request()->routeIs('admin.roles.*') ? 'nav-active' : '' }}">
            <span class="inline-flex items-center gap-2">
              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
              Roles
            </span>
          </a>
          <a href="{{ route('admin.permissions.index') }}"
             class="nav-item {{ request()->routeIs('admin.permissions.*') ? 'nav-active' : '' }}">
            <span class="inline-flex items-center gap-2">
              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 2l4 7H8l4-7zM2 22h20"/></svg>
              Permisos
            </span>
          </a>
        @endif

        {{-- PATIENT MENU --}}
        @if(!empty($isPatient) && $isPatient)
          <a href="{{ route('app.dashboard') }}"
             class="nav-item {{ request()->routeIs('app.dashboard') ? 'nav-active' : '' }}">
            <span class="inline-flex items-center gap-2">
              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10"/></svg>
              Mi panel
            </span>
          </a>
          <a href="{{ route('app.appointments.index') }}"
             class="nav-item {{ request()->routeIs('app.appointments.*') ? 'nav-active' : '' }}">
            <span class="inline-flex items-center gap-2">
              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>
              </svg>
              Mis citas
            </span>
          </a>
          <a href="{{ route('app.invoices.index') }}"
             class="nav-item {{ request()->routeIs('app.invoices.*') ? 'nav-active' : '' }}">
            <span class="inline-flex items-center gap-2">
              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M6 2h12v20l-3-2-3 2-3-2-3 2z"/></svg>
              Mis facturas
            </span>
          </a>
        @endif
      </nav>

      @auth
      <form method="POST" action="{{ route('logout') }}" class="p-3">
        @csrf
        <button class="w-full text-left btn btn-ghost">
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M9 21H5a2 2 0 0 1-2-2V5c0-1.1.9-2 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg>
          Cerrar sesión
        </button>
      </form>
      @endauth
    </aside>

    {{-- Main --}}
    <main class="w-full md:pl-72">
      {{-- Header sticky --}}
      <header class="sticky top-0 z-10 bg-white/95 backdrop-blur border-b px-4 py-3 flex items-center justify-between">
        <div>
          <h2 class="text-lg font-semibold leading-none">@yield('title','Dashboard')</h2>
          @auth <p class="text-[11px] text-slate-500">Hola, {{ auth()->user()->name }}</p> @endauth
        </div>
        <div class="flex items-center gap-2">
          @hasSection('header-actions')
            @yield('header-actions')
          @else
            @auth
              @if(!empty($isAdmin) && $isAdmin)
                @unless(request()->routeIs('admin.appointments.*'))
                  <a href="{{ route('admin.appointments.create') }}" class="btn btn-primary">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 5v14M5 12h14"/></svg>
                    Nueva cita
                  </a>
                @endunless
              @endif
              @if(!empty($isPatient) && $isPatient)
                @unless(request()->routeIs('app.appointments.*'))
                  <a href="{{ route('app.appointments.create') }}" class="btn btn-primary">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 5v14M5 12h14"/></svg>
                    Reservar cita
                  </a>
                @endunless
              @endif
            @endauth
          @endif
        </div>
      </header>

      <div class="p-4">
        @if (session('ok'))
          <div class="mb-3 p-2 rounded bg-green-50 text-green-700 text-sm">{{ session('ok') }}</div>
        @endif
        @if (session('warn'))
          <div class="mb-3 p-2 rounded bg-amber-50 text-amber-700 text-sm">{{ session('warn') }}</div>
        @endif
        @if ($errors->any())
          <div class="mb-3 p-2 rounded bg-rose-50 text-rose-700 text-sm">
            <ul class="list-disc ms-4">
              @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
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
