@extends('layouts.app')
@section('title','Nuevo plan')

@section('header-actions')
  <a href="{{ route('admin.patients.plans.index',$patient) }}" class="btn bg-slate-600 text-white hover:bg-slate-700 flex items-center gap-2 transition-colors">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
    </svg>
    Volver
</a>
@endsection

@section('content')
  <form method="post" action="{{ route('admin.patients.plans.store',$patient) }}" class="card max-w-xl">
    @csrf
    <h3 class="font-semibold mb-3">Nuevo plan para {{ $patient->last_name }}, {{ $patient->first_name }}</h3>
    <label class="block text-sm mb-1">Título</label>
    <input name="title" class="w-full border rounded px-3 py-2 mb-3" placeholder="Plan inicial" required>
    <button class="btn btn-primary">Crear</button>
  </form>
@endsection
