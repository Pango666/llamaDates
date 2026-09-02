@extends('layouts.app')
@section('title','Nuevo plan de tratamiento')

@section('header-actions')
  <a href="{{ route('admin.patients.plans.index',$patient) }}" class="btn bg-slate-600 text-white hover:bg-slate-700 flex items-center gap-2 transition-colors">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
    </svg>
    Volver
</a>
@endsection

@section('content')
  <form method="post" action="{{ route('admin.patients.plans.store',$patient) }}" class="card max-w-2xl mx-auto">
    @csrf
    <h3 class="font-semibold mb-4 text-lg border-b pb-2">Nuevo plan para {{ $patient->last_name }}, {{ $patient->first_name }}</h3>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
      <div>
        <label class="block text-sm font-medium mb-1 text-slate-700">Servicio / Tratamiento *</label>
        <select name="service_id" id="service_id" class="w-full border rounded-lg px-3 py-2 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-sky-500 outline-none" required>
          <option value="">Seleccione un servicio</option>
          @foreach($services as $s)
            <option value="{{ $s->id }}" data-price="{{ $s->price }}">{{ $s->name }} (Bs {{ number_format($s->price, 2) }})</option>
          @endforeach
        </select>
        @error('service_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
      </div>

      <div>
        <label class="block text-sm font-medium mb-1 text-slate-700">Odontólogo Asignado *</label>
        <select name="dentist_id" class="w-full border rounded-lg px-3 py-2 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-sky-500 outline-none" required>
          <option value="">Seleccione un doctor</option>
          @foreach($dentists as $d)
            <option value="{{ $d->id }}">{{ $d->user->name ?? 'Dr. '.$d->id }}</option>
          @endforeach
        </select>
        @error('dentist_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
      </div>

      <div>
        <label class="block text-sm font-medium mb-1 text-slate-700">Costo Total Estimado (Bs) *</label>
        <input type="number" step="0.01" name="estimate_total" id="estimate_total" class="w-full border rounded-lg px-3 py-2 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-sky-500 outline-none" required>
        @error('estimate_total') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
      </div>

      <div>
        <label class="block text-sm font-medium mb-1 text-slate-700">Número de Sesiones Estimadas *</label>
        <input type="number" min="1" name="total_sessions" class="w-full border rounded-lg px-3 py-2 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-sky-500 outline-none" placeholder="Ej. 3" required>
        @error('total_sessions') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
      </div>
      
      <div>
        <label class="block text-sm font-medium mb-1 text-slate-700">Código de Diente (Opcional)</label>
        <input type="text" name="tooth_code" class="w-full border rounded-lg px-3 py-2 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-sky-500 outline-none" placeholder="Ej. 18">
      </div>
      
      <div>
        <label class="block text-sm font-medium mb-1 text-slate-700">Superficie (Opcional)</label>
        <select name="surface" class="w-full border rounded-lg px-3 py-2 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-sky-500 outline-none">
          <option value="">N/A</option>
          <option value="O">Oclusal (O)</option>
          <option value="M">Mesial (M)</option>
          <option value="D">Distal (D)</option>
          <option value="B">Bucal/Vestibular (B)</option>
          <option value="L">Lingual/Palatino (L)</option>
          <option value="I">Incisal (I)</option>
        </select>
      </div>

      <div class="md:col-span-2">
        <label class="block text-sm font-medium mb-1 text-slate-700">Título Personalizado (Opcional)</label>
        <input name="title" class="w-full border rounded-lg px-3 py-2 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-sky-500 outline-none" placeholder="Si se deja vacío, se usará el nombre del servicio">
      </div>
    </div>

    <div class="mt-6 flex justify-end">
      <button class="btn btn-primary px-6">Crear Plan de Tratamiento</button>
    </div>
  </form>

  <script>
    document.getElementById('service_id').addEventListener('change', function() {
      const selected = this.options[this.selectedIndex];
      if (selected.value) {
        document.getElementById('estimate_total').value = selected.dataset.price;
      } else {
        document.getElementById('estimate_total').value = '';
      }
    });
  </script>
@endsection
