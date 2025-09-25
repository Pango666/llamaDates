@extends('layouts.app')
@section('title','Odontogramas')

@section('header-actions')
  <a href="{{ route('admin.patients.show',$patient) }}" class="btn btn-ghost">Volver</a>
@endsection

@section('content')
  <form method="post" action="{{ route('admin.odontograms.store',$patient) }}" class="card mb-4">
    @csrf
    <div class="flex items-end gap-3">
      <div>
        <label class="block text-xs text-slate-500 mb-1">Fecha</label>
        <input type="date" name="date" value="{{ now()->toDateString() }}" class="border rounded px-3 py-2">
      </div>
      <div class="flex-1">
        <label class="block text-xs text-slate-500 mb-1">Notas (opcional)</label>
        <input type="text" name="notes" class="w-full border rounded px-3 py-2">
      </div>
      <button class="btn btn-primary">+ Nuevo odontograma</button>
    </div>
  </form>

  <div class="card">
    <table class="min-w-full text-sm">
      <thead class="border-b">
        <tr class="text-left">
          <th class="px-3 py-2">Fecha</th>
          <th class="px-3 py-2">Notas</th>
          <th class="px-3 py-2 text-right">Acciones</th>
        </tr>
      </thead>
      <tbody>
        @forelse($list as $o)
          <tr class="border-b">
            <td class="px-3 py-2">{{ \Illuminate\Support\Carbon::parse($o->date)->format('Y-m-d') }}</td>
            <td class="px-3 py-2">{{ $o->notes ?: '—' }}</td>
            <td class="px-3 py-2 text-right">
              <a href="{{ route('admin.odontograms.show',$o) }}" class="btn btn-ghost">Abrir</a>
            </td>
          </tr>
        @empty
          <tr><td colspan="3" class="px-3 py-6 text-center text-slate-500">Sin odontogramas.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
@endsection
