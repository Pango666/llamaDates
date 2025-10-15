@extends('layouts.app')
@section('title','Mi odontograma')

@section('header-actions')
  <a href="{{ route('app.profile',['tab'=>'historia']) }}" class="btn btn-ghost">
    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M15 19l-7-7 7-7"/></svg>
    Volver a Historia
  </a>
@endsection

@section('content')
  <div class="card">
    @if(!$odo)
      <div class="text-sm text-slate-500">No existe un odontograma registrado todavía.</div>
    @else
      <div class="mb-3">
        <div class="text-sm">
          <span class="font-medium">Creado:</span> {{ $odo->created_at?->format('Y-m-d H:i') ?? '—' }} ·
          <span class="font-medium">Actualizado:</span> {{ $odo->updated_at?->format('Y-m-d H:i') ?? '—' }}
        </div>
      </div>

      <div class="overflow-x-auto border rounded">
        <table class="min-w-full text-sm">
          <thead class="border-b bg-slate-50">
            <tr class="text-left">
              <th class="px-3 py-2">Pieza</th>
              <th class="px-3 py-2">Superficie</th>
              <th class="px-3 py-2">Estado / Marca</th>
              <th class="px-3 py-2">Notas</th>
            </tr>
          </thead>
          <tbody>
            @forelse($odo->teeth as $t)
              <tr class="border-b">
                <td class="px-3 py-2">{{ $t->tooth_code }}</td>
                <td class="px-3 py-2">{{ $t->surface ?: '—' }}</td>
                <td class="px-3 py-2">{{ $t->status ?? $t->mark ?? '—' }}</td>
                <td class="px-3 py-2">{{ $t->notes ?: '—' }}</td>
              </tr>
            @empty
              <tr><td colspan="4" class="px-3 py-4 text-center text-slate-500">Sin piezas registradas.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    @endif
  </div>
@endsection
