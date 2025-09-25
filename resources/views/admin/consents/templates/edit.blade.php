{{-- resources/views/admin/consents/templates/edit.blade.php --}}
@extends('layouts.app')
@section('title','Editar plantilla')

@section('header-actions')
  <a href="{{ route('admin.consents.templates') }}" class="btn btn-ghost">Volver</a>
@endsection

@section('content')
  <form method="post" action="{{ route('admin.consents.templates.update',$template) }}" class="card space-y-3">
    @csrf @method('PUT')

    @if ($errors->any())
      <div class="p-2 rounded bg-rose-50 text-rose-700 text-sm">
        <ul class="list-disc ml-5">
          @foreach ($errors->all() as $err) <li>{{ $err }}</li> @endforeach
        </ul>
      </div>
    @endif

    <div>
      <label class="block text-xs text-slate-500 mb-1">Nombre</label>
      <input name="name" value="{{ old('name', $template->name) }}" class="w-full border rounded px-3 py-2" required>
    </div>

    <div>
      <label class="block text-xs text-slate-500 mb-1">
        Cuerpo
        <span class="text-slate-400">
          (variables: {{ '{' }}{patient.full_name} }}, {{ '{' }}{patient.ci} }}, {{ '{' }}{doctor.name} }}, {{ '{' }}{today} }})
        </span>
      </label>
      <textarea name="body" rows="14" class="w-full border rounded px-3 py-2" required>{{ old('body', $template->body) }}</textarea>
    </div>

    <button class="btn btn-primary">Guardar cambios</button>
  </form>
@endsection
