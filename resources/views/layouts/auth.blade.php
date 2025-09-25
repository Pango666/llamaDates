<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title','Acceso') · DetalCare</title>

  {{-- Tailwind por CDN (sin Vite) --}}
  <script src="https://cdn.tailwindcss.com"></script>

  {{-- Tu JS propio sin Vite --}}
  <script defer src="{{ asset('js/app.js') }}"></script>
</head>
<body class="min-h-screen bg-slate-100 flex items-center justify-center">
  <div class="w-full max-w-md bg-white rounded-2xl shadow p-6">
    <div class="text-center mb-4">
      <h1 class="text-xl font-semibold">DentalCare</h1>
      <p class="text-slate-500 text-sm">@yield('subtitle')</p>
    </div>

    @if (session('ok'))
      <div class="mb-3 p-2 rounded bg-green-50 text-green-700 text-sm">
        {{ session('ok') }}
      </div>
    @endif

    @if ($errors->any())
      <div class="mb-3 p-2 rounded bg-red-50 text-red-700 text-sm">
        <ul class="list-disc ms-4">
          @foreach ($errors->all() as $e)
            <li>{{ $e }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    @yield('content')
  </div>
</body>
</html>
