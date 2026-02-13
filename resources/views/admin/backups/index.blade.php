@extends('layouts.app')
@section('title','Respaldos y Sistema')

@section('header-actions')
  <div class="flex gap-2">
    <form method="post" action="{{ route('admin.backups.database') }}" onsubmit="return confirm('¿Crear respaldo de base de datos?');">
      @csrf
      <button class="btn bg-blue-600 text-white hover:bg-blue-700 flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>
        </svg>
        Respaldar BD
      </button>
    </form>

    <form method="post" action="{{ route('admin.backups.files') }}" onsubmit="return confirm('¿Crear respaldo de archivos?');">
      @csrf
      <button class="btn bg-emerald-600 text-white hover:bg-emerald-700 flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
        </svg>
        Respaldar Archivos
      </button>
    </form>
  </div>
@endsection

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

  {{-- Flash Messages --}}
  @if(session('ok'))
    <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 flex items-center gap-3">
      <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      <span class="text-sm font-medium text-emerald-800">{{ session('ok') }}</span>
    </div>
  @endif
  @if(session('error'))
    <div class="bg-rose-50 border border-rose-200 rounded-xl p-4 flex items-center gap-3">
      <svg class="w-5 h-5 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      <span class="text-sm font-medium text-rose-800">{{ session('error') }}</span>
    </div>
  @endif

  {{-- Header --}}
  <div class="card">
    <div class="border-b border-slate-200 pb-4">
      <h1 class="text-xl font-semibold text-slate-800 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center">
          <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/>
          </svg>
        </div>
        Respaldos y Mantenimiento del Sistema
      </h1>
      <p class="text-sm text-slate-600 mt-2 ml-13">
        Crea respaldos de la base de datos y archivos, limpia cachés y mantén el sistema optimizado.
      </p>
    </div>
  </div>

  {{-- KPIs del Sistema --}}
  <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
    {{-- PHP --}}
    <div class="card bg-slate-50 border-slate-200">
      <div class="flex items-center gap-3">
        <div class="p-2.5 bg-slate-200 rounded-lg">
          <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
          </svg>
        </div>
        <div>
          <p class="text-xs font-semibold text-slate-500 uppercase">PHP</p>
          <p class="text-lg font-bold text-slate-900">{{ $cacheStatus['php_version'] }}</p>
        </div>
      </div>
    </div>

    {{-- Laravel --}}
    <div class="card bg-rose-50 border-rose-200">
      <div class="flex items-center gap-3">
        <div class="p-2.5 bg-rose-100 rounded-lg">
          <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
          </svg>
        </div>
        <div>
          <p class="text-xs font-semibold text-rose-600 uppercase">Laravel</p>
          <p class="text-lg font-bold text-rose-900">{{ $cacheStatus['laravel_version'] }}</p>
        </div>
      </div>
    </div>

    {{-- Disk --}}
    <div class="card bg-blue-50 border-blue-200">
      <div class="flex items-center gap-3">
        <div class="p-2.5 bg-blue-100 rounded-lg">
          <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
          </svg>
        </div>
        <div>
          <p class="text-xs font-semibold text-blue-600 uppercase">Disco Libre</p>
          <p class="text-lg font-bold text-blue-900">{{ $cacheStatus['disk_free'] }}</p>
        </div>
      </div>
    </div>

    {{-- Logs --}}
    <div class="card bg-amber-50 border-amber-200">
      <div class="flex items-center gap-3">
        <div class="p-2.5 bg-amber-100 rounded-lg">
          <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
        </div>
        <div>
          <p class="text-xs font-semibold text-amber-600 uppercase">Logs</p>
          <p class="text-lg font-bold text-amber-900">{{ $cacheStatus['logs_size'] }}</p>
        </div>
      </div>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Left: Backups Table --}}
    <div class="lg:col-span-2 space-y-6">
      <div class="card p-0 overflow-hidden">
        <div class="px-5 py-4 border-b bg-gradient-to-r from-slate-50 to-white flex items-center justify-between">
          <div class="flex items-center gap-3">
            <h3 class="font-semibold text-slate-700">Respaldos Disponibles</h3>
            <span class="px-2.5 py-1 bg-blue-100 text-blue-700 text-xs font-semibold rounded-full">
              {{ count($backups) }}
            </span>
          </div>
          <span class="text-xs text-slate-500">{{ $cacheStatus['backups_size'] }} total</span>
        </div>

        @if(count($backups) > 0)
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                  <th class="px-5 py-3 text-left font-semibold text-slate-600">Archivo</th>
                  <th class="px-5 py-3 text-left font-semibold text-slate-600">Tipo</th>
                  <th class="px-5 py-3 text-left font-semibold text-slate-600">Fecha</th>
                  <th class="px-5 py-3 text-right font-semibold text-slate-600">Tamaño</th>
                  <th class="px-5 py-3 text-right font-semibold text-slate-600">Acciones</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                @foreach($backups as $backup)
                  <tr class="hover:bg-blue-50/30 transition-colors">
                    <td class="px-5 py-3">
                      <div class="flex items-center gap-2">
                        @if($backup['type'] === 'database')
                          <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                            </svg>
                          </div>
                        @else
                          <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                            </svg>
                          </div>
                        @endif
                        <span class="font-medium text-slate-800 text-xs">{{ $backup['name'] }}</span>
                      </div>
                    </td>
                    <td class="px-5 py-3">
                      <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold
                        {{ $backup['type'] === 'database' ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' }}">
                        {{ $backup['type'] === 'database' ? 'Base de datos' : 'Archivos' }}
                      </span>
                    </td>
                    <td class="px-5 py-3 text-slate-600">{{ $backup['date'] }}</td>
                    <td class="px-5 py-3 text-right text-slate-700 font-medium">{{ $backup['size'] }}</td>
                    <td class="px-5 py-3">
                      <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('admin.backups.download', $backup['name']) }}"
                           class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium text-blue-600 hover:bg-blue-50 transition-colors"
                           title="Descargar">
                          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                          </svg>
                          Descargar
                        </a>

                        <form method="post" action="{{ route('admin.backups.delete', $backup['name']) }}"
                              onsubmit="return confirm('¿Eliminar este respaldo?');">
                          @csrf @method('DELETE')
                          <button class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium text-rose-600 hover:bg-rose-50 transition-colors"
                                  title="Eliminar">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Eliminar
                          </button>
                        </form>
                      </div>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @else
          <div class="text-center py-12">
            <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-4">
              <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2"/>
              </svg>
            </div>
            <h4 class="font-semibold text-slate-700 mb-1">No hay respaldos</h4>
            <p class="text-sm text-slate-500">Crea tu primer respaldo usando los botones de arriba.</p>
          </div>
        @endif
      </div>
    </div>

    {{-- Right: Cache & Maintenance --}}
    <div class="space-y-6">
      {{-- Cache Status --}}
      <div class="card">
        <h3 class="font-semibold text-slate-800 flex items-center gap-2 mb-4">
          <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
          </svg>
          Estado del Caché
        </h3>

        <div class="space-y-3">
          {{-- Config --}}
          <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
            <div class="flex items-center gap-2">
              <span class="w-2.5 h-2.5 rounded-full {{ $cacheStatus['config_cached'] ? 'bg-emerald-500' : 'bg-slate-300' }}"></span>
              <span class="text-sm text-slate-700">Config</span>
            </div>
            <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $cacheStatus['config_cached'] ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
              {{ $cacheStatus['config_cached'] ? 'Cacheada' : 'Sin caché' }}
            </span>
          </div>

          {{-- Routes --}}
          <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
            <div class="flex items-center gap-2">
              <span class="w-2.5 h-2.5 rounded-full {{ $cacheStatus['routes_cached'] ? 'bg-emerald-500' : 'bg-slate-300' }}"></span>
              <span class="text-sm text-slate-700">Rutas</span>
            </div>
            <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $cacheStatus['routes_cached'] ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
              {{ $cacheStatus['routes_cached'] ? 'Cacheadas' : 'Sin caché' }}
            </span>
          </div>

          {{-- Views --}}
          <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
            <div class="flex items-center gap-2">
              <span class="w-2.5 h-2.5 rounded-full {{ $cacheStatus['views_cached'] ? 'bg-amber-500' : 'bg-slate-300' }}"></span>
              <span class="text-sm text-slate-700">Vistas compiladas</span>
            </div>
            <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $cacheStatus['views_cached'] ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-500' }}">
              {{ $cacheStatus['views_cached'] ? $cacheStatus['views_count'] . ' archivos' : 'Sin caché' }}
            </span>
          </div>
        </div>
      </div>

      {{-- Maintenance Actions --}}
      <div class="card">
        <h3 class="font-semibold text-slate-800 flex items-center gap-2 mb-4">
          <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
          </svg>
          Mantenimiento
        </h3>

        <div class="space-y-2">
          {{-- Clear All Cache --}}
          <form method="post" action="{{ route('admin.backups.clear.all') }}">
            @csrf
            <button class="w-full flex items-center gap-3 p-3 rounded-lg bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 hover:from-blue-100 hover:to-indigo-100 transition-all text-left group">
              <div class="p-2 bg-blue-100 rounded-lg group-hover:bg-blue-200 transition-colors">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
              </div>
              <div>
                <div class="text-sm font-semibold text-blue-900">Limpiar Todo</div>
                <div class="text-xs text-blue-600">Config, caché, vistas y rutas</div>
              </div>
            </button>
          </form>

          {{-- Individual Clears --}}
          <div class="grid grid-cols-2 gap-2">
            <form method="post" action="{{ route('admin.backups.clear.cache') }}">
              @csrf
              <button class="w-full p-3 rounded-lg bg-slate-50 border border-slate-200 hover:bg-slate-100 text-left transition-colors">
                <div class="text-xs font-semibold text-slate-700">Caché App</div>
                <div class="text-[10px] text-slate-500">cache:clear</div>
              </button>
            </form>

            <form method="post" action="{{ route('admin.backups.clear.config') }}">
              @csrf
              <button class="w-full p-3 rounded-lg bg-slate-50 border border-slate-200 hover:bg-slate-100 text-left transition-colors">
                <div class="text-xs font-semibold text-slate-700">Config</div>
                <div class="text-[10px] text-slate-500">config:clear</div>
              </button>
            </form>

            <form method="post" action="{{ route('admin.backups.clear.views') }}">
              @csrf
              <button class="w-full p-3 rounded-lg bg-slate-50 border border-slate-200 hover:bg-slate-100 text-left transition-colors">
                <div class="text-xs font-semibold text-slate-700">Vistas</div>
                <div class="text-[10px] text-slate-500">view:clear</div>
              </button>
            </form>

            <form method="post" action="{{ route('admin.backups.clear.routes') }}">
              @csrf
              <button class="w-full p-3 rounded-lg bg-slate-50 border border-slate-200 hover:bg-slate-100 text-left transition-colors">
                <div class="text-xs font-semibold text-slate-700">Rutas</div>
                <div class="text-[10px] text-slate-500">route:clear</div>
              </button>
            </form>
          </div>

          {{-- Clear Logs --}}
          <form method="post" action="{{ route('admin.backups.clear.logs') }}" onsubmit="return confirm('¿Limpiar todos los logs? El contenido actual se perderá.');">
            @csrf
            <button class="w-full flex items-center gap-3 p-3 rounded-lg bg-amber-50 border border-amber-200 hover:bg-amber-100 transition-all text-left group">
              <div class="p-2 bg-amber-100 rounded-lg group-hover:bg-amber-200 transition-colors">
                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
              </div>
              <div>
                <div class="text-sm font-semibold text-amber-900">Limpiar Logs</div>
                <div class="text-xs text-amber-600">{{ $cacheStatus['logs_size'] }} actualmente</div>
              </div>
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
