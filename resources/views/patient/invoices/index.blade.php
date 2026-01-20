@extends('patient.layout')
@section('title','Mis pagos')

@php
  $statusMap = [
    'draft'     => ['label' => 'Borrador',     'cls' => 'bg-slate-50 text-slate-700 border-slate-200'],
    'pending'   => ['label' => 'Pendiente',    'cls' => 'bg-amber-50 text-amber-800 border-amber-200'],
    'unpaid'    => ['label' => 'Pendiente',    'cls' => 'bg-amber-50 text-amber-800 border-amber-200'],
    'paid'      => ['label' => 'Pagado',       'cls' => 'bg-emerald-50 text-emerald-800 border-emerald-200'],
    'cancelled' => ['label' => 'Anulado',      'cls' => 'bg-rose-50 text-rose-800 border-rose-200 line-through'],
    'canceled'  => ['label' => 'Anulado',      'cls' => 'bg-rose-50 text-rose-800 border-rose-200 line-through'],
    'void'      => ['label' => 'Anulado',      'cls' => 'bg-rose-50 text-rose-800 border-rose-200 line-through'],
    'refunded'  => ['label' => 'Reembolsado',  'cls' => 'bg-indigo-50 text-indigo-800 border-indigo-200'],

    // Por si te llega algo raro:
    'non-attendance' => ['label' => 'Inasistencia', 'cls' => 'bg-rose-50 text-rose-800 border-rose-200'],
    'no_show'        => ['label' => 'Inasistencia', 'cls' => 'bg-rose-50 text-rose-800 border-rose-200'],
  ];

  $labelOf = function($s) use ($statusMap) {
    $k = strtolower(trim((string) $s));
    return $statusMap[$k]['label'] ?? 'Pendiente';
  };

  $clsOf = function($s) use ($statusMap) {
    $k = strtolower(trim((string) $s));
    return $statusMap[$k]['cls'] ?? 'bg-amber-50 text-amber-800 border-amber-200';
  };

  $fmtMoney = fn($n) => number_format((float)$n, 2);

  $fmtDate = function($dt) {
    if (!$dt) return '—';
    try {
      return $dt->locale('es')->translatedFormat('D, d M Y');
    } catch (\Throwable $e) {
      return $dt->format('Y-m-d');
    }
  };
@endphp

@section('content')
  <div class="max-w-5xl mx-auto">
    {{-- Encabezado --}}
    <div class="card border border-slate-200 mb-4">
      <div class="flex items-start justify-between gap-3 flex-wrap">
        <div>
          <div class="text-xs uppercase tracking-wide text-slate-500">Mis pagos</div>
          <div class="font-semibold text-slate-800 mt-1">
            {{ $invoices->total() }} recibo(s)
          </div>
          <div class="text-sm text-slate-600 mt-1">
            Aquí verás tus recibos y su estado.
          </div>
        </div>

        <a href="{{ route('app.dashboard') }}"
           class="inline-flex items-center justify-center rounded-lg px-4 py-2 text-sm font-medium
                  border border-slate-300 bg-white text-slate-800 hover:bg-slate-50 transition">
          Volver al panel
        </a>
      </div>
    </div>

    {{-- Listado --}}
    <div class="grid gap-3">
      @forelse($invoices as $inv)
        @php
          $itemsCount = $inv->relationLoaded('items')
            ? $inv->items->count()
            : ($inv->items_count ?? null);

          $itemsCount = $itemsCount ?? (method_exists($inv, 'items') ? $inv->items()->count() : 0);

          if ($inv->relationLoaded('items')) {
            $total = (float) $inv->items->sum('total');
          } else {
            $total = (float) ($inv->total ?? $inv->amount_total ?? $inv->items_sum_total ?? 0);
            if ($total == 0 && method_exists($inv, 'items')) {
              try { $total = (float) $inv->items()->sum('total'); } catch (\Throwable $e) { $total = 0; }
            }
          }

          $status = strtolower((string)($inv->status ?? 'pending'));
          $createdAt = $inv->created_at;
          $isRecent = $createdAt ? $createdAt->diffInDays(now()) <= 7 : false;
        @endphp

        <a href="{{ route('app.invoices.show',$inv) }}"
           class="card border border-slate-200 hover:bg-slate-50 transition block">
          <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
              <div class="flex items-center gap-2 flex-wrap mb-2">
                <span class="inline-flex items-center text-xs px-2.5 py-1 rounded-full border {{ $clsOf($status) }}">
                  {{ $labelOf($status) }}
                </span>

                @if($isRecent)
                  <span class="inline-flex items-center text-xs px-2.5 py-1 rounded-full border bg-emerald-50 text-emerald-800 border-emerald-200">
                    Reciente
                  </span>
                @endif
              </div>

              <div class="font-semibold text-slate-800 truncate">
                Recibo #{{ $inv->number }}
              </div>

              <div class="text-xs text-slate-500 mt-1 flex flex-wrap gap-x-3 gap-y-1">
                <span>{{ $fmtDate($createdAt) }}</span>
                <span>·</span>
                <span>{{ $createdAt ? $createdAt->format('H:i') : '—' }}</span>
              </div>
            </div>

            <div class="shrink-0 text-right">
              <div class="text-xs text-slate-500">Total</div>
              <div class="text-base font-bold text-slate-900 leading-tight">
                Bs {{ $fmtMoney($total) }}
              </div>
              <div class="text-xs text-slate-500 mt-1">
                {{ $itemsCount }} item(s)
              </div>
            </div>
          </div>

          <div class="mt-3 flex items-center justify-between">
            <span class="text-sm text-slate-600">
              Ver detalle del recibo
            </span>
            <span class="inline-flex items-center justify-center rounded-lg px-3 py-1.5 text-sm font-semibold
                        bg-blue-600 text-white hover:bg-blue-700 transition">
              Abrir
            </span>
          </div>
        </a>
      @empty
        <div class="card border border-dashed border-slate-300 text-center text-slate-500 py-10">
          Aún no tienes recibos.
        </div>
      @endforelse
    </div>

    <div class="mt-4">
      {{ $invoices->links() }}
    </div>
  </div>
@endsection
