@extends('layouts.app')
@section('title', 'Editar Servicio - ' . $service->name)

@section('header-actions')
  <a href="{{ route('admin.services') }}"
     class="btn btn-slate flex items-center gap-2 transition-colors">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
    </svg>
    Volver al listado
  </a>
@endsection

@section('content')
  {{-- Estilos locales del botón slate --}}
  <style>
    .btn{display:inline-flex;align-items:center;gap:.5rem;padding:.5rem 1rem;border-radius:.5rem;
         font-weight:500;border:1px solid transparent;text-decoration:none}
    .btn-slate{background:#475569;color:#fff;border-color:#475569}
    .btn-slate:hover{background:#334155;border-color:#334155}
  </style>

  <div class="max-w-4xl mx-auto">
    <div class="card">
      <div class="border-b border-slate-200 pb-4 mb-6">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-xl font-semibold text-slate-800 flex items-center gap-2">
              <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
              </svg>
              Editar Servicio
            </h1>
            <p class="text-sm text-slate-600 mt-1">Modifique la información del servicio dental</p>
          </div>
          <div class="flex items-center gap-2 text-sm text-slate-500">
            <span class="bg-slate-100 px-2 py-1 rounded">ID: {{ $service->id }}</span>
            <span class="px-2 py-1 rounded
                        {{ $service->active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
              {{ $service->active ? 'Activo' : 'Inactivo' }}
            </span>
          </div>
        </div>
      </div>

      <form method="post" action="{{ route('admin.services.update', $service) }}" class="space-y-6">
        @csrf
        @method('PUT')
        @include('admin.services._form', ['service' => $service])
      </form>
    </div>

    {{-- Información adicional --}}
    <div class="mt-6 grid gap-4">
      <div class="card bg-slate-50 border-slate-200">
        <div class="flex items-start gap-3">
          <svg class="w-5 h-5 text-slate-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <div class="flex-1">
            <h3 class="font-medium text-slate-800">Información del servicio</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-3 text-sm">
              <div>
                <span class="text-slate-500">Creado:</span>
                <p class="font-medium">{{ $service->created_at->format('d/m/Y') }}</p>
              </div>
              <div>
                <span class="text-slate-500">Actualizado:</span>
                <p class="font-medium">{{ $service->updated_at->format('d/m/Y') }}</p>
              </div>
              <div>
                <span class="text-slate-500">Duración:</span>
                <p class="font-medium">{{ $service->duration_min }} min</p>
              </div>
              <div>
                <span class="text-slate-500">Precio:</span>
                <p class="font-medium">Bs {{ number_format($service->price, 2) }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      {{-- Advertencias --}}
      <div class="card bg-amber-50 border-amber-200">
        <div class="flex items-start gap-3">
          <svg class="w-5 h-5 text-amber-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
          </svg>
          <div>
            <h3 class="font-medium text-amber-800">Consideraciones importantes</h3>
            <ul class="text-sm text-amber-700 mt-2 space-y-1">
              <li>• Los cambios en duración afectarán las citas futuras</li>
              <li>• El precio nuevo no afecta citas ya facturadas</li>
              <li>• Desactivar impide nuevas asignaciones</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
