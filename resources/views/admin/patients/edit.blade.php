@extends('layouts.app')
@section('title','Editar paciente')

@section('header-actions')
  <a href="{{ route('admin.patients.show',$patient) }}" class="btn bg-slate-600 text-white hover:bg-slate-700 flex items-center gap-2 transition-colors">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
    </svg>
    Volver al listado
</a>
@endsection

@section('content')
  <form method="post" action="{{ route('admin.patients.update',$patient) }}" class="card">
    @csrf @method('PUT')
    @include('admin.patients._form', ['patient'=>$patient])
  </form>
  {{-- === Acceso al portal del paciente === --}}
<section class="card mt-6">
  <div class="flex items-center justify-between mb-3">
    <h3 class="font-semibold">Acceso al portal</h3>
    @if($patient->user)
      <span class="badge {{ $patient->user->status==='active'
        ? 'bg-emerald-100 text-emerald-700'
        : 'bg-slate-100 text-slate-600' }}">
        {{ $patient->user->status==='active' ? 'ACTIVO' : 'SUSPENDIDO' }}
      </span>
    @else
      <span class="badge bg-amber-100 text-amber-700">Sin usuario</span>
    @endif
  </div>

  @if($patient->user)
    <div class="grid gap-3 md:grid-cols-2">
      <div>
        <div class="text-xs text-slate-500">Email de acceso</div>
        <div class="font-medium">{{ $patient->user->email }}</div>
      </div>
      <div>
        <div class="text-xs text-slate-500">Estado</div>
        <div class="font-medium">{{ ucfirst($patient->user->status) }}</div>
      </div>
    </div>

    <div class="flex flex-wrap gap-2 mt-4">
      {{-- Activar/Suspender --}}
      <form method="post" action="{{ route('admin.patients.update',$patient) }}">
        @csrf @method('PUT')
        <input type="hidden" name="portal_action" value="{{ $patient->user->status==='active' ? 'disable' : 'enable' }}">
        <button class="btn {{ $patient->user->status==='active' ? 'btn-danger' : 'btn-primary' }}">
          {{ $patient->user->status==='active' ? 'Suspender acceso' : 'Activar acceso' }}
        </button>
      </form>
    </div>
  @else
    {{-- Crear usuario de portal --}}
    <form method="post" action="{{ route('admin.patients.update',$patient) }}" class="grid gap-3 md:grid-cols-3 mt-2">
      @csrf @method('PUT')
      <input type="hidden" name="portal_action" value="create">

      <div class="md:col-span-2">
        <label class="block text-xs text-slate-500 mb-1">Email de portal</label>
        <input name="portal_email" type="email"
               value="{{ old('portal_email',$patient->email) }}"
               class="w-full border rounded px-3 py-2" placeholder="correo@ejemplo.com" required>
      </div>
      <div>
        <label class="block text-xs text-slate-500 mb-1">Contraseña inicial (opcional)</label>
        <input name="portal_password" type="text" class="w-full border rounded px-3 py-2"
               placeholder="(Deja vacío para generar)">
      </div>

      <div class="md:col-span-3">
        <button class="btn btn-primary">Crear usuario de portal</button>
      </div>
    </form>
  @endif
</section>

@if(session('portal_password'))
  <div class="mt-3 p-2 rounded bg-amber-50 text-amber-700 text-sm">
    Contraseña: <b>{{ session('portal_password') }}</b>
  </div>
@endif
@endsection
