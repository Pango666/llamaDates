@extends('patient.layout')
@section('title','Consentimientos')

@section('pt')
  <div class="card">
    @forelse($rows as $c)
      <div class="flex items-center justify-between border-b last:border-0 py-2">
        <div>
          <div class="font-medium">{{ $c->title }}</div>
          <div class="text-xs text-slate-500">{{ $c->created_at->format('Y-m-d H:i') }}</div>
        </div>
        <a class="btn btn-ghost" href="{{ route('app.consents.show',$c) }}">Ver</a>
      </div>
    @empty
      <div class="text-sm text-slate-500">Sin consentimientos.</div>
    @endforelse
    <div class="p-3">{{ $rows->links() }}</div>
  </div>
@endsection
