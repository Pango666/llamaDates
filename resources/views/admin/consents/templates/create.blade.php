@extends('layouts.app')
@section('title', 'Nueva Plantilla de Consentimiento')

@section('header-actions')
  <a href="{{ route('admin.consents.templates') }}" class="btn bg-slate-600 text-white hover:bg-slate-700 flex items-center gap-2 transition-colors">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
    </svg>
    Volver a Plantillas
  </a>
@endsection

@section('content')
  <div class="max-w-4xl mx-auto">
    {{-- Header --}}
    <div class="card mb-6">
      <div class="border-b border-slate-200 pb-4">
        <h1 class="text-xl font-semibold text-slate-800 flex items-center gap-2">
          <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          Crear Nueva Plantilla
        </h1>
        <p class="text-sm text-slate-600 mt-1">Complete los detalles de la nueva plantilla de consentimiento.</p>
      </div>
    </div>

    <form method="post" action="{{ route('admin.consents.templates.store') }}" class="card">
      @csrf

      {{-- Alertas de error --}}
      @if ($errors->any())
        <div class="mb-6 p-4 rounded-lg bg-red-50 border border-red-200 flex items-start gap-3">
          <svg class="w-5 h-5 text-red-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <div class="text-sm text-red-700">
            <strong class="font-medium">Por favor, corrige los siguientes errores:</strong>
            <ul class="list-disc ms-4 mt-1 space-y-1">
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        </div>
      @endif

      {{-- Nombre --}}
      <div class="space-y-2 mb-6">
        <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
          <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
          </svg>
          Nombre de la Plantilla <span class="text-red-500">*</span>
        </label>
        <input 
          type="text" 
          name="name" 
          value="{{ old('name') }}" 
          class="w-full border border-slate-300 rounded-lg px-4 py-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
          placeholder="Ej: Consentimiento para Ortodoncia"
          required
        >
        <p class="text-xs text-slate-500">Un nombre descriptivo para identificar la plantilla.</p>
      </div>

      {{-- Cuerpo --}}
      <div class="space-y-2 mb-6">
        <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
          <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
          Contenido de la Plantilla <span class="text-red-500">*</span>
        </label>
        
        {{-- Variables disponibles --}}
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-3">
          <h4 class="text-sm font-medium text-blue-800 mb-2 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Variables disponibles
          </h4>
          <div class="grid grid-cols-2 gap-2 text-sm">
            <code class="bg-white px-2 py-1 rounded border text-blue-600">{"{patient.full_name}"}</code>
            <code class="bg-white px-2 py-1 rounded border text-blue-600">{"{patient.ci}"}</code>
            <code class="bg-white px-2 py-1 rounded border text-blue-600">{"{doctor.name}"}</code>
            <code class="bg-white px-2 py-1 rounded border text-blue-600">{"{today}"}</code>
          </div>
          <p class="text-xs text-blue-600 mt-2">Estas variables se reemplazarán automáticamente al generar el consentimiento.</p>
        </div>

        <textarea 
          name="body" 
          rows="16" 
          class="w-full border border-slate-300 rounded-lg px-4 py-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors font-mono text-sm"
          placeholder="Escriba aquí el contenido del consentimiento..."
          required
        >{{ old('body') }}</textarea>
        <p class="text-xs text-slate-500">Utilice las variables entre llaves para información dinámica.</p>
      </div>

      {{-- Acciones --}}
      <div class="flex items-center gap-4 pt-6 border-t border-slate-200">
        <button type="submit" class="btn bg-blue-600 text-white hover:bg-blue-700 flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
          </svg>
          Guardar Plantilla
        </button>
        <a href="{{ route('admin.consents.templates') }}" class="btn bg-rose-600 text-white hover:bg-rose-700 flex items-center gap-2 transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
          Cancelar
        </a>
      </div>
    </form>
  </div>
@endsection