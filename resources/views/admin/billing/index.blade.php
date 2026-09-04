@extends('layouts.app')
@section('title', 'Gestión de Pagos y Recibos')

@section('header-actions')
  <div class="flex gap-2">
    @if(auth()->user()->role === 'admin')
      <a href="{{ route('admin.billing.pdf', request()->query()) }}" target="_blank"
        class="btn bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        Exportar Reporte
      </a>
    @endif
    <a href="{{ route('admin.billing.create') }}"
      class="btn bg-blue-600 text-white hover:bg-blue-700 flex items-center gap-2">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
      </svg>
      Nuevo Recibo
    </a>
  </div>
@endsection

@section('content')
  <div class="max-w-7xl mx-auto">
    {{-- Header --}}
    <div class="card mb-6">
      <div class="border-b border-slate-200 pb-4">
        <h1 class="text-xl font-semibold text-slate-800 flex items-center gap-2">
          <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" />
          </svg>
          Gestión de Pagos y Recibos
        </h1>
        <p class="text-sm text-slate-600 mt-1">Administre los recibos y pagos de los pacientes.</p>
      </div>
    </div>

    {{-- Métricas monetarias (Movido arriba) --}}
    @if(auth()->user()->role === 'admin')
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        {{-- Total Cobrado (histórico) --}}
        <div class="card bg-gradient-to-br from-emerald-50 to-emerald-100 border-emerald-200">
          <div class="flex items-center gap-3">
            <div class="p-3 bg-emerald-200/60 rounded-xl">
              <svg class="w-7 h-7 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div>
              <p class="text-xs font-semibold text-emerald-700 uppercase tracking-wider">Total Cobrado</p>
              <p class="text-2xl font-bold text-emerald-900">Bs {{ number_format($totalCollected, 2) }}</p>
              <p class="text-xs text-emerald-600 mt-0.5">Recaudación histórica</p>
            </div>
          </div>
        </div>

        {{-- Saldos Pendientes --}}
        <div class="card bg-gradient-to-br from-amber-50 to-amber-100 border-amber-200">
          <div class="flex items-center gap-3">
            <div class="p-3 bg-amber-200/60 rounded-xl">
              <svg class="w-7 h-7 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div>
              <p class="text-xs font-semibold text-amber-700 uppercase tracking-wider">Saldos Pendientes</p>
              <p class="text-2xl font-bold text-amber-900">Bs {{ number_format($totalBalances, 2) }}</p>
              <p class="text-xs text-amber-600 mt-0.5">Por cobrar en recibos abiertos</p>
            </div>
          </div>
        </div>

        {{-- Cobrado Hoy --}}
        <div class="card bg-gradient-to-br from-blue-50 to-blue-100 border-blue-200">
          <div class="flex items-center gap-3">
            <div class="p-3 bg-blue-200/60 rounded-xl">
              <svg class="w-7 h-7 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
            </div>
            <div>
              <p class="text-xs font-semibold text-blue-700 uppercase tracking-wider">Cobrado Hoy</p>
              <p class="text-2xl font-bold text-blue-900">Bs {{ number_format($todayCollected, 2) }}</p>
              <p class="text-xs text-blue-600 mt-0.5">{{ now()->format('d/m/Y') }}</p>
            </div>
          </div>
        </div>
      </div>
    @endif

    {{-- Estadísticas de conteo --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
      <div class="card bg-blue-50 border-blue-200">
        <div class="flex items-center gap-3">
          <div class="p-2 bg-blue-100 rounded-lg">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
          </div>
          <div>
            <p class="text-sm font-medium text-blue-800">Total Recibos</p>
            <p class="text-2xl font-bold text-blue-900">{{ $invoices->total() }}</p>
          </div>
        </div>
      </div>

      <div class="card bg-emerald-50 border-emerald-200">
        <div class="flex items-center gap-3">
          <div class="p-2 bg-emerald-100 rounded-lg">
            <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div>
            <p class="text-sm font-medium text-emerald-800">Pagadas</p>
            <p class="text-2xl font-bold text-emerald-900">
              {{ $invoices->where('status', 'paid')->count() }}
            </p>
          </div>
        </div>
      </div>

      <div class="card bg-amber-50 border-amber-200">
        <div class="flex items-center gap-3">
          <div class="p-2 bg-amber-100 rounded-lg">
            <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div>
            <p class="text-sm font-medium text-amber-800">Pendientes</p>
            <p class="text-2xl font-bold text-amber-900">
              {{ $invoices->whereIn('status', ['draft', 'issued'])->count() }}
            </p>
          </div>
        </div>
      </div>

      <div class="card bg-rose-50 border-rose-200">
        <div class="flex items-center gap-3">
          <div class="p-2 bg-rose-100 rounded-lg">
            <svg class="w-6 h-6 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </div>
          <div>
            <p class="text-sm font-medium text-rose-800">Anuladas</p>
            <p class="text-2xl font-bold text-rose-900">
              {{ $invoices->where('status', 'canceled')->count() }}
            </p>
          </div>
        </div>
      </div>
    </div>

    {{-- Charts (Admin) --}}
    @if(auth()->user()->role === 'admin' && isset($chart1Labels))
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="card bg-white p-4">
          <h3 class="text-sm font-semibold text-slate-700 mb-4">Tratamientos vs Recaudación (Mensual)</h3>
          <div class="h-64">
            <canvas id="monthlyChart"></canvas>
          </div>
        </div>
        <div class="card bg-white p-4">
          <h3 class="text-sm font-semibold text-slate-700 mb-4">Ingresos por Tratamiento (Proporcional)</h3>
          <div class="h-64">
            <canvas id="treatmentChart"></canvas>
          </div>
        </div>
      </div>
      <script>
        document.addEventListener('DOMContentLoaded', () => {
          try {
            // Chart 1: Monthly Bar Chart
            new Chart(document.getElementById('monthlyChart'), {
              type: 'bar',
              data: {
                labels: {!! json_encode($chart1Labels) !!},
                datasets: [
                  {
                    label: 'Total Ventas (Bs)',
                    data: {!! json_encode($chart1Invoiced) !!},
                    backgroundColor: '#93c5fd', // blue-300
                    borderRadius: 4
                  },
                  {
                    label: 'Dinero Recaudado (Bs)',
                    data: {!! json_encode($chart1Collected) !!},
                    backgroundColor: '#34d399', // emerald-400
                    borderRadius: 4
                  }
                ]
              },
              options: {
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true } },
                plugins: { legend: { position: 'top' } },
                interaction: {
                  mode: 'index',
                  intersect: false,
                }
              }
            });

            // Chart 2: Revenue Percentage by Treatment
            new Chart(document.getElementById('treatmentChart'), {
              type: 'bar',
              data: {
                labels: {!! json_encode($chart2Labels) !!},
                datasets: [{
                  label: 'Ingresos Recaudados (Bs)',
                  data: {!! json_encode($chart2Data) !!},
                  backgroundColor: [
                    '#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#06b6d4', '#f43f5e', '#64748b'
                  ],
                  borderRadius: 4
                }]
              },
              options: {
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true } },
                plugins: { legend: { display: false } }
              }
            });
          } catch (e) { console.error(e); }
        });
      </script>
    @endif

    {{-- Filtros --}}
    <form method="get" class="card mb-6">
      <div class="grid gap-4 md:grid-cols-6 md:items-end">
        {{-- Búsqueda --}}
        <div class="md:col-span-2 space-y-2">
          <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            Buscar
          </label>
          <input type="text" name="q" value="{{ $q }}" placeholder="Número de recibo o paciente..."
            class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors">
        </div>

        {{-- Estado --}}
        <div class="space-y-2">
          <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Estado
          </label>
          <select name="status"
            class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors">
            @foreach(['all' => 'Todos', 'draft' => 'Borrador', 'issued' => 'Pendiente', 'paid' => 'Pagada', 'canceled' => 'Anulada'] as $k => $lbl)
              <option value="{{ $k }}" @selected($status === $k)>{{ $lbl }}</option>
            @endforeach
          </select>
        </div>

        {{-- Cobrador (Solo Admin) --}}
        @if(auth()->user()->role === 'admin')
          <div class="space-y-2">
            <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
              <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
              Cobrador
            </label>
            <select name="cobrador"
              class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors">
              <option value="">Todos</option>
              @foreach($collectors as $collector)
                <option value="{{ $collector->id }}" @selected($cobradorId == $collector->id)>{{ $collector->name }}</option>
              @endforeach
            </select>
          </div>
        @endif

        {{-- Fecha Desde --}}
        <div class="space-y-2">
          <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            Desde
          </label>
          <input type="date" name="from" value="{{ $from }}"
            class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors">
        </div>

        {{-- Fecha Hasta --}}
        <div class="space-y-2">
          <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            Hasta
          </label>
          <input type="date" name="to" value="{{ $to }}"
            class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors">
        </div>

        {{-- Botones de acción --}}
        <div class="flex gap-2 md:col-span-6">
          <button type="submit" class="btn bg-blue-600 text-white hover:bg-blue-700 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            Aplicar Filtros
          </button>

          @if($q !== '' || $status !== 'all' || $from || $to || $cobradorId)
            <a href="{{ route('admin.billing') }}" class="btn btn-ghost flex items-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
              Limpiar Filtros
            </a>
          @endif
        </div>
      </div>
    </form>



    {{-- Reporte de Cobradores --}}
    @if(in_array(auth()->user()->role, ['admin', 'cajero', 'asistente']))
      <div class="card mb-6">
        <div class="border-b border-slate-200 pb-4 mb-4 flex items-start justify-between gap-4">
          <div>
            <h2 class="text-lg font-semibold text-slate-800 flex items-center gap-2">
              <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
              {{ auth()->user()->role === 'admin' ? 'Reporte de Cobradores' : 'Mi Reporte de Pagos' }}
            </h2>
            <p class="text-sm text-slate-500 mt-1">
              {{ auth()->user()->role === 'admin' ? 'Consulte cuánto recaudó cada cobrador. Haga clic en una fila para ver el desglose.' : 'Consulte su historial de pagos cobrados. Descargue su reporte para presentar.' }}
            </p>
          </div>
          <a href="{{ route('admin.billing.collectors.pdf', array_filter(['cobrador' => $cobradorId, 'from' => $from, 'to' => $to])) }}"
            target="_blank"
            class="btn bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 flex items-center gap-2 text-sm shadow-sm flex-shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Descargar PDF
          </a>
        </div>

        {{-- Resultados con Accordion --}}
        @if($collectorReport->count())
          @php
            $methodLabels = ['cash' => 'Efectivo', 'card' => 'Tarjeta', 'transfer' => 'Transferencia', 'wallet' => 'Billetera'];
            $grandTotal = $collectorReport->sum('total');
            $grandCount = $collectorReport->sum('cantidad');
          @endphp

          <div class="space-y-2">
            @foreach($collectorReport as $idx => $row)
              <div class="border border-slate-200 rounded-xl overflow-hidden bg-white hover:shadow-sm transition-shadow">
                {{-- Summary Row (clickable) --}}
                <button type="button"
                  onclick="document.getElementById('detail-{{ $idx }}').classList.toggle('hidden'); this.querySelector('.chevron-icon').classList.toggle('rotate-180')"
                  class="w-full px-4 py-3 flex items-center justify-between gap-4 text-left hover:bg-slate-50/80 transition-colors">
                  <div class="flex items-center gap-3 min-w-0">
                    <div
                      class="w-9 h-9 bg-gradient-to-br from-indigo-100 to-indigo-200 rounded-full flex items-center justify-center flex-shrink-0">
                      <svg class="w-4 h-4 text-indigo-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                      </svg>
                    </div>
                    <div class="min-w-0">
                      <p class="font-semibold text-slate-800 text-sm truncate">{{ $row->receiver->name ?? 'Sin asignar' }}</p>
                      <p class="text-xs text-slate-500">{{ \Carbon\Carbon::parse($row->fecha)->translatedFormat('d M, Y') }}</p>
                    </div>
                  </div>

                  <div class="flex items-center gap-5 flex-shrink-0">
                    <div class="text-right">
                      <p class="font-bold text-emerald-700 text-sm">Bs {{ number_format($row->total, 2) }}</p>
                      <p class="text-xs text-slate-500">{{ $row->cantidad }} {{ $row->cantidad === 1 ? 'cobro' : 'cobros' }}</p>
                    </div>
                    <div class="flex gap-1">
                      @foreach($row->metodos as $m)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium
                                  {{ $m === 'cash' ? 'bg-green-100 text-green-700' : '' }}
                                  {{ $m === 'card' ? 'bg-purple-100 text-purple-700' : '' }}
                                  {{ $m === 'transfer' ? 'bg-sky-100 text-sky-700' : '' }}
                                  {{ $m === 'wallet' ? 'bg-orange-100 text-orange-700' : '' }}
                                ">{{ $methodLabels[$m] ?? $m }}</span>
                      @endforeach
                    </div>
                    <svg class="w-5 h-5 text-slate-400 transition-transform duration-200 chevron-icon" fill="none"
                      stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                  </div>
                </button>

                {{-- Detail Panel (hidden by default) --}}
                <div id="detail-{{ $idx }}" class="hidden border-t border-slate-100">
                  <div class="bg-slate-50/50 px-4 py-3">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Desglose de cobros</p>
                    <div class="overflow-x-auto rounded-lg border border-slate-200 bg-white">
                      <table class="w-full text-xs">
                        <thead>
                          <tr class="bg-slate-100 text-slate-600">
                            <th class="px-3 py-2 text-left font-semibold">Hora</th>
                            <th class="px-3 py-2 text-left font-semibold">Recibo</th>
                            <th class="px-3 py-2 text-left font-semibold">Paciente</th>
                            <th class="px-3 py-2 text-right font-semibold">Monto</th>
                            <th class="px-3 py-2 text-left font-semibold">Método</th>
                            <th class="px-3 py-2 text-left font-semibold">Referencia</th>
                          </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                          @foreach($row->payments as $payment)
                            <tr class="hover:bg-blue-50/40 transition-colors">
                              <td class="px-3 py-2 text-slate-600 whitespace-nowrap">
                                <div class="flex items-center gap-1.5">
                                  <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                  </svg>
                                  {{ $payment->paid_at->format('d/m/Y H:i') }}
                                </div>
                              </td>
                              <td class="px-3 py-2">
                                <a href="{{ route('admin.billing.show', $payment->invoice_id) }}"
                                  class="text-blue-600 hover:text-blue-800 hover:underline font-medium">
                                  #{{ $payment->invoice->number ?? '—' }}
                                </a>
                              </td>
                              <td class="px-3 py-2 text-slate-700">
                                {{ $payment->invoice->patient->first_name ?? '' }}
                                {{ $payment->invoice->patient->last_name ?? '' }}
                              </td>
                              <td class="px-3 py-2 text-right font-semibold text-emerald-700 whitespace-nowrap">
                                Bs {{ number_format($payment->amount, 2) }}
                              </td>
                              <td class="px-3 py-2">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium
                                          {{ $payment->method === 'cash' ? 'bg-green-100 text-green-700' : '' }}
                                          {{ $payment->method === 'card' ? 'bg-purple-100 text-purple-700' : '' }}
                                          {{ $payment->method === 'transfer' ? 'bg-sky-100 text-sky-700' : '' }}
                                          {{ $payment->method === 'wallet' ? 'bg-orange-100 text-orange-700' : '' }}
                                        ">{{ $methodLabels[$payment->method] ?? $payment->method }}</span>
                              </td>
                              <td class="px-3 py-2 text-slate-500">
                                {{ $payment->reference ?? '—' }}
                              </td>
                            </tr>
                          @endforeach
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
            @endforeach
          </div>

          {{-- Totales del reporte --}}
          <div
            class="mt-4 p-4 bg-gradient-to-r from-slate-50 to-indigo-50 rounded-xl border border-slate-200 flex items-center justify-between">
            <div class="flex items-center gap-2">
              <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
              </svg>
              <span class="font-semibold text-slate-700">Total del Reporte</span>
            </div>
            <div class="flex items-center gap-6">
              <div class="text-right">
                <p class="text-xs text-slate-500 uppercase tracking-wide">Cobros</p>
                <p class="font-bold text-slate-800">{{ $grandCount }}</p>
              </div>
              <div class="text-right">
                <p class="text-xs text-slate-500 uppercase tracking-wide">Recaudado</p>
                <p class="font-bold text-emerald-700 text-lg">Bs {{ number_format($grandTotal, 2) }}</p>
              </div>
            </div>
          </div>

        @else
          <div class="text-center py-8 text-slate-400">
            <svg class="w-12 h-12 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <p class="text-sm font-medium text-slate-500">No hay cobros registrados</p>
            <p class="text-xs text-slate-400 mt-1">Ajuste los filtros para ver resultados.</p>
          </div>
        @endif
      </div>
    @endif

    {{-- Tabla de facturas --}}
    <div class="card p-0 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-slate-50 border-b border-slate-200">
            <tr class="text-left">
              <th class="px-4 py-3 font-semibold text-slate-700">Recibos</th>
              <th class="px-4 py-3 font-semibold text-slate-700">Paciente</th>
              <th class="px-4 py-3 font-semibold text-slate-700">Fecha</th>
              <th class="px-4 py-3 font-semibold text-slate-700 text-right">Precio de Servicio</th>
              <th class="px-4 py-3 font-semibold text-slate-700 text-right">Monto Cobrado</th>
              <th class="px-4 py-3 font-semibold text-slate-700 text-right">Saldo</th>
              <th class="px-4 py-3 font-semibold text-slate-700">Estado</th>
              <th class="px-4 py-3 font-semibold text-slate-700 text-right">Acciones</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200">
            @forelse($invoices as $invoice)
              @php
                $invoice->loadMissing(['items', 'payments']);
                $total = $invoice->grand_total;
                $paid = $invoice->paid_amount;
                $balance = $invoice->balance;

                $statusConfig = [
                  'draft' => ['class' => 'bg-slate-100 text-slate-700', 'icon' => 'edit', 'label' => 'Borrador'],
                  'issued' => ['class' => 'bg-blue-100 text-blue-700', 'icon' => 'send', 'label' => 'Pendiente'],
                  'paid' => ['class' => 'bg-emerald-100 text-emerald-700', 'icon' => 'check', 'label' => 'Pagada'],
                  'canceled' => ['class' => 'bg-rose-100 text-rose-700 line-through', 'icon' => 'x', 'label' => 'Anulada'],
                ];
                $statusInfo = $statusConfig[$invoice->status] ?? $statusConfig['draft'];
              @endphp

              <tr class="hover:bg-slate-50 transition-colors">
                {{-- Número de factura --}}
                <td class="px-4 py-3">
                  <a href="{{ route('admin.billing.show', $invoice) }}"
                    class="font-medium text-blue-600 hover:text-blue-800 hover:underline flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    {{ $invoice->number }}
                  </a>
                </td>

                {{-- Paciente --}}
                <td class="px-4 py-3">
                  <div class="flex items-center gap-2">
                    <div class="w-6 h-6 bg-slate-100 rounded-full flex items-center justify-center">
                      <svg class="w-3 h-3 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                      </svg>
                    </div>
                    <span>{{ $invoice->patient->first_name }} {{ $invoice->patient->last_name }}</span>
                  </div>
                </td>

                {{-- Fecha --}}
                <td class="px-4 py-3 text-slate-600">
                  {{ $invoice->created_at->format('d/m/Y H:i') }}
                </td>

                {{-- Total --}}
                <td class="px-4 py-3 text-right font-medium text-slate-800">
                  Bs {{ number_format($total, 2) }}
                </td>

                {{-- Pagado --}}
                <td class="px-4 py-3 text-right font-medium text-emerald-600">
                  Bs {{ number_format($paid, 2) }}
                </td>

                <td class="px-4 py-3 text-right font-medium {{ $balance > 0 ? 'text-amber-600' : 'text-slate-600' }}">
                  Bs {{ number_format($balance, 2) }}
                </td>

                {{-- Estado --}}
                <td class="px-4 py-3">
                  <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusInfo['class'] }}">
                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      @if($statusInfo['icon'] === 'check')
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                      @elseif($statusInfo['icon'] === 'edit')
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      @elseif($statusInfo['icon'] === 'send')
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
                      @else
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                      @endif
                    </svg>
                    {{ $statusInfo['label'] }}
                  </span>
                </td>

                {{-- Acciones --}}
                <td class="px-4 py-3">
                  <div class="flex items-center justify-end gap-2">
                    {{-- Pagar --}}
                    @if(!in_array($invoice->status, ['paid', 'canceled']) && $balance > 0)
                      <a href="{{ route('admin.billing.show', $invoice) }}#registrar-cobro"
                        class="btn bg-emerald-600 text-white hover:bg-emerald-700 flex items-center gap-1 text-xs px-3 py-1.5 shadow-sm"
                        title="Registrar pago">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        Pagar
                      </a>
                    @endif

                    {{-- Ver --}}
                    <a href="{{ route('admin.billing.show', $invoice) }}" class="btn btn-ghost flex items-center gap-1"
                      title="Ver detalle">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                      </svg>
                    </a>

                    {{-- Editar --}}
                    @if(!in_array($invoice->status, ['paid', 'canceled']) && !$invoice->payments()->exists())
                      <a href="{{ route('admin.billing.edit', $invoice) }}" class="btn btn-ghost flex items-center gap-1"
                        title="Editar">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                      </a>
                    @endif

                    {{-- PDF --}}
                    @php
                      $pdfRel = 'invoices/invoice_' . $invoice->number . '.pdf';
                      $pdfExists = \Illuminate\Support\Facades\Storage::disk('public')->exists($pdfRel);
                    @endphp
                    @if($pdfExists)
                      <a href="{{ route('admin.invoices.view', $invoice) }}?t={{ time() }}"
                        class="btn btn-ghost flex items-center gap-1" target="_blank" rel="noopener" title="Ver PDF">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                      </a>
                    @endif
                    <form action="{{ route('admin.invoices.regenerate', $invoice) }}" method="post" class="inline">
                      @csrf
                      <button class="btn btn-ghost flex items-center gap-1"
                        title="{{ $pdfExists ? 'Regenerar PDF' : 'Generar PDF' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                      </button>
                    </form>


                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="8" class="px-4 py-8 text-center">
                  <div class="flex flex-col items-center justify-center text-slate-500">
                    <svg class="w-12 h-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" />
                    </svg>
                    <p class="text-lg font-medium mb-1">No se encontraron recibos</p>
                    <p class="text-sm">No hay resultados que coincidan con tu búsqueda.</p>
                    <a href="{{ route('admin.billing.create') }}"
                      class="btn bg-blue-600 text-white hover:bg-blue-700 flex items-center gap-2 mt-4">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                      </svg>
                      Crear Primer Recibo
                    </a>
                  </div>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    {{-- Paginación --}}
    @if($invoices->hasPages())
      <div class="mt-6">
        {{ $invoices->links() }}
      </div>
    @endif
  </div>
@endsection