@extends('layouts.auth')
@section('title','Iniciar sesión')
@section('subtitle','Accede con tu cuenta')

@section('content')
<form method="POST" action="{{ route('login.post') }}" class="space-y-5" id="loginForm">
  @csrf

  {{-- Encabezado con icono dental --}}
  <div class="text-center">
    <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-sky-500 to-cyan-500 text-white shadow">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" viewBox="0 0 24 24" fill="currentColor">
        <path d="M7.5 2.75c-2.623 0-4.75 2.127-4.75 4.75 0 2.81 1.027 6.551 2.48 8.86.64 1.02 1.788 1.64 3.01 1.64 1.21 0 1.79-.61 2.37-1.67.41-.76.86-1.6 1.39-1.6.53 0 .98.84 1.39 1.6.58 1.06 1.16 1.67 2.37 1.67 1.222 0 2.37-.62 3.01-1.64 1.454-2.309 2.48-6.05 2.48-8.86 0-2.623-2.127-4.75-4.75-4.75-1.43 0-2.82.65-3.75 1.77-.93-1.12-2.32-1.77-3.75-1.77z"/>
      </svg>
    </div>
    <h2 class="text-lg font-semibold text-slate-800">Iniciar sesión</h2>
    <p class="text-sm text-slate-500">Accede con tu cuenta</p>
  </div>

  {{-- Email --}}
  <div>
    <label class="mb-1 block text-sm font-medium text-slate-700">Email</label>
    <div class="relative">
      <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21.75 7.5v9a2.25 2.25 0 0 1-2.25 2.25h-15A2.25 2.25 0 0 1 2.25 16.5v-9M21.75 7.5l-9.75 6L2.25 7.5"/>
        </svg>
      </span>
      <input
        type="email"
        name="email"
        value="{{ old('email') }}"
        required
        autocomplete="username"
        class="w-full rounded-lg border border-slate-200 bg-white px-10 py-2.5 text-slate-800 placeholder-slate-400 outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-200"
        placeholder="tucorreo@dominio.com">
    </div>
    @error('email')
      <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
    @enderror
  </div>

  {{-- Password --}}
  <div>
    <label class="mb-1 block text-sm font-medium text-slate-700">Contraseña</label>
    <div class="relative">
      <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16.5 10.5V9a4.5 4.5 0 10-9 0v1.5M6.75 10.5h10.5v7.5a2.25 2.25 0 01-2.25 2.25H9a2.25 2.25 0 01-2.25-2.25v-7.5z"/>
        </svg>
      </span>
      <input
        id="passwordInput"
        type="password"
        name="password"
        required
        autocomplete="current-password"
        class="w-full rounded-lg border border-slate-200 bg-white px-10 py-2.5 pr-12 text-slate-800 placeholder-slate-400 outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-200"
        placeholder="••••••••">
      <button type="button" id="togglePwd"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600"
              aria-label="Mostrar u ocultar contraseña">
        {{-- ojo simple: cambiaremos solo el texto para no meter libs --}}
        <span class="text-xs font-medium">ver</span>
      </button>
    </div>
    @error('password')
      <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
    @enderror
  </div>

  {{-- Remember + forgot, en una sola línea sin romperse --}}
  <div class="flex items-center justify-between gap-3">
    <label class="inline-flex items-center gap-2 text-sm text-slate-600">
      <input type="checkbox" name="remember" class="h-4 w-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500">
      Recuérdame
    </label>
    <a href="{{ route('password.request') }}"
       class="shrink-0 text-sm font-medium text-sky-700 hover:underline">
       ¿Olvidaste tu contraseña?
    </a>
  </div>

  {{-- Botón --}}
  <button
    class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-sky-600 to-cyan-600 px-4 py-2.5 text-white transition hover:from-sky-700 hover:to-cyan-700 focus:outline-none focus:ring-4 focus:ring-sky-200">
    Entrar
  </button>

  {{-- Pie --}}
  <p class="text-center text-xs text-slate-500">
    Al continuar aceptas nuestros <a href="#" class="font-medium text-sky-700 hover:underline">Términos</a> y
    <a href="#" class="font-medium text-sky-700 hover:underline">Política de Privacidad</a>.
  </p>
</form>

{{-- Toggle de contraseña (ligero, sin Alpine) --}}
<script>
  (function () {
    const btn = document.getElementById('togglePwd');
    const input = document.getElementById('passwordInput');
    if (!btn || !input) return;
    btn.addEventListener('click', function () {
      const isText = input.type === 'text';
      input.type = isText ? 'password' : 'text';
      btn.firstElementChild.textContent = isText ? 'ver' : 'ocultar';
    });
  })();
</script>
@endsection