@extends('layouts.app')
@section('title', 'Nuevo Producto')

@section('header-actions')
  <a href="{{ route('admin.inv.products.index') }}" class="btn btn-ghost flex items-center gap-2">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
    </svg>
    Volver a Productos
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
          Nuevo Producto
        </h1>
        <p class="text-sm text-slate-600 mt-1">Registre un nuevo producto en el inventario.</p>
      </div>
    </div>

    <form method="post" action="{{ route('admin.inv.products.store') }}" class="card">
      @csrf
      @include('admin.inv.products.form-fields')
      
      {{-- Acciones --}}
      <div class="flex items-center gap-4 pt-6 border-t border-slate-200 md:col-span-3">
        <button type="submit" class="btn bg-blue-600 text-white hover:bg-blue-700 flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
          </svg>
          Guardar Producto
        </button>
        <a href="{{ route('admin.inv.products.index') }}" class="btn btn-ghost flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
          Cancelar
        </a>
      </div>
    </form>
  </div>
@endsection