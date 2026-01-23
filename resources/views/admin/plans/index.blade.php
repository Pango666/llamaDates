@extends('layouts.app')
@section('title','Planes de tratamiento')

@section('header-actions')
  <a href="{{ route('admin.patients.show',$patient) }}" class="btn bg-slate-600 text-white hover:bg-slate-700 flex items-center gap-2 transition-colors">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
    </svg>
    Volver
</a>
  @if(auth()->user()->hasAnyPermission(['treatment_plans.manage', 'patient_plans.create']))
  <a href="{{ route('admin.patients.plans.create',$patient) }}" class="btn btn-primary">+ Nuevo plan</a>
  @endif
@endsection

@section('content')
  <div class="card">
    <h3 class="font-semibold mb-3">Planes de {{ $patient->last_name }}, {{ $patient->first_name }}</h3>

    @if($plans->isEmpty())
      <p class="text-sm text-slate-500">Sin planes aún.</p>
    @else
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="border-b">
            <tr class="text-left">
              <th class="px-3 py-2">Título</th>
              <th class="px-3 py-2">Estado</th>
              <th class="px-3 py-2">Total</th>
              <th class="px-3 py-2">Creado</th>
              <th class="px-3 py-2 text-right">Acciones</th>
            </tr>
          </thead>
          <tbody>
            @foreach($plans as $pl)
              @php
                $badge = ['draft'=>'bg-slate-100 text-slate-700','approved'=>'bg-emerald-100 text-emerald-700','in_progress'=>'bg-blue-100 text-blue-700'][$pl->status] ?? 'bg-slate-100';
              @endphp
              <tr class="border-b">
                <td class="px-3 py-2 font-medium">
                  <a href="{{ route('admin.plans.edit',$pl) }}" class="hover:underline">{{ $pl->title }}</a>
                </td>
                <td class="px-3 py-2"><span class="badge {{ $badge }}">{{ str_replace('_',' ',$pl->status) }}</span></td>
                <td class="px-3 py-2">Bs {{ number_format($pl->estimate_total,2) }}</td>
                <td class="px-3 py-2">{{ $pl->created_at?->format('Y-m-d') }}</td>
                <td class="px-3 py-2">
                  <div class="flex justify-end gap-2">
                    <a href="{{ route('admin.plans.edit',$pl) }}" class="btn btn-ghost">Abrir</a>
                    <form method="post" action="{{ route('admin.plans.destroy',$pl) }}" onsubmit="return confirm('¿Eliminar plan?');">
                      @csrf @method('DELETE')
                      <button class="btn btn-danger">Eliminar</button>
                    </form>
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      <div class="mt-3">{{ $plans->links() }}</div>
    @endif
  </div>
@endsection
