<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title','Panel') · llamaDates</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="js/app.js"></script>
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
    <aside class="hidden md:block fixed inset-y-0 left-0 w-64 bg-white border-r overflow-y-auto">
      <div class="p-4 border-b">
        <h1 class="font-semibold">llamaDates</h1>
        <p class="text-xs text-slate-500">Admin</p>
      </div>
      <nav class="p-3 space-y-1">
        <a href="{{ route('admin.dashboard') }}"
           class="nav-item {{ request()->routeIs('admin.dashboard') ? 'nav-active' : '' }}">Dashboard</a>
        <a href="{{ route('admin.appointments.index') }}"
           class="nav-item {{ request()->routeIs('admin.appointments.*') ? 'nav-active' : '' }}">Citas</a>
        <a href="{{ route('admin.patients') }}"
           class="nav-item {{ request()->routeIs('admin.patients') ? 'nav-active' : '' }}">Pacientes</a>
        <a href="{{ route('admin.dentists') }}"
           class="nav-item {{ request()->routeIs('admin.dentists') ? 'nav-active' : '' }}">Dentistas</a>
        <a href="{{ route('admin.services') }}"
           class="nav-item {{ request()->routeIs('admin.services') ? 'nav-active' : '' }}">Servicios</a>
        <a href="{{ route('admin.schedules') }}"
           class="nav-item {{ request()->routeIs('admin.schedules') ? 'nav-active' : '' }}">Horarios</a>
        <a href="{{ route('admin.billing') }}"
           class="nav-item {{ request()->routeIs('admin.billing') ? 'nav-active' : '' }}">Pagos</a>
        <a href="{{ route('admin.consents.templates') }}"
           class="nav-item {{ request()->routeIs('admin.consents.templates') ? 'nav-active' : '' }}">Plantillas de consentimiento</a>
        <a href="{{ route('admin.chairs.index') }}"
           class="nav-item {{ request()->routeIs('admin.consents.templates') ? 'nav-active' : '' }}">Sillas</a>
           
      </nav>
      <form method="POST" action="{{ route('logout') }}" class="p-3">
        @csrf
        <button class="w-full text-left btn btn-ghost">Cerrar sesión</button>
      </form>
    </aside>

    {{-- Main (con padding para no tapar el sidebar fijo) --}}
    <main class="w-full md:pl-64">
      {{-- Header sticky: muestra SIEMPRE el título de la sección --}}
      <header class="sticky top-0 z-10 bg-white/95 backdrop-blur border-b px-4 py-3 flex items-center justify-between">
        <div>
          <h2 class="text-lg font-semibold leading-none">@yield('title','Dashboard')</h2>
          <p class="text-[11px] text-slate-500">Hola, {{ auth()->user()->name }}</p>
        </div>
        <div class="flex items-center gap-2">
          @hasSection('header-actions')
            @yield('header-actions')
          @else
            @unless(request()->routeIs('admin.appointments.*'))
              <a href="{{ route('admin.appointments.create') }}" class="btn btn-primary">+ Nueva cita</a>
            @endunless
          @endif
        </div>
      </header>

      {{-- Contenido --}}
      <div class="p-4">
        @if (session('ok'))
          <div class="mb-3 p-2 rounded bg-green-50 text-green-700 text-sm">{{ session('ok') }}</div>
        @endif
        @if ($errors->any())
          <div class="mb-3 p-2 rounded bg-rose-50 text-rose-700 text-sm">
            <ul class="list-disc ms-4">
              @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
            </ul>
          </div>
        @endif

        {{-- Ya NO imprimimos el título aquí para evitar duplicados --}}
        @yield('content')
      </div>
    </main>
  </div>
  @yield('scripts')
  @stack('scripts')
</body>
</html>
