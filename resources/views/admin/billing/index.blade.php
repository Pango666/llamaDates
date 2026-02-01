@extends('layouts.app')
@section('title', 'Gestión de Pagos y Recibos')

@section('header-actions')
  <div class="flex gap-2">
    @if(auth()->user()->role === 'admin')
      <a href="{{ route('admin.billing.pdf', request()->query()) }}" target="_blank" class="btn bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        Exportar Reporte
      </a>
    @endif
    <a href="{{ route('admin.billing.create') }}" class="btn bg-blue-600 text-white hover:bg-blue-700 flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
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
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
          </svg>
          Gestión de Pagos y Recibos
        </h1>
        <p class="text-sm text-slate-600 mt-1">Administre los recibos y pagos de los pacientes.</p>
      </div>
    </div>

    {{-- Charts (Admin) --}}
    @if(auth()->user()->role === 'admin' && isset($chartIncome))
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
          <div class="card bg-white p-4">
              <h3 class="text-sm font-semibold text-slate-700 mb-4">Ingresos (Últimos 15 Días)</h3>
              <div class="h-64">
                <canvas id="incomeChart"></canvas>
              </div>
          </div>
          <div class="card bg-white p-4">
              <h3 class="text-sm font-semibold text-slate-700 mb-4">Estado de Recibos</h3>
              <div class="h-64">
                <canvas id="billStatusChart"></canvas>
              </div>
          </div>
      </div>
      <script>
        document.addEventListener('DOMContentLoaded', () => {
            try {
                // Income
                new Chart(document.getElementById('incomeChart'), {
                    type: 'line',
                    data: {
                        labels: {!! json_encode($chartIncome->keys()) !!},
                        datasets: [{
                            label: 'Ingresos (Bs)',
                            data: {!! json_encode($chartIncome->values()) !!},
                            borderColor: '#10b981',
                            backgroundColor: '#d1fae5',
                            fill: true,
                            tension: 0.3
                        }]
                    },
                    options: { maintainAspectRatio: false, scales: { y: { beginAtZero: true } } }
                });

                // Status
                new Chart(document.getElementById('billStatusChart'), {
                    type: 'pie',
                    data: {
                        labels: {!! json_encode($chartStatus->keys()) !!},
                        datasets: [{
                            data: {!! json_encode($chartStatus->values()) !!},
                            backgroundColor: ['#f59e0b', '#3b82f6', '#10b981', '#ef4444'],
                        }]
                    },
                    options: { maintainAspectRatio: false, plugins: { legend: { position: 'right' } } }
                });
            } catch(e){ console.error(e); }
        });
      </script>
    @endif

    {{-- Filtros --}}
    <form method="get" class="card mb-6">
      <div class="grid gap-4 md:grid-cols-5 md:items-end">
        {{-- Búsqueda --}}
        <div class="md:col-span-2 space-y-2">
          <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            Buscar
          </label>
          <input 
            type="text" 
            name="q" 
            value="{{ $q }}" 
            placeholder="Número de recibo o nombre del paciente..."
            class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
          >
        </div>

        {{-- Estado --}}
        <div class="space-y-2">
          <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Estado
          </label>
          <select name="status" class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors">
            @foreach(['all' => 'Todos', 'draft' => 'Borrado', 'issued' => 'Pendiente', 'paid' => 'Pagada', 'canceled' => 'Cancelada'] as $k => $lbl)
              <option value="{{ $k }}" @selected($status === $k)>{{ $lbl }}</option>
            @endforeach
          </select>
        </div>

        {{-- Fecha Desde --}}
        <div class="space-y-2">
          <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            Desde
          </label>
          <input 
            type="date" 
            name="from" 
            value="{{ $from }}" 
            class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
          >
        </div>

        {{-- Fecha Hasta --}}
        <div class="space-y-2">
          <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            Hasta
          </label>
          <input 
            type="date" 
            name="to" 
            value="{{ $to }}" 
            class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
          >
        </div>

        {{-- Botones de acción --}}
        <div class="flex gap-2 md:col-span-5">
          <button type="submit" class="btn bg-blue-600 text-white hover:bg-blue-700 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            Aplicar Filtros
          </button>
          
          @if($q !== '' || $status !== 'all' || $from || $to)
            <a href="{{ route('admin.billing') }}" class="btn btn-ghost flex items-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
              Limpiar Filtros
            </a>
          @endif
        </div>
      </div>
    </form>

    {{-- Estadísticas --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
      <div class="card bg-blue-50 border-blue-200">
        <div class="flex items-center gap-3">
          <div class="p-2 bg-blue-100 rounded-lg">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
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
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
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
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
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
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </div>
          <div>
            <p class="text-sm font-medium text-rose-800">Canceladas</p>
            <p class="text-2xl font-bold text-rose-900">
              {{ $invoices->where('status', 'canceled')->count() }}
            </p>
          </div>
        </div>
      </div>
    </div>

    {{-- Tabla de facturas --}}
    <div class="card p-0 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-slate-50 border-b border-slate-200">
            <tr class="text-left">
              <th class="px-4 py-3 font-semibold text-slate-700">Recibos</th>
              <th class="px-4 py-3 font-semibold text-slate-700">Paciente</th>
              <th class="px-4 py-3 font-semibold text-slate-700">Fecha</th>
              <th class="px-4 py-3 font-semibold text-slate-700 text-right">Total</th>
              <th class="px-4 py-3 font-semibold text-slate-700 text-right">Pagado</th>
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
                  'draft' => ['class' => 'bg-slate-100 text-slate-700', 'icon' => 'edit'],
                  'issued' => ['class' => 'bg-blue-100 text-blue-700', 'icon' => 'send'],
                  'paid' => ['class' => 'bg-emerald-100 text-emerald-700', 'icon' => 'check'],
                  'canceled' => ['class' => 'bg-rose-100 text-rose-700 line-through', 'icon' => 'x'],
                ];
                $statusInfo = $statusConfig[$invoice->status] ?? $statusConfig['draft'];
              @endphp
              
              <tr class="hover:bg-slate-50 transition-colors">
                {{-- Número de factura --}}
                <td class="px-4 py-3">
                  <a href="{{ route('admin.billing.show', $invoice) }}" class="font-medium text-blue-600 hover:text-blue-800 hover:underline flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    {{ $invoice->number }}
                  </a>
                </td>

                {{-- Paciente --}}
                <td class="px-4 py-3">
                  <div class="flex items-center gap-2">
                    <div class="w-6 h-6 bg-slate-100 rounded-full flex items-center justify-center">
                      <svg class="w-3 h-3 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                      </svg>
                    </div>
                    <span>{{ $invoice->patient->first_name }} {{ $invoice->patient->last_name }}</span>
                  </div>
                </td>

                {{-- Fecha --}}
                <td class="px-4 py-3 text-slate-600">
                  {{ $invoice->created_at->format('d/m/Y') }}
                </td>

                {{-- Total --}}
                <td class="px-4 py-3 text-right font-medium text-slate-800">
                  ${{ number_format($total, 2) }}
                </td>

                {{-- Pagado --}}
                <td class="px-4 py-3 text-right font-medium text-emerald-600">
                  ${{ number_format($paid, 2) }}
                </td>

                {{-- Saldo --}}
                <td class="px-4 py-3 text-right font-medium {{ $balance > 0 ? 'text-amber-600' : 'text-slate-600' }}">
                  ${{ number_format($balance, 2) }}
                </td>

                {{-- Estado --}}
                <td class="px-4 py-3">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusInfo['class'] }}">
                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      @if($statusInfo['icon'] === 'check')
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                      @elseif($statusInfo['icon'] === 'edit')
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                      @elseif($statusInfo['icon'] === 'send')
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>
                      @else
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                      @endif
                    </svg>
                    {{ ucfirst($invoice->status) }}
                  </span>
                </td>

                {{-- Acciones --}}
                <td class="px-4 py-3">
                  <div class="flex items-center justify-end gap-2">
                    {{-- Ver --}}
                    <a href="{{ route('admin.billing.show', $invoice) }}" class="btn btn-ghost flex items-center gap-1" title="Ver detalle">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                      </svg>
                    </a>

                    {{-- Editar --}}
                    @if(!in_array($invoice->status, ['paid', 'canceled']) && !$invoice->payments()->exists())
                      <a href="{{ route('admin.billing.edit', $invoice) }}" class="btn btn-ghost flex items-center gap-1" title="Editar">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                      </a>
                    @endif

                    {{-- PDF --}}
                    @php
                      $pdfRel = 'invoices/invoice_'.$invoice->number.'.pdf';
                      $pdfExists = \Illuminate\Support\Facades\Storage::disk('public')->exists($pdfRel);
                    @endphp
                    @if($pdfExists)
                      <a href="{{ route('admin.invoices.view', $invoice) }}?t={{ time() }}" class="btn btn-ghost flex items-center gap-1" target="_blank" rel="noopener" title="Ver PDF">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                      </a>
                    @endif
                    <form action="{{ route('admin.invoices.regenerate', $invoice) }}" method="post" class="inline">
                      @csrf
                      <button class="btn btn-ghost flex items-center gap-1" title="{{ $pdfExists ? 'Regenerar PDF' : 'Generar PDF' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                        </svg>
                      </button>
                    </form>

                    {{-- Eliminar --}}
                    @if(!$invoice->payments()->exists())
                      <form method="post" action="{{ route('admin.billing.delete', $invoice) }}" onsubmit="return confirm('¿Está seguro de eliminar este recibo?');">
                        @csrf @method('DELETE')
                        <button class="btn bg-red-600 text-white hover:bg-red-700 flex items-center gap-1" title="Eliminar">
                          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                          </svg>
                        </button>
                      </form>
                    @endif
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="8" class="px-4 py-8 text-center">
                  <div class="flex flex-col items-center justify-center text-slate-500">
                    <svg class="w-12 h-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
                    </svg>
                    <p class="text-lg font-medium mb-1">No se encontraron recibos</p>
                    <p class="text-sm">No hay resultados que coincidan con tu búsqueda.</p>
                    <a href="{{ route('admin.billing.create') }}" class="btn bg-blue-600 text-white hover:bg-blue-700 flex items-center gap-2 mt-4">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
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