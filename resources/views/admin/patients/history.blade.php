@extends('layouts.app')
@section('title','Historia clínica')

@section('header-actions')
  <a href="{{ route('admin.patients') }}" class="btn btn-ghost">Volver</a>
  <a href="{{ route('admin.patients.timeline', $patient) }}" class="btn btn-ghost">Historia completa</a>
  <a href="{{ route('admin.odontograms.open', $patient) }}" class="btn btn-primary">Abrir odontograma</a>
@endsection

@section('content')
  {{-- Encabezado --}}
  <div class="mb-4">
    <h2 class="text-lg font-semibold">Historia clínica</h2>
    <p class="text-sm text-slate-500">
      Paciente: <span class="font-medium">{{ $patient->first_name }} {{ $patient->last_name }}</span>
    </p>
  </div>

  {{-- Formulario --}}
  <form method="post" action="{{ route('admin.patients.history.update', $patient) }}" class="card space-y-4">
    @csrf @method('PUT')

    <div class="grid md:grid-cols-3 gap-4">
      <label class="flex items-center gap-2">
        <input type="checkbox" name="smoker" value="1" {{ old('smoker',$history->smoker) ? 'checked' : '' }}>
        <span>Fumador/a</span>
      </label>

      <label class="flex items-center gap-2">
        <input type="checkbox" name="pregnant" value="1" {{ old('pregnant',$history->pregnant) ? 'checked' : '' }}>
        <span>Embarazo</span>
      </label>
    </div>

    <div class="grid md:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm mb-1">Alergias</label>
        <textarea name="allergies" rows="2" class="w-full border rounded px-3 py-2"
          placeholder="Penicilina, anestésicos locales...">{{ old('allergies',$history->allergies) }}</textarea>
      </div>
      <div>
        <label class="block text-sm mb-1">Medicación</label>
        <textarea name="medications" rows="2" class="w-full border rounded px-3 py-2"
          placeholder="Medicamentos actuales/temporales">{{ old('medications',$history->medications) }}</textarea>
      </div>
      <div>
        <label class="block text-sm mb-1">Enf. sistémicas</label>
        <textarea name="systemic_diseases" rows="2" class="w-full border rounded px-3 py-2"
          placeholder="Hipertensión, diabetes, etc.">{{ old('systemic_diseases',$history->systemic_diseases) }}</textarea>
      </div>
      <div>
        <label class="block text-sm mb-1">Cirugías previas</label>
        <textarea name="surgical_history" rows="2" class="w-full border rounded px-3 py-2"
          placeholder="Apendicectomía 2010, etc.">{{ old('surgical_history',$history->surgical_history) }}</textarea>
      </div>
      <div class="md:col-span-2">
        <label class="block text-sm mb-1">Hábitos</label>
        <textarea name="habits" rows="2" class="w-full border rounded px-3 py-2"
          placeholder="Café diario, bruxismo, etc.">{{ old('habits',$history->habits) }}</textarea>
      </div>
    </div>

    <div>
      <label class="block text-sm mb-1">Extra (JSON opcional)</label>
      <textarea name="extra_json" rows="3" class="w-full border rounded px-3 py-2"
        placeholder='{"bp":"120/80","temp":"36.7"}'>{{ old('extra_json', $history->extra ? json_encode($history->extra, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) : '') }}</textarea>
      <p class="text-xs text-slate-500 mt-1">Si lo completas, se guardará en <code>extra</code>.</p>
    </div>

    <div class="pt-2">
      <button class="btn bg-blue-600 text-white hover:bg-blue-700">Guardar</button>
    </div>
  </form>
@endsection
