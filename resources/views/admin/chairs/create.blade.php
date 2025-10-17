@extends('layouts.app')
@section('title', 'Nueva Silla')

@section('header-actions')
  <a href="{{ route('admin.chairs.index') }}" class="btn btn-ghost flex items-center gap-2">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
    </svg>
    Volver a Sillas
  </a>
@endsection

@section('content')
  <div class="max-w-md mx-auto">
    {{-- Header --}}
    <div class="card mb-6">
      <div class="border-b border-slate-200 pb-4">
        <h1 class="text-xl font-semibold text-slate-800 flex items-center gap-2">
          <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          Nueva Silla Odontológica
        </h1>
        <p class="text-sm text-slate-600 mt-1">Registre una nueva silla en el sistema.</p>
      </div>
    </div>

    <form method="post" action="{{ route('admin.chairs.store') }}" class="card">
      @csrf

      {{-- Nombre --}}
      <div class="space-y-2 mb-6">
        <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
          <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
          </svg>
          Nombre de la Silla <span class="text-red-500">*</span>
        </label>
        <input 
          type="text" 
          name="name" 
          value="{{ old('name', $chair->name) }}"
          class="w-full border border-slate-300 rounded-lg px-4 py-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
          placeholder="Ej: Silla 1, Consultorio A, etc."
          required
        >
        <p class="text-xs text-slate-500">Identificador único para la silla odontológica.</p>
      </div>

      {{-- Turno --}}
      <div class="space-y-2 mb-6">
        <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
          <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          Turno de Trabajo <span class="text-red-500">*</span>
        </label>
        <select 
          name="shift" 
          class="w-full border border-slate-300 rounded-lg px-4 py-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
          required
        >
          @foreach(['mañana' => 'Mañana', 'tarde' => 'Tarde', 'completo' => 'Completo'] as $key => $label)
            <option value="{{ $key }}" @selected(old('shift', $chair->shift) === $key)>
              {{ $label }}
            </option>
          @endforeach
        </select>
        <p class="text-xs text-slate-500">Seleccione el horario de disponibilidad de la silla.</p>
      </div>

      {{-- Descripción (opcional) --}}
      <div class="space-y-2 mb-6">
        <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
          <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          Descripción (Opcional)
        </label>
        <textarea 
          name="description" 
          rows="3"
          class="w-full border border-slate-300 rounded-lg px-4 py-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
          placeholder="Información adicional sobre la silla..."
        >{{ old('description', $chair->description) }}</textarea>
        <p class="text-xs text-slate-500">Características o ubicación específica de la silla.</p>
      </div>

      {{-- Acciones --}}
      <div class="flex items-center gap-4 pt-6 border-t border-slate-200">
        <button type="submit" class="btn bg-blue-600 text-white hover:bg-blue-700 flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
          </svg>
          Guardar Silla
        </button>
        <a href="{{ route('admin.chairs.index') }}" class="btn btn-ghost flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
          Cancelar
        </a>
      </div>
    </form>
  </div>
@endsection