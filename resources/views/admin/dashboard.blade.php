@extends('layouts.app')
@section('title','Dashboard')

@section('content')
  {{-- KPIs --}}
  <div class="grid gap-4 md:grid-cols-4 mb-6">
    <div class="card"><div class="text-xs text-slate-500">Pacientes</div><div class="text-2xl font-semibold">{{ $stats['patients'] }}</div></div>
    <div class="card"><div class="text-xs text-slate-500">Odontólogos</div><div class="text-2xl font-semibold">{{ $stats['dentists'] }}</div></div>
    <div class="card"><div class="text-xs text-slate-500">Servicios</div><div class="text-2xl font-semibold">{{ $stats['services'] }}</div></div>
    <div class="card"><div class="text-xs text-slate-500">Citas hoy</div><div class="text-2xl font-semibold">{{ $stats['todayVisits'] }}</div></div>
  </div>

  <div class="grid gap-6 lg:grid-cols-3">
    {{-- Calendario --}}
    <section class="card lg:col-span-2" id="calendar-wrap">
      @include('admin.partials._calendar', ['month'=>$month,'day'=>$day,'perDay'=>$perDay])
    </section>

    {{-- Lista del día --}}
    <aside class="card" id="daylist-wrap">
      @include('admin.partials._day_list', ['day'=>$day,'appointments'=>$appointments])
    </aside>
  </div>

  {{-- Metadatos para JS --}}
  <template id="dash-meta"
    data-url="{{ route('admin.dashboard.data') }}"
    data-month="{{ $month->format('Y-m') }}"
    data-day="{{ $day->toDateString() }}"></template>
@endsection
