@extends('layouts.app')
@section('title','Editar plan')

@section('header-actions')
  <a href="{{ route('admin.patients.show', $plan->patient_id) }}" class="btn bg-slate-600 text-white hover:bg-slate-700 flex items-center gap-2 transition-colors">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
    </svg>
    Volver
</a>
  <a href="{{ route('admin.patients.plans.index', $plan->patient_id) }}" class="btn btn-ghost">Ver planes</a>
  <a href="{{ route('admin.plans.print',$plan) }}" class="btn btn-ghost">Imprimir</a>
  <a href="{{ route('admin.plans.pdf',$plan) }}" class="btn btn-ghost">PDF</a>
  <a href="{{ route('admin.plans.invoice.create',$plan) }}" class="btn btn-primary">Cobrar</a>
@endsection

@section('content')
<div class="grid gap-4">

  {{-- Encabezado / meta del plan --}}
  <section class="card">
    <form method="post" action="{{ route('admin.plans.update', $plan) }}" class="grid md:grid-cols-6 gap-3">
      @csrf @method('PUT')

      <div class="md:col-span-3">
        <label class="block text-xs text-slate-500 mb-1">Título</label>
        <input name="title" value="{{ old('title',$plan->title) }}" class="w-full border rounded px-3 py-2" required>
      </div>

      @if($plan->invoiceLatest)
  <div class="text-sm mb-2">
    Recibo: <a href="{{ route('admin.invoices.show',$plan->invoiceLatest) }}" class="text-blue-600 hover:underline">#{{ $plan->invoiceLatest->number }}</a>
    <span class="badge {{ $plan->invoiceLatest->status==='paid'?'bg-emerald-100 text-emerald-700':'bg-amber-100 text-amber-700' }}">
      {{ $plan->invoiceLatest->status==='paid'?'Pagada':'Emitida' }}
    </span>
  </div>
@endif


      <div>
        <label class="block text-xs text-slate-500 mb-1">Estado</label>
        <select name="status" class="w-full border rounded px-3 py-2">
          @foreach(['draft'=>'Borrador','approved'=>'Aprobado','in_progress'=>'En curso','closed'=>'Cerrado'] as $val=>$lbl)
            <option value="{{ $val }}" @selected(old('status',$plan->status)===$val)>{{ $lbl }}</option>
          @endforeach
        </select>
      </div>

      <div class="md:col-span-2">
        <label class="block text-xs text-slate-500 mb-1">Total estimado</label>
        <div class="px-3 py-2 rounded border bg-slate-50 font-semibold">
          Bs {{ number_format($plan->treatments->sum('price'),2) }}
        </div>
      </div>

      <div class="flex items-end gap-2">
        <button type="submit" name="action" value="save" class="btn btn-primary">
          <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
          </svg>
          Guardar cambios
        </button>
        @if($plan->status !== 'approved')
        <button type="submit" name="action" value="approve" class="btn btn-success">
          <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
          </svg>
          Aprobar plan y programar citas
        </button>
        @endif
      </div>
    </form>
    <div class="text-xs text-slate-500 mt-2">
      Paciente: <span class="font-medium">{{ $plan->patient?->last_name }}, {{ $plan->patient?->first_name }}</span>
      · Creado {{ $plan->created_at?->format('Y-m-d H:i') }}
    </div>
  </section>

  {{-- Alta de tratamientos --}}
  <section class="card">
    <h3 class="font-semibold mb-3">Añadir tratamiento</h3>
    <form action="{{ route('admin.plans.treatments.store', $plan) }}" method="post" class="grid md:grid-cols-5 gap-3 items-end">
      @csrf
      <div class="md:col-span-2">
        <label class="block text-xs text-slate-500 mb-1">Servicio</label>
        <select name="service_id" class="w-full border rounded px-2 py-2" required>
          @foreach($services as $s)
            <option value="{{ $s->id }}" data-default-price="{{ $s->price }}">
              {{ $s->name }} (Bs {{ number_format($s->price,2) }})
            </option>
          @endforeach
        </select>
      </div>

      <div>
        <label class="block text-xs text-slate-500 mb-1">Pieza (opcional)</label>
        <input type="text" name="tooth_code" class="border rounded px-2 py-2 w-full" placeholder="ej. 26">
      </div>

      <div>
        <label class="block text-xs text-slate-500 mb-1">Superficie</label>
        <select name="surface" class="border rounded px-2 py-2 w-full">
          <option value="">—</option>
          <option value="O">O</option><option value="M">M</option><option value="D">D</option>
          <option value="B">B</option><option value="L">L</option>
        </select>
      </div>

      <div>
        <label class="block text-xs text-slate-500 mb-1">Precio</label>
        <input type="number" step="0.01" min="0" name="price" class="border rounded px-2 py-2 w-full"
               value="{{ old('price') }}" placeholder="Auto">
      </div>

      <div class="md:col-span-5">
        <button class="btn btn-primary">Agregar</button>
      </div>
    </form>
  </section>

  {{-- Lista de tratamientos --}}
  <section class="card p-0">
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-white border-b sticky top-0 z-10">
          <tr class="text-left">
            <th class="px-3 py-2">#</th>
            <th class="px-3 py-2">Servicio</th>
            <th class="px-3 py-2">Pieza</th>
            <th class="px-3 py-2">Sup.</th>
            <th class="px-3 py-2">Estado</th>
            <th class="px-3 py-2 text-right">Precio</th>
            <th class="px-3 py-2 text-right">Acciones</th>
          </tr>
        </thead>
        <tbody>
          @forelse($plan->treatments as $i => $t)
            @php
              $badge = match($t->status){
                'planned'     => 'bg-slate-100 text-slate-700',
                'in_progress' => 'bg-amber-100 text-amber-700',
                'done'        => 'bg-emerald-100 text-emerald-700',
                default       => 'bg-slate-100 text-slate-700',
              };
            @endphp
            <tr class="border-b hover:bg-slate-50">
              <td class="px-3 py-2">{{ $i+1 }}</td>
              <td class="px-3 py-2 whitespace-nowrap">{{ $t->service?->name ?? '—' }}</td>
              <td class="px-3 py-2">{{ $t->tooth_code ?: '—' }}</td>
              <td class="px-3 py-2">{{ $t->surface ?: '—' }}</td>
              <td class="px-3 py-2">
                <span class="badge {{ $badge }}">{{ [
                  'planned'=>'Planificado','in_progress'=>'En curso','done'=>'Realizado'
                ][$t->status] ?? $t->status }}</span>
              </td>
              <td class="px-3 py-2 text-right">Bs {{ number_format($t->price,2) }}</td>
              <td class="px-3 py-2">
                <div class="flex gap-2">
                  <a href="{{ route('admin.plans.treatments.edit', [$plan, $t]) }}" class="btn btn-ghost">Editar</a>
                  <form action="{{ route('admin.plans.treatments.destroy', $t) }}" method="post"
                        onsubmit="return confirm('¿Eliminar tratamiento?');">
                    @csrf @method('DELETE')
                    <button class="btn btn-danger">Eliminar</button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td class="px-3 py-6 text-center text-slate-500" colspan="7">Aún no agregaste tratamientos.</td>
            </tr>
          @endforelse
        </tbody>

        @if($plan->treatments->count())
          <tfoot class="bg-slate-50">
            <tr>
              <td colspan="5" class="px-3 py-2 text-right font-medium">Total estimado</td>
              <td class="px-3 py-2 text-right font-semibold">Bs {{ number_format($plan->treatments->sum('price'),2) }}</td>
              <td></td>
            </tr>
          </tfoot>
        @endif
      </table>
    </div>
  </section>
</div>

{{-- Autollenado de precio desde el servicio seleccionado --}}
<script>
  document.addEventListener('change', function (e) {
    if (e.target.name === 'service_id') {
      const def = e.target.selectedOptions[0]?.dataset.defaultPrice;
      const $price = e.target.form.querySelector('input[name="price"]');
      if (def && $price && !$price.value) $price.value = def;
    }
  });
</script>
@endsection
