@extends('layouts.app')
@section('title', 'Nuevo Proveedor')

@section('header-actions')
  <a href="{{ route('admin.inv.suppliers.index') }}" class="btn btn-ghost flex items-center gap-2">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
    </svg>
    Volver a Proveedores
  </a>
  <a href="{{ route('admin.inv.suppliers.index') }}" class="btn bg-slate-600 text-white hover:bg-slate-700 flex items-center gap-2 transition-colors">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
    </svg>
    Volver a Proveedores
  </a>
@endsection

@section('content')
  <div class="max-w-2xl mx-auto">
    {{-- Header --}}
    <div class="card mb-6">
      <div class="border-b border-slate-200 pb-4">
        <h1 class="text-xl font-semibold text-slate-800 flex items-center gap-2">
          <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          Nuevo Proveedor
        </h1>
        <p class="text-sm text-slate-600 mt-1">Registre un nuevo proveedor en el sistema.</p>
      </div>
    </div>

    <form method="post" action="{{ route('admin.inv.suppliers.store') }}" class="card">
      @csrf
      @include('admin.inv.suppliers.form-fields')
      
      {{-- Acciones --}}
      <div class="flex items-center gap-4 pt-6 border-t border-slate-200 md:col-span-2">
        <button type="submit" class="btn bg-blue-600 text-white hover:bg-blue-700 flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
          </svg>
          Guardar Proveedor
        </button>
        <a href="{{ route('admin.inv.suppliers.index') }}" class="btn bg-rose-600 text-white hover:bg-rose-700 flex items-center gap-2 transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
          Cancelar
        </a>
      </div>
    </form>
  </div>
@endsection