@extends('layouts.app')
@section('title','Consentimiento')

@section('header-actions')
  <a href="{{ route('admin.patients.consents.index',$consent->patient_id) }}" class="btn btn-ghost">Volver</a>
@endsection

@section('content')
<div class="grid gap-4 md:grid-cols-3">
  <section class="card md:col-span-2">
    <h3 class="font-semibold mb-2">{{ $consent->title }}</h3>
    <div class="text-sm text-slate-500 mb-3">
      Paciente: <strong>{{ $consent->patient->last_name }}, {{ $consent->patient->first_name }}</strong>
      · Creado: {{ $consent->created_at->format('Y-m-d H:i') }}
    </div>
    <div class="prose max-w-none">
      {!! nl2br(e($consent->body)) !!}
    </div>
  </section>

  <aside class="card">
    <h4 class="font-semibold mb-2">Acciones</h4>
    <div class="flex flex-col gap-2">
      <a class="btn btn-ghost" href="{{ route('admin.consents.pdf',$consent) }}">
        {{ $consent->file_path ? 'Descargar PDF' : 'Generar PDF' }}
      </a>
      @if($consent->file_path)
        <a class="btn btn-ghost" href="{{ asset('storage/'.$consent->file_path) }}" target="_blank">Ver PDF</a>
      @endif
    </div>

    <div class="border-t my-3"></div>

    <h4 class="font-semibold mb-2">Firmado</h4>
    @if($consent->signed_at)
      <div class="text-sm">
        Firmado por <strong>{{ $consent->signed_by_name }}</strong>
        @if($consent->signed_by_doc) ({{ $consent->signed_by_doc }}) @endif
        el {{ $consent->signed_at->format('Y-m-d H:i') }}
      </div>
      @if($consent->signature_path)
        <a class="btn btn-ghost mt-2" href="{{ asset('storage/'.$consent->signature_path) }}" target="_blank">Ver escaneado</a>
      @endif
    @else
      <form method="post" action="{{ route('admin.consents.uploadSigned',$consent) }}" enctype="multipart/form-data" class="space-y-2">
        @csrf
        <input class="w-full border rounded px-2 py-2" name="signed_by_name" placeholder="Nombre quien firma" required>
        <input class="w-full border rounded px-2 py-2" name="signed_by_doc"  placeholder="Documento (opcional)">
        <input type="file" name="scan" accept=".pdf,.jpg,.jpeg,.png" class="w-full border rounded px-2 py-2" required>
        <button class="btn btn-primary w-full">Adjuntar firmado</button>
      </form>
    @endif
  </aside>
</div>
@endsection
