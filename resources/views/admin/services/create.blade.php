@extends('layouts.app')
@section('title', 'Nuevo Servicio')

@section('header-actions')
  <a href="{{ route('admin.services') }}" class="btn bg-slate-600 text-white hover:bg-slate-700 flex items-center gap-2 transition-colors">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
    </svg>
    Volver al listado
  </a>
@endsection

@section('content')
  {{-- Estilos locales para el botón slate --}}
  <style>
    .btn{display:inline-flex;align-items:center;gap:.5rem;padding:.5rem 1rem;border-radius:.5rem;
         font-weight:500;border:1px solid transparent;text-decoration:none}
    .btn-slate{background:#475569;color:#fff;border-color:#475569}
    .btn-slate:hover{background:#334155;border-color:#334155}
  </style>

  <div class="max-w-4xl mx-auto">
    <div class="card">
      <div class="border-b border-slate-200 pb-4 mb-6">
        <h1 class="text-xl font-semibold text-slate-800 flex items-center gap-2">
          <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
          </svg>
          Registrar Nuevo Servicio
        </h1>
        <p class="text-sm text-slate-600 mt-1">
          Complete la información del servicio dental. Los campos marcados con
          <span class="text-red-500">*</span> son obligatorios.
        </p>
      </div>

      <form method="post" action="{{ route('admin.services.store') }}" class="space-y-6">
        @csrf
        @include('admin.services._form', ['service' => $service])
      </form>
    </div>

    {{-- Información de ayuda --}}
    <div class="mt-6 grid gap-4 md:grid-cols-2">
      <div class="card bg-blue-50 border-blue-200">
        <div class="flex items-start gap-3">
          <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <div>
            <h3 class="font-medium text-blue-800">Información importante</h3>
            <ul class="text-sm text-blue-700 mt-2 space-y-1">
              <li>• La duración debe ser en múltiplos de 5 minutos</li>
              <li>• El precio se usará para facturación automática</li>
              <li>• Los servicios inactivos no estarán disponibles para citas</li>
            </ul>
          </div>
        </div>
      </div>

      <div class="card bg-amber-50 border-amber-200">
        <div class="flex items-start gap-3">
          <svg class="w-5 h-5 text-amber-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
          </svg>
          <div>
            <h3 class="font-medium text-amber-800">Recomendaciones</h3>
            <ul class="text-sm text-amber-700 mt-2 space-y-1">
              <li>• Use nombres descriptivos para los servicios</li>
              <li>• Considere el tiempo real de procedimiento</li>
              <li>• Mantenga actualizados los precios</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
