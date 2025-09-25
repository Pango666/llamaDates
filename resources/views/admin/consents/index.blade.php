@extends('layouts.app')
@section('title','Consentimientos')

@section('header-actions')
  <a href="{{ route('admin.patients.show',$patient) }}" class="btn btn-ghost">Volver</a>
  <a href="{{ route('admin.patients.consents.create',$patient) }}" class="btn btn-primary">+ Nuevo consentimiento</a>
@endsection

@section('content')
<div class="card">
  <h3 class="font-semibold mb-2">Consentimientos de {{ $patient->last_name }}, {{ $patient->first_name }}</h3>
  <div class="overflow-x-auto">
    <table class="min-w-full text-sm">
      <thead class="border-b">
        <tr class="text-left">
          <th class="px-3 py-2">Título</th>
          <th class="px-3 py-2">Estado</th>
          <th class="px-3 py-2">Firmado</th>
          <th class="px-3 py-2 text-right">Acciones</th>
        </tr>
      </thead>
      <tbody>
        @forelse($consents as $c)
          <tr class="border-b">
            <td class="px-3 py-2"><a class="hover:underline" href="{{ route('admin.consents.show',$c) }}">{{ $c->title }}</a></td>
            <td class="px-3 py-2">
              @if($c->signed_at)
                <span class="badge bg-emerald-100 text-emerald-700">Firmado</span>
              @else
                <span class="badge bg-amber-100 text-amber-700">Pendiente</span>
              @endif
            </td>
            <td class="px-3 py-2">{{ $c->signed_at ? $c->signed_at->format('Y-m-d H:i') : '—' }}</td>
            <td class="px-3 py-2">
              <div class="flex justify-end gap-2">
                <a href="{{ route('admin.consents.print',$c) }}" class="btn btn-ghost">Imprimir</a>
                <a href="{{ route('admin.consents.pdf',$c) }}" class="btn btn-ghost">PDF</a>
                <a href="{{ route('admin.consents.show',$c) }}" class="btn btn-ghost">Ver</a>
                <form method="post" action="{{ route('admin.consents.destroy',$c) }}" onsubmit="return confirm('Eliminar?')">
                  @csrf @method('DELETE')
                  <button class="btn btn-danger">Eliminar</button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="4" class="px-3 py-6 text-center text-slate-500">Sin consentimientos.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="mt-3">{{ $consents->links() }}</div>
</div>
@endsection
