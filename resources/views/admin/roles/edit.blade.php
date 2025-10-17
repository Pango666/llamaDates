@extends('layouts.app')
@section('title', 'Editar Rol: ' . $role->name)

@section('header-actions')
  <a href="{{ route('admin.roles.index') }}" class="btn btn-ghost flex items-center gap-2">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
    </svg>
    Volver a Roles
  </a>
@endsection

@section('content')
  <div class="max-w-2xl mx-auto">
    {{-- Header --}}
    <div class="card mb-6">
      <div class="border-b border-slate-200 pb-4">
        <h1 class="text-xl font-semibold text-slate-800 flex items-center gap-2">
          <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
          </svg>
          Editar Rol
        </h1>
        <p class="text-sm text-slate-600 mt-1">Modifique la información del rol.</p>
      </div>
    </div>

    <form method="post" action="{{ route('admin.roles.update', $role) }}" class="card">
      @csrf @method('PUT')
      @include('admin.roles._form', ['role' => $role])
      
      {{-- Información del rol --}}
      <div class="bg-slate-50 border border-slate-200 rounded-lg p-4 mb-6">
        <div class="grid grid-cols-2 gap-4 text-sm">
          <div>
            <span class="font-medium text-slate-700">Creado:</span>
            <span class="text-slate-600">{{ $role->created_at->format('d/m/Y H:i') }}</span>
          </div>
          <div>
            <span class="font-medium text-slate-700">Última actualización:</span>
            <span class="text-slate-600">{{ $role->updated_at->format('d/m/Y H:i') }}</span>
          </div>
          <div class="col-span-2">
            <span class="font-medium text-slate-700">Permisos asignados:</span>
            <span class="text-slate-600">{{ $role->permissions_count }} permiso(s)</span>
          </div>
        </div>
      </div>

      {{-- Acciones --}}
      <div class="flex items-center gap-4 pt-6 border-t border-slate-200">
        <button type="submit" class="btn bg-blue-600 text-white hover:bg-blue-700 flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
          </svg>
          Actualizar Rol
        </button>
        <a href="{{ route('admin.roles.index') }}" class="btn btn-ghost flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
          Cancelar
        </a>
      </div>
    </form>
  </div>
@endsection