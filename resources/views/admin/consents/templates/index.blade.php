@extends('layouts.app')
@section('title','Plantillas de consentimiento')

@section('header-actions')
  <a href="{{ route('admin.consents.templates.create') }}" class="btn btn-primary">+ Nueva plantilla</a>
@endsection

@section('content')
<div class="card">
  <table class="min-w-full text-sm">
    <thead class="border-b"><tr><th class="px-3 py-2 text-left">Nombre</th><th class="px-3 py-2 text-right">Acciones</th></tr></thead>
    <tbody>
      @forelse($templates as $t)
        <tr class="border-b">
          <td class="px-3 py-2">{{ $t->name }}</td>
          <td class="px-3 py-2 text-right">
            <a href="{{ route('admin.consents.templates.edit',$t) }}" class="btn btn-ghost">Editar</a>
            <form method="post" action="{{ route('admin.consents.templates.destroy',$t) }}" class="inline" onsubmit="return confirm('Eliminar?')">
              @csrf @method('DELETE')
              <button class="btn btn-danger">Eliminar</button>
            </form>
          </td>
        </tr>
      @empty
        <tr><td colspan="2" class="px-3 py-6 text-center text-slate-500">Sin plantillas.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection
