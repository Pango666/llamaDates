{{-- create --}}
@extends('layouts.app')
@section('title','Nueva Categoría')

@section('header-actions')
  <a href="{{ route('admin.inv.product_categories.index') }}" class="btn bg-slate-600 text-white hover:bg-slate-700 flex items-center gap-2">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
    </svg>
    Volver a Categorías
  </a>
@endsection

@section('content')
  <div class="max-w-xl mx-auto">
    <div class="card mb-6">
      <div class="border-b border-slate-200 pb-4">
        <h1 class="text-xl font-semibold text-slate-800 flex items-center gap-2">
          <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          Nueva Categoría
        </h1>
      </div>
    </div>

    <form method="post" action="{{ route('admin.inv.product_categories.store') }}" class="card">
      @csrf
      @include('admin.inv.product_categories.form-fields')

      <div class="flex items-center gap-4 pt-6 border-t border-slate-200">
        <button class="btn bg-blue-600 text-white hover:bg-blue-700 flex items-center gap-2">
          Guardar Categoría
        </button>
        <a href="{{ route('admin.inv.product_categories.index') }}" class="btn btn-ghost">
          Cancelar
        </a>
      </div>
    </form>
  </div>
@endsection
