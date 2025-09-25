@extends('layouts.app')
@section('title','Horarios')

@section('header-actions')
  <a href="{{ route('admin.dentists') }}" class="btn btn-ghost">Ver odontólogos</a>
@endsection

@section('content')
  <form method="get" class="card mb-4">
    <div class="flex flex-col md:flex-row gap-3 md:items-end">
      <div class="flex-1">
        <label class="block text-xs text-slate-500 mb-1">Buscar</label>
        <input type="text" name="q" value="{{ $q }}" placeholder="Nombre o especialidad"
               class="w-full border rounded px-3 py-2">
      </div>
      <div>
        <button class="btn btn-ghost">Filtrar</button>
        @if($q!=='')
          <a class="btn btn-ghost" href="{{ route('admin.schedules') }}">Limpiar</a>
        @endif
      </div>
    </div>
  </form>

  <div class="card p-0 overflow-x-auto">
    <table class="min-w-full text-sm">
      <thead class="bg-white border-b">
        <tr class="text-left">
          <th class="px-3 py-2">Odontólogo</th>
          <th class="px-3 py-2">Especialidad</th>
          <th class="px-3 py-2">Días configurados</th>
          <th class="px-3 py-2">Bloques</th>
          <th class="px-3 py-2 text-right">Acciones</th>
        </tr>
      </thead>
      <tbody>
      @forelse($dentists as $d)
        @php
          $days = $daysByDentist[$d->id] ?? [];
        @endphp
        <tr class="border-b hover:bg-slate-50">
          <td class="px-3 py-2">{{ $d->name }}</td>
          <td class="px-3 py-2">{{ $d->specialty ?: '—' }}</td>
          <td class="px-3 py-2">
            @forelse($days as $dy)
              <span class="badge bg-slate-100">{{ $dayLabels[$dy] }}</span>
            @empty
              <span class="text-slate-500">Sin configurar</span>
            @endforelse
          </td>
          <td class="px-3 py-2">{{ $d->blocks_count }}</td>
          <td class="px-3 py-2">
            <div class="flex items-center justify-end">
              <a class="btn btn-ghost" href="{{ route('admin.schedules.edit',$d) }}">Configurar</a>
            </div>
          </td>
        </tr>
      @empty
        <tr><td colspan="5" class="px-3 py-6 text-center text-slate-500">Sin resultados.</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-3">{{ $dentists->links() }}</div>
@endsection
