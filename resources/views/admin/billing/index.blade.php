@extends('layouts.app')
@section('title','Pagos / Facturas')

@section('header-actions')
  <a href="{{ route('admin.billing.create') }}" class="btn btn-primary">+ Nueva factura</a>
@endsection

@section('content')
  <form method="get" class="card mb-4">
    <div class="grid gap-3 md:grid-cols-5 md:items-end">
      <div class="md:col-span-2">
        <label class="block text-xs text-slate-500 mb-1">Buscar (número o paciente)</label>
        <input type="text" name="q" value="{{ $q }}" class="w-full border rounded px-3 py-2">
      </div>
      <div>
        <label class="block text-xs text-slate-500 mb-1">Estado</label>
        <select name="status" class="w-full border rounded px-3 py-2">
          @foreach(['all'=>'Todos','draft'=>'Borrador','issued'=>'Emitida','paid'=>'Pagada','canceled'=>'Cancelada'] as $k=>$lbl)
            <option value="{{ $k }}" @selected($status===$k)>{{ $lbl }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="block text-xs text-slate-500 mb-1">Desde</label>
        <input type="date" name="from" value="{{ $from }}" class="w-full border rounded px-3 py-2">
      </div>
      <div>
        <label class="block text-xs text-slate-500 mb-1">Hasta</label>
        <input type="date" name="to" value="{{ $to }}" class="w-full border rounded px-3 py-2">
      </div>
      <div class="md:col-span-5">
        <button class="btn btn-ghost">Filtrar</button>
        @if($q!=='' || $status!=='all' || $from || $to)
          <a href="{{ route('admin.billing') }}" class="btn btn-ghost">Limpiar</a>
        @endif
      </div>
    </div>
  </form>

  <div class="card p-0 overflow-x-auto">
    <table class="min-w-full text-sm">
      <thead class="bg-white border-b">
        <tr class="text-left">
          <th class="px-3 py-2">N°</th>
          <th class="px-3 py-2">Paciente</th>
          <th class="px-3 py-2">Fecha</th>
          <th class="px-3 py-2 text-right">Total</th>
          <th class="px-3 py-2 text-right">Pagado</th>
          <th class="px-3 py-2 text-right">Saldo</th>
          <th class="px-3 py-2">Estado</th>
          <th class="px-3 py-2 text-right">Acciones</th>
        </tr>
      </thead>
      <tbody>
        @forelse($invoices as $inv)
          @php
            $inv->loadMissing(['items','payments']);
            $total  = $inv->grand_total;
            $paid   = $inv->paid_amount;
            $bal    = $inv->balance;
            $badge = [
              'draft'=>'bg-slate-200 text-slate-700',
              'issued'=>'bg-blue-100 text-blue-700',
              'paid'=>'bg-emerald-100 text-emerald-700',
              'canceled'=>'bg-rose-100 text-rose-700 line-through',
            ][$inv->status] ?? 'bg-slate-100 text-slate-700';
          @endphp
          <tr class="border-b hover:bg-slate-50">
            <td class="px-3 py-2">
              <a href="{{ route('admin.billing.show',$inv) }}" class="hover:underline font-medium">{{ $inv->number }}</a>
            </td>
            <td class="px-3 py-2">{{ $inv->patient->first_name }} {{ $inv->patient->last_name }}</td>
            <td class="px-3 py-2">{{ $inv->created_at->format('Y-m-d') }}</td>
            <td class="px-3 py-2 text-right">{{ number_format($total,2) }}</td>
            <td class="px-3 py-2 text-right">{{ number_format($paid,2) }}</td>
            <td class="px-3 py-2 text-right">{{ number_format($bal,2) }}</td>
            <td class="px-3 py-2"><span class="badge {{ $badge }}">{{ ucfirst($inv->status) }}</span></td>
            <td class="px-3 py-2">
              <div class="flex items-center justify-end gap-2">
                <a class="btn btn-ghost" href="{{ route('admin.billing.show',$inv) }}">Ver</a>
                @if(!in_array($inv->status,['paid','canceled']) && !$inv->payments()->exists())
                  <a class="btn btn-ghost" href="{{ route('admin.billing.edit',$inv) }}">Editar</a>
                @endif
                @php
  $pdfRel = 'invoices/invoice_'.$inv->number.'.pdf';
  $pdfExists = \Illuminate\Support\Facades\Storage::disk('public')->exists($pdfRel);
@endphp
@if($inv->status==='paid')
    @if($pdfExists)
      <a href="{{ route('admin.invoices.view',$inv) }}"
         class="btn btn-ghost" target="_blank" rel="noopener">
        Ver comprobante
      </a>
    @endif
    <form action="{{ route('admin.invoices.regenerate',$inv) }}" method="post" class="inline">
      @csrf
      <button class="btn btn-ghost">
        {{ $pdfExists ? 'Regenerar PDF' : 'Generar PDF' }}
      </button>
    </form>
  @endif
                @if(!$inv->payments()->exists())
                  <form method="post" action="{{ route('admin.billing.delete',$inv) }}"
                        onsubmit="return confirm('¿Eliminar factura?');">
                    @csrf @method('DELETE')
                    <button class="btn btn-danger">Eliminar</button>
                  </form>
                @endif
              </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="8" class="px-3 py-6 text-center text-slate-500">Sin resultados.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-3">{{ $invoices->links() }}</div>
@endsection
