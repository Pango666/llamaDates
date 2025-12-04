@php
  $isEdit = isset($permission) && $permission->exists;
@endphp

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

<div class="grid md:grid-cols-2 gap-6 mb-6">
  {{-- Nombre (clave) --}}
  <div class="space-y-2">
    <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
      <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
      </svg>
      Nombre (Clave) <span class="text-red-500">*</span>
    </label>
    <input 
      type="text" 
      name="name" 
      value="{{ old('name', $permission->name ?? '') }}" 
      class="w-full border border-slate-300 rounded-lg px-4 py-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors font-mono text-sm"
      placeholder="Ej: patients.create, appointments.view"
      required
      {{ $isEdit ? 'readonly' : '' }}
    >
    <p class="text-xs text-slate-500">
      @if($isEdit)
        La clave del permiso no puede modificarse una vez creada.
      @else
        Identificador único del permiso. Usar formato: <code>recurso.accion</code>
      @endif
    </p>
  </div>

  {{-- Etiqueta --}}
  <div class="space-y-2">
    <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
      <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
      </svg>
      Etiqueta
    </label>
    <input 
      type="text" 
      name="label" 
      value="{{ old('label', $permission->label ?? '') }}" 
      class="w-full border border-slate-300 rounded-lg px-4 py-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
      placeholder="Ej: Crear pacientes, Ver citas"
    >
    <p class="text-xs text-slate-500">Descripción legible del permiso para mostrar en interfaces.</p>
  </div>
</div>

{{-- Ejemplos de permisos --}}
@if(!$isEdit)
  <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
    <h4 class="text-sm font-medium text-blue-800 mb-3 flex items-center gap-2">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      Ejemplos de convenciones de nombres
    </h4>
    <div class="grid grid-cols-2 gap-3 text-sm">
      <div>
        <code class="block bg-white px-3 py-2 rounded border text-blue-600 mb-1">patients.view</code>
        <span class="text-xs text-blue-700">Ver pacientes</span>
      </div>
      <div>
        <code class="block bg-white px-3 py-2 rounded border text-blue-600 mb-1">patients.create</code>
        <span class="text-xs text-blue-700">Crear pacientes</span>
      </div>
      <div>
        <code class="block bg-white px-3 py-2 rounded border text-blue-600 mb-1">appointments.manage</code>
        <span class="text-xs text-blue-700">Gestionar citas</span>
      </div>
      <div>
        <code class="block bg-white px-3 py-2 rounded border text-blue-600 mb-1">billing.view</code>
        <span class="text-xs text-blue-700">Ver pagos</span>
      </div>
    </div>
  </div>
@endif