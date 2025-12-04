@extends('layouts.app')
@section('title','Mis Recibos')

@section('content')
  <div class="grid gap-3">
    @forelse($invoices as $inv)
      <a class="card hover:bg-slate-50 block" href="{{ route('app.invoices.show',$inv) }}">
        <div class="flex items-center justify-between">
          <div>
            <div class="font-semibold">#{{ $inv->number }}</div>
            <div class="text-xs text-slate-500">{{ $inv->created_at->format('Y-m-d H:i') }} · Estado: {{ strtoupper($inv->status) }}</div>
          </div>
          <div class="text-sm text-slate-600">
            Items: {{ $inv->items->count() }} · Total a pagar: {{ number_format($inv->items->sum('total'),2) }}
          </div>
        </div>
      </a>
    @empty
      <div class="text-sm text-slate-500">Aún no tienes recibos.</div>
    @endforelse
  </div>

  <div class="mt-3">{{ $invoices->links() }}</div>
@endsection
