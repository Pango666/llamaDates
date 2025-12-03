@extends('layouts.app')
@section('title','Consentimiento')

@section('header-actions')
  <a href="{{ route('admin.patients.consents.index',$patient) }}" class="btn bg-slate-600 text-white hover:bg-slate-700 flex items-center gap-2 transition-colors">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
    </svg>
    Volver
  </a>
  <a href="{{ route('admin.consents.edit',$consent) }}" class="btn btn-ghost">Editar</a>
  <a href="{{ route('admin.consents.print',$consent) }}" class="btn btn-ghost">Imprimir</a>
  <a href="{{ route('admin.consents.pdf',$consent) }}" class="btn btn-ghost">PDF</a>
@endsection

@section('content')
<div class="grid md:grid-cols-3 gap-4">
  <section class="card md:col-span-2">
    <h3 class="font-semibold mb-2">{{ $consent->title }}</h3>
    <div class="text-sm text-slate-500 mb-3">
      Paciente: <span class="font-medium">{{ $patient->last_name }}, {{ $patient->first_name }}</span>
      · Creado: {{ $consent->created_at->format('Y-m-d H:i') }}
    </div>
    <article class="prose max-w-none">
      {!! nl2br(e($consent->body)) !!}
    </article>

    <div class="mt-4 p-3 bg-slate-50 rounded">
      <div class="text-xs text-slate-500 mb-1">Firma</div>
      @if($consent->signed_at)
        <div class="text-sm">Firmado el {{ $consent->signed_at->format('Y-m-d H:i') }}
          @if($consent->signed_by_name) por {{ $consent->signed_by_name }} @endif
          @if($consent->signed_by_doc) ({{ $consent->signed_by_doc }}) @endif
        </div>
        @if($consent->file_path)
          <div class="mt-2">
            <a class="text-blue-600 hover:underline" target="_blank" href="{{ asset('storage/'.$consent->file_path) }}">Ver escaneo</a>
          </div>
        @endif
      @else
        <div class="text-sm text-amber-700">Pendiente de firma (sube el escaneo firmado abajo)</div>
      @endif
    </div>
  </section>

  <aside class="card">
    <h4 class="font-semibold mb-2">Subir escaneo firmado</h4>
    <form method="post" action="{{ route('admin.consents.upload',$consent) }}" enctype="multipart/form-data" class="space-y-2">
      @csrf
      <div>
        <label class="block text-xs text-slate-500 mb-1">Nombre del firmante</label>
        <input name="signed_by_name" class="w-full border rounded px-3 py-2" value="{{ old('signed_by_name',$consent->signed_by_name) }}">
      </div>
      <div>
        <label class="block text-xs text-slate-500 mb-1">Documento (CI/NIT)</label>
        <input name="signed_by_doc" class="w-full border rounded px-3 py-2" value="{{ old('signed_by_doc',$consent->signed_by_doc) }}">
      </div>
      <div>
        <label class="block text-xs text-slate-500 mb-1">Archivo (PDF/JPG/PNG)</label>
        <input type="file" name="scan" accept=".pdf,.jpg,.jpeg,.png" required>
      </div>
      <button class="btn btn-primary w-full">Guardar escaneo y marcar como firmado</button>
    </form>
  </aside>
</div>
@endsection
