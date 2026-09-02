<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title','Acceso') · CEOT DATES</title>
  <link rel="icon" href="{{ asset('images/logo.png') }}">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Inter', sans-serif; }
  </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-800 to-slate-900 flex items-center justify-center px-4 py-8">

  <div class="w-full max-w-md">

    {{-- Card --}}
    <div class="bg-white rounded-2xl shadow-xl p-8">

      {{-- Logo + Nombre --}}
      <div class="text-center mb-7">
        <div class="w-16 h-16 bg-slate-800 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
          <img src="{{ asset('images/logo.png') }}" alt="CEOT" class="w-9 h-9 object-contain brightness-0 invert"
               onerror="this.style.display='none'">
        </div>
        <h1 class="text-2xl font-bold text-slate-800">CEOT DATES</h1>
        <p class="text-slate-500 text-sm mt-1">Gestión dental inteligente</p>
      </div>

      {{-- Alerts --}}
      @if ($errors->any())
        <div class="mb-5 p-3 rounded-lg bg-red-50 border border-red-200 text-sm text-red-700 flex items-center gap-2">
          <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          {{ $errors->first() }}
        </div>
      @endif

      @if (session('status') || session('ok'))
        <div class="mb-5 p-3 rounded-lg bg-green-50 border border-green-200 text-sm text-green-700 flex items-center gap-2">
          <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          {{ session('status') ?? session('ok') }}
        </div>
      @endif

      {{-- Dynamic Content --}}
      @yield('content')
    </div>

    {{-- Footer --}}
    <p class="text-center text-xs text-slate-400 mt-6">&copy; {{ date('Y') }} CEOT DATES · Todos los derechos reservados</p>
  </div>
</body>
</html>