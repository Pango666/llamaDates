@extends('patient.layout')
@section('title','Cita')

@section('pt')
  <div class="card mb-4">
    <h3 class="font-semibold mb-2">{{ $appointment->service->name }}</h3>
    <div class="grid md:grid-cols-2 gap-2 text-sm">
      <div>Fecha: {{ $appointment->date }} · {{ substr($appointment->start_time,0,5) }}–{{ substr($appointment->end_time,0,5) }}</div>
      <div>Odontólogo: {{ $appointment->dentist->name }}</div>
      <div>Estado: {{ $appointment->status }}</div>
      <div>Notas: {{ $appointment->notes ?: '—' }}</div>
    </div>
  </div>

  <div class="card">
    <h4 class="font-semibold mb-2">Adjuntos</h4>
    @forelse($appointment->attachments as $att)
      <div class="flex justify-between border-b last:border-0 py-2 text-sm">
        <div>{{ $att->original_name }}</div>
        <a class="btn btn-ghost" target="_blank" href="{{ asset('storage/'.$att->path) }}">Ver</a>
      </div>
    @empty
      <div class="text-sm text-slate-500">Sin adjuntos.</div>
    @endforelse
  </div>
@endsection
