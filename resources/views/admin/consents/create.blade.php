{{-- resources/views/admin/consents/create.blade.php --}}
@extends('layouts.app')
@section('title','Nuevo consentimiento')

@section('header-actions')
  <a href="{{ route('admin.patients.consents.index',$patient) }}" class="btn bg-slate-600 text-white hover:bg-slate-700 flex items-center gap-2 transition-colors">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
    </svg>
    Volver
  </a>
@endsection

@section('content')
<form method="post" action="{{ route('admin.patients.consents.store', $patient) }}" class="card space-y-3">
  @csrf

  {{-- si vienes desde una cita, mantenla vinculada --}}
  @if(request('appointment_id'))
    <input type="hidden" name="appointment_id" value="{{ request('appointment_id') }}">
  @endif

  <div>
    <label class="block text-xs text-slate-500 mb-1">Título</label>
    <input name="title" class="w-full border rounded px-3 py-2" required>
  </div>

  <div>
    <label class="block text-xs text-slate-500 mb-1">Plantilla</label>
    <select name="template_id" class="w-full border rounded px-3 py-2">
      <option value="">— Ninguna —</option>
      @foreach($templates as $t)
        <option value="{{ $t->id }}">{{ $t->name }}</option>
      @endforeach
    </select>
    <p class="text-xs text-slate-500 mt-1">
      Variables: <code>{{ '{' }}{patient.full_name} }}</code>,
      <code>{{ '{' }}{patient.ci} }}</code>,
      <code>{{ '{' }}{doctor.name} }}</code>,
      <code>{{ '{' }}{today} }}</code>
    </p>
  </div>

  <div>
    <label class="block text-xs text-slate-500 mb-1">Cuerpo (opcional)</label>
    <textarea name="body" rows="14" class="w-full border rounded px-3 py-2"></textarea>
  </div>

  <label class="inline-flex items-center gap-2">
    <input type="checkbox" name="to_pdf" value="1"> <span>Generar PDF al guardar</span>
  </label>

  <button class="btn btn-primary">Guardar</button>
</form>
@endsection
