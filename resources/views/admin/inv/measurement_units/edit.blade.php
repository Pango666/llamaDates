@extends('layouts.app')
@section('title','Editar Unidad de Medida: '.$measurementUnit->name)

@section('header-actions')
  <a href="{{ route('admin.inv.measurement_units.index') }}" class="btn bg-slate-600 text-white hover:bg-slate-700 flex items-center gap-2">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
    </svg>
    Volver a Unidades
  </a>
@endsection

@section('content')
  <div class="max-w-xl mx-auto">

    <div class="card mb-6">
      <div class="border-b border-slate-200 pb-4">
        <h1 class="text-xl font-semibold text-slate-800 flex items-center gap-2">
          <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
          </svg>
          Editar Unidad de Medida
        </h1>
      </div>
    </div>

    <form method="post" action="{{ route('admin.inv.measurement_units.update', $measurementUnit) }}" class="card">
      @csrf @method('PUT')
      @include('admin.inv.measurement_units.form-fields', ['measurementUnit' => $measurementUnit])

      <div class="flex items-center gap-4 pt-6 border-t border-slate-200">
        <button class="btn bg-blue-600 text-white hover:bg-blue-700 flex items-center gap-2">
          Guardar Cambios
        </button>
        <a href="{{ route('admin.inv.measurement_units.index') }}" class="btn btn-ghost">
          Volver
        </a>
      </div>
    </form>
  </div>
@endsection
