@extends('patient.layout')
@section('title','Consentimiento')

@section('pt')
  <div class="card">
    <h3 class="font-semibold mb-2">{{ $consent->title }}</h3>
    <div class="prose max-w-none">{!! nl2br(e($consent->content)) !!}</div>

    @if($consent->pdf_path)
      <div class="mt-3">
        <a class="btn" target="_blank" href="{{ asset('storage/'.$consent->pdf_path) }}">Abrir PDF</a>
      </div>
    @endif
  </div>
@endsection
