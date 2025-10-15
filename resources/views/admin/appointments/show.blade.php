@extends('layouts.app')
@section('title','Detalle de cita')

@section('header-actions')
  <a href="{{ route('admin.appointments.index') }}" class="btn btn-ghost">← Volver</a>
@endsection

@section('content')
  @php
    // -------- Estado, badges y permisos de edición ----------
    $badge = [
      'reserved'   => 'bg-slate-100 text-slate-700',
      'confirmed'  => 'bg-blue-100 text-blue-700',
      'in_service' => 'bg-amber-100 text-amber-700',
      'done'       => 'bg-emerald-100 text-emerald-700',
      'no_show'    => 'bg-rose-100 text-rose-700',
      'canceled'   => 'bg-slate-200 text-slate-700 line-through',
    ][$appointment->status] ?? 'bg-slate-100 text-slate-700';

    // Solo se edita cuando la cita está "En atención"
    $canEdit = $appointment->status === 'in_service';

    // -------- Fallbacks por si el controlador no los pasa ----------
    $notes = $notes
      ?? \App\Models\ClinicalNote::where('appointment_id',$appointment->id)->with('author')->orderByDesc('created_at')->get();

    $diagnoses = $diagnoses
      ?? \App\Models\Diagnosis::where('appointment_id',$appointment->id)->orderByDesc('created_at')->get();

    $attachments = $attachments
      ?? \App\Models\Attachment::where('appointment_id',$appointment->id)->orderByDesc('created_at')->get();

    // Factura y totales (fallback)
    if (!isset($invoice)) {
      $invoice = \App\Models\Invoice::with(['items','payments'])
        ->where('appointment_id',$appointment->id)->latest()->first();
    }
    $totals = null; $isPaid = false;
    if ($invoice) {
      $subtotal = $invoice->items->sum('total');
      $discount = (float) $invoice->discount;
      $taxPct   = (float) $invoice->tax_percent;
      $base     = max($subtotal - $discount, 0);
      $grand    = $base + ($base * $taxPct / 100);
      $paid     = $invoice->payments->sum('amount');
      $due      = max($grand - $paid, 0);
      $totals   = compact('subtotal','base','grand','paid','due');
      $isPaid   = ($invoice->status === 'paid') || $due <= 0.0001;
    }

    // Fallback de suministros usados
    $usedSupplies = $usedSupplies
      ?? \App\Models\AppointmentSupply::with(['product','location'])
           ->where('appointment_id',$appointment->id)->latest()->get();
  @endphp

  {{-- ==================== HEADER CON INFORMACIÓN PRINCIPAL ==================== --}}
  <div class="bg-white rounded-lg shadow-sm border p-4 mb-4">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
      {{-- Información principal --}}
      <div class="flex-1">
        <div class="flex items-center gap-3 mb-2">
          <h1 class="text-xl font-bold text-slate-800">Cita #{{ $appointment->id }}</h1>
          <span class="badge {{ $badge }} text-sm font-medium">
            {{ [
              'reserved'=>'🟡 Reservado',
              'confirmed'=>'🔵 Confirmado', 
              'in_service'=>'🟠 En atención',
              'done'=>'🟢 Atendido',
              'no_show'=>'🔴 No asistió',
              'canceled'=>'⚫ Cancelado'
            ][$appointment->status] ?? $appointment->status }}
          </span>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
          <div>
            <div class="text-xs text-slate-500 mb-1">👤 Paciente</div>
            <div class="font-semibold">{{ $appointment->patient->last_name }}, {{ $appointment->patient->first_name }}</div>
          </div>
          <div>
            <div class="text-xs text-slate-500 mb-1">🦷 Odontólogo</div>
            <div class="font-semibold">{{ $appointment->dentist->name }}</div>
          </div>
          <div>
            <div class="text-xs text-slate-500 mb-1">📅 Fecha y hora</div>
            <div class="font-semibold">
              {{ \Illuminate\Support\Carbon::parse($appointment->date)->toDateString() }} · 
              {{ \Illuminate\Support\Str::substr($appointment->start_time,0,5) }}–{{ \Illuminate\Support\Str::substr($appointment->end_time,0,5) }}
            </div>
          </div>
        </div>
      </div>

      {{-- Acciones rápidas --}}
      <div class="flex flex-col gap-2">
        <form action="{{ route('admin.appointments.status',$appointment) }}" method="post" class="flex gap-2">
          @csrf
          @if($appointment->status === 'confirmed')
            <button name="status" value="in_service" class="btn bg-orange-500 text-white hover:bg-orange-600">
              🟠 Iniciar atención
            </button>
          @elseif($appointment->status === 'in_service')
            <button name="status" value="done" class="btn bg-green-500 text-white hover:bg-green-600">
              ✅ Finalizar atención
            </button>
          @endif
          
          @if(!in_array($appointment->status, ['done', 'canceled', 'no_show']))
            <button name="status" value="canceled" class="btn btn-ghost border border-red-200 text-red-600 hover:bg-red-50">
              ❌ Cancelar
            </button>
          @endif
        </form>
        
        @if($invoice)
          <a href="{{ route('admin.invoices.show',$invoice) }}" class="btn btn-ghost text-center border border-blue-200">
            💰 Ver factura
          </a>
        @else
          <a href="{{ route('admin.invoices.createFromAppointment',$appointment->id) }}" class="btn btn-ghost text-center border border-green-200">
            📄 Crear factura
          </a>
        @endif
      </div>
    </div>

    {{-- Servicio y notas --}}
    <div class="mt-4 pt-4 border-t border-slate-200">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <div class="text-xs text-slate-500 mb-1">🛠️ Servicio</div>
          <div class="font-medium">{{ $appointment->service->name }}</div>
        </div>
        <div>
          <div class="text-xs text-slate-500 mb-1">📝 Notas de la cita</div>
          <div class="text-sm">{{ $appointment->notes ?: 'Sin notas adicionales' }}</div>
        </div>
      </div>
    </div>
  </div>

  {{-- ==================== SECCIÓN DE ATENCIÓN CLÍNICA ==================== --}}
  @if($appointment->status === 'in_service' || $notes->count() > 0 || $diagnoses->count() > 0)
  <div class="grid gap-4 md:grid-cols-2">
    {{-- Notas clínicas --}}
    <section class="card">
      <div class="flex items-center justify-between mb-4">
        <h3 class="font-semibold flex items-center gap-2">
          📋 Notas clínicas
          @if(!$canEdit)
            <span class="text-xs bg-slate-100 text-slate-600 px-2 py-1 rounded">Solo lectura</span>
          @endif
        </h3>
        @if($canEdit && \Illuminate\Support\Facades\Route::has('admin.notes.create'))
          <a href="{{ route('admin.notes.create', ['patient_id'=>$appointment->patient_id, 'appointment_id'=>$appointment->id]) }}" 
             class="btn btn-ghost text-sm">
            + Nueva nota
          </a>
        @endif
      </div>

      @if($canEdit)
        <form method="post" action="{{ route('admin.appointments.notes.store',$appointment) }}" class="mb-4 p-3 bg-slate-50 rounded-lg">
          @csrf
          <input type="hidden" name="type" value="SOAP">
          
          <div class="grid gap-2 mb-3">
            <div>
              <label class="block text-xs font-medium text-slate-600 mb-1">S - Subjetivo</label>
              <textarea name="subjective" rows="2" class="w-full border rounded px-3 py-2 text-sm" 
                        placeholder="Lo que el paciente reporta..."></textarea>
            </div>
            <div>
              <label class="block text-xs font-medium text-slate-600 mb-1">O - Objetivo</label>
              <textarea name="objective" rows="2" class="w-full border rounded px-3 py-2 text-sm" 
                        placeholder="Hallazgos clínicos..."></textarea>
            </div>
            <div>
              <label class="block text-xs font-medium text-slate-600 mb-1">A - Evaluación</label>
              <textarea name="assessment" rows="2" class="w-full border rounded px-3 py-2 text-sm" 
                        placeholder="Impresión diagnóstica..."></textarea>
            </div>
            <div>
              <label class="block text-xs font-medium text-slate-600 mb-1">P - Plan</label>
              <textarea name="plan" rows="2" class="w-full border rounded px-3 py-2 text-sm" 
                        placeholder="Plan de tratamiento..."></textarea>
            </div>
          </div>

          <div class="flex gap-2">
            <button class="btn bg-blue-500 text-white hover:bg-blue-600 text-sm">💾 Guardar nota</button>
            <button type="button" onclick="this.form.reset()" class="btn btn-ghost text-sm">🔄 Limpiar</button>
          </div>
        </form>
      @endif

      <div class="space-y-3 max-h-96 overflow-y-auto">
        @forelse($notes as $n)
          <div class="border rounded-lg p-3 bg-white">
            <div class="flex justify-between items-start mb-2">
              <div class="text-xs text-slate-500">
                {{ $n->created_at->format('d/m H:i') }}
                @if($n->author) · {{ $n->author->name }} @endif
              </div>
              @if($canEdit)
                <form method="post" action="{{ route('admin.notes.destroy',$n) }}" 
                      onsubmit="return confirm('¿Eliminar nota?');" class="inline">
                  @csrf @method('DELETE')
                  <button class="text-red-500 hover:text-red-700 text-xs">🗑️</button>
                </form>
              @endif
            </div>
            
            <div class="space-y-1 text-sm">
              @if($n->subjective)<div><span class="font-medium text-slate-600">S:</span> {{ $n->subjective }}</div>@endif
              @if($n->objective) <div><span class="font-medium text-slate-600">O:</span> {{ $n->objective }}</div>@endif
              @if($n->assessment)<div><span class="font-medium text-slate-600">A:</span> {{ $n->assessment }}</div>@endif
              @if($n->plan)      <div><span class="font-medium text-slate-600">P:</span> {{ $n->plan }}</div>@endif
            </div>
          </div>
        @empty
          <div class="text-center py-4 text-slate-500">
            <p>No hay notas clínicas registradas</p>
          </div>
        @endforelse
      </div>
    </section>

    {{-- Diagnósticos --}}
    <section class="card">
      <div class="flex items-center justify-between mb-4">
        <h3 class="font-semibold flex items-center gap-2">
          🩺 Diagnósticos
          @if(!$canEdit)
            <span class="text-xs bg-slate-100 text-slate-600 px-2 py-1 rounded">Solo lectura</span>
          @endif
        </h3>
      </div>

      @if($canEdit)
        <form method="post" action="{{ route('admin.appointments.diagnoses.store',$appointment) }}" class="mb-4 p-3 bg-slate-50 rounded-lg">
          @csrf
          <div class="grid grid-cols-2 gap-2 mb-2">
            <div class="col-span-2">
              <input name="label" class="w-full border rounded px-3 py-2 text-sm" 
                     placeholder="Diagnóstico (ej: Caries dental)" required>
            </div>
            <div>
              <input name="code" class="w-full border rounded px-3 py-2 text-sm" 
                     placeholder="Código CIE-10">
            </div>
            <div>
              <input name="tooth_code" class="w-full border rounded px-3 py-2 text-sm" 
                     placeholder="Pieza (ej: 26)">
            </div>
            <div>
              <select name="surface" class="w-full border rounded px-3 py-2 text-sm">
                <option value="">Superficie</option>
                <option>O</option><option>M</option><option>D</option>
                <option>B</option><option>L</option><option>I</option>
              </select>
            </div>
            <div>
              <select name="status" class="w-full border rounded px-3 py-2 text-sm">
                <option value="active">Activo</option>
                <option value="resolved">Resuelto</option>
              </select>
            </div>
          </div>
          <div class="flex gap-2">
            <input name="notes" class="flex-1 border rounded px-3 py-2 text-sm" 
                   placeholder="Notas adicionales (opcional)">
            <button class="btn bg-green-500 text-white hover:bg-green-600 text-sm">➕ Agregar</button>
          </div>
        </form>
      @endif

      <div class="space-y-2 max-h-96 overflow-y-auto">
        @forelse($diagnoses as $d)
          <div class="border rounded-lg p-3 bg-white">
            <div class="flex justify-between items-start mb-1">
              <div class="font-medium text-sm">{{ $d->label }}</div>
              @if($canEdit)
                <form method="post" action="{{ route('admin.diagnoses.destroy',$d) }}" 
                      onsubmit="return confirm('¿Eliminar diagnóstico?');">
                  @csrf @method('DELETE')
                  <button class="text-red-500 hover:text-red-700 text-xs">🗑️</button>
                </form>
              @endif
            </div>
            
            <div class="text-xs text-slate-600 space-y-1">
              @if($d->code)<div><span class="font-medium">CIE-10:</span> {{ $d->code }}</div>@endif
              @if($d->tooth_code)
                <div><span class="font-medium">Pieza:</span> {{ $d->tooth_code }} @if($d->surface)· {{ $d->surface }}@endif</div>
              @endif
              <div class="flex justify-between">
                <span class="badge {{ $d->status==='active' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700' }} text-xs">
                  {{ $d->status==='active'?'Activo':'Resuelto' }}
                </span>
                <span class="text-slate-500">{{ $d->created_at->format('d/m H:i') }}</span>
              </div>
              @if($d->notes)<div class="mt-1">{{ $d->notes }}</div>@endif
            </div>
          </div>
        @empty
          <div class="text-center py-4 text-slate-500">
            <p>No hay diagnósticos registrados</p>
          </div>
        @endforelse
      </div>
    </section>
  </div>
  @endif

  {{-- ==================== SECCIÓN DE RECURSOS ==================== --}}
  <div class="grid gap-4 md:grid-cols-3 mt-4">
    {{-- Suministros usados --}}
    <section class="card md:col-span-2">
      <div class="flex items-center justify-between mb-4">
        <h3 class="font-semibold">📦 Suministros usados</h3>
        @if($canEdit)
          <button onclick="document.getElementById('supply-form').classList.toggle('hidden')" 
                  class="btn btn-ghost text-sm">
            ➕ Agregar suministro
          </button>
        @endif
      </div>

      @php
        $sups = $sups ?? \App\Models\AppointmentSupply::with(['appointment','product','location'])
                  ->where('appointment_id',$appointment->id)->orderByDesc('id')->get();
        $sumCost = $sups->sum(fn($x) => (float)$x->unit_cost_at_issue * (float)$x->qty);
      @endphp

      {{-- @if($canEdit)
        <form id="supply-form" method="post" action="{{ route('admin.appointments.supplies.store',$appointment) }}" 
              class="hidden mb-4 p-3 bg-slate-50 rounded-lg">
          @csrf
          <div class="grid grid-cols-2 md:grid-cols-4 gap-2 mb-2">
            <select name="product_id" class="col-span-2 border rounded px-2 py-2 text-sm" required>
              <option value="">Producto...</option>
              @foreach($products as $p)
                <option value="{{ $p->id }}">{{ $p->name }}</option>
              @endforeach
            </select>
            <input name="qty" type="number" step="0.001" min="0.001" class="border rounded px-2 py-2 text-sm" 
                   placeholder="Cantidad" required>
            <input name="unit_cost" type="number" step="0.0001" min="0" class="border rounded px-2 py-2 text-sm" 
                   placeholder="Costo unit.">
          </div>
          <div class="flex gap-2">
            <button class="btn bg-blue-500 text-white hover:bg-blue-600 text-sm flex-1">💾 Guardar</button>
            <button type="button" onclick="document.getElementById('supply-form').classList.add('hidden')" 
                    class="btn btn-ghost text-sm">❌ Cancelar</button>
          </div>
        </form>
      @endif --}}

      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="border-b bg-slate-50">
            <tr>
              <th class="px-3 py-2 text-left">Producto</th>
              <th class="px-3 py-2 text-right">Cantidad</th>
              <th class="px-3 py-2 text-right">Costo unit.</th>
              <th class="px-3 py-2 text-right">Total</th>
              @if($canEdit)<th class="px-3 py-2 text-right">Acciones</th>@endif
            </tr>
          </thead>
          <tbody>
            @forelse($sups as $s)
              @php
                $u = (float)$s->unit_cost_at_issue;
                $q = (float)$s->qty;
              @endphp
              <tr class="border-b hover:bg-slate-50">
                <td class="px-3 py-2">
                  <div class="font-medium">{{ $s->product->name ?? '#'.$s->product_id }}</div>
                  <div class="text-xs text-slate-500">
                    {{ $s->location->name ?? '—' }}@if($s->lot) · Lote: {{ $s->lot }}@endif
                  </div>
                </td>
                <td class="px-3 py-2 text-right">{{ rtrim(rtrim(number_format($q,3,'.',''), '0'),'.') }}</td>
                <td class="px-3 py-2 text-right">{{ number_format($u, 4) }}</td>
                <td class="px-3 py-2 text-right font-medium">{{ number_format($u*$q, 2) }}</td>
                @if($canEdit)
                  <td class="px-3 py-2 text-right">
                    {{-- <form method="post" action="{{ route('admin.appointments.supplies.destroy', [$appointment, $s]) }}"
                          onsubmit="return confirm('¿Eliminar suministro?');" class="inline">
                      @csrf @method('DELETE')
                      <button class="text-red-500 hover:text-red-700 text-xs">🗑️</button>
                    </form> --}}
                  </td>
                @endif
              </tr>
            @empty
              <tr>
                <td colspan="{{ $canEdit ? 5 : 4 }}" class="px-3 py-4 text-center text-slate-500">
                  No hay suministros registrados
                </td>
              </tr>
            @endforelse
          </tbody>
          @if($sups->count())
            <tfoot class="bg-slate-50 font-medium">
              <tr>
                <td colspan="{{ $canEdit ? 3 : 2 }}" class="px-3 py-2 text-right">Total:</td>
                <td class="px-3 py-2 text-right">{{ number_format($sumCost,2) }}</td>
                @if($canEdit)<td></td>@endif
              </tr>
            </tfoot>
          @endif
        </table>
      </div>
    </section>

    {{-- Panel de acciones rápidas --}}
    <aside class="card">
      <h3 class="font-semibold mb-3">🚀 Acciones rápidas</h3>
      
      <div class="space-y-2">
        <a class="btn btn-ghost w-full justify-start text-sm"
           href="{{ route('admin.odontograms.open', ['patient'=>$appointment->patient_id, 'appointment_id'=>$appointment->id]) }}">
          🦷 Odontograma
        </a>

        <a class="btn btn-ghost w-full justify-start text-sm"
           href="{{ route('admin.patients.consents.create', ['patient'=>$appointment->patient_id, 'appointment_id'=>$appointment->id]) }}">
          📄 Consentimiento PDF
        </a>

        <a class="btn btn-ghost w-full justify-start text-sm"
           href="{{ route('admin.patients.consents.index', $appointment->patient_id) }}">
          📋 Ver consentimientos
        </a>

        @if($invoice)
          <div class="border-t pt-2 mt-2">
            <div class="text-xs text-slate-500 mb-1">💰 Estado de factura</div>
            <div class="text-sm font-medium {{ $isPaid ? 'text-green-600' : 'text-orange-600' }}">
              {{ $isPaid ? '✅ Pagada' : '🟡 Pendiente' }}
            </div>
            @if($totals)
              <div class="text-xs text-slate-600 mt-1">
                Total: Bs {{ number_format($totals['grand'],2) }}<br>
                Saldo: Bs {{ number_format($totals['due'],2) }}
              </div>
            @endif
          </div>
        @endif
      </div>
    </aside>
  </div>

  {{-- ==================== ARCHIVOS ADJUNTOS ==================== --}}
  @if($attachments->count() > 0 || $canEdit)
  <section class="card mt-4">
    <div class="flex items-center justify-between mb-4">
      <h3 class="font-semibold">📎 Archivos adjuntos</h3>
      @if($canEdit)
        <button onclick="document.getElementById('attachment-form').classList.toggle('hidden')" 
                class="btn btn-ghost text-sm">
          📤 Subir archivos
        </button>
      @endif
    </div>

    @if($canEdit)
      <form id="attachment-form" method="post" action="{{ route('admin.appointments.attachments.store',$appointment) }}" 
            enctype="multipart/form-data" class="hidden mb-4 p-3 bg-slate-50 rounded-lg">
        @csrf
        <div class="flex flex-col md:flex-row gap-2">
          <input type="file" name="files[]" multiple class="flex-1 border rounded px-2 py-2 text-sm" 
                 accept="image/*,application/pdf">
          <input type="text" name="notes" class="flex-1 border rounded px-2 py-2 text-sm" 
                 placeholder="Descripción (opcional)">
          <button class="btn bg-blue-500 text-white hover:bg-blue-600 text-sm">📤 Subir</button>
        </div>
      </form>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
      @forelse($attachments as $a)
        <div class="border rounded-lg p-3 hover:bg-slate-50">
          <div class="flex justify-between items-start mb-2">
            <div class="font-medium text-sm truncate">{{ $a->original_name }}</div>
            @if($canEdit)
              <form method="post" action="{{ route('admin.attachments.destroy',$a) }}" 
                    onsubmit="return confirm('¿Eliminar archivo?');">
                @csrf @method('DELETE')
                <button class="text-red-500 hover:text-red-700 text-xs">🗑️</button>
              </form>
            @endif
          </div>
          
          <div class="text-xs text-slate-500 mb-2">
            {{ $a->created_at->format('d/m H:i') }} · 
            {{ strtoupper($a->type ?: 'archivo') }}
          </div>
          
          <div class="flex gap-2">
            <a class="btn btn-ghost text-xs" href="{{ asset('storage/'.$a->path) }}" target="_blank">
              👁️ Ver
            </a>
            @if($a->notes)
              <span class="text-xs text-slate-600 flex-1 truncate">{{ $a->notes }}</span>
            @endif
          </div>
        </div>
      @empty
        <div class="col-span-2 text-center py-4 text-slate-500">
          <p>No hay archivos adjuntos</p>
        </div>
      @endforelse
    </div>
  </section>
  @endif

  {{-- ==================== ALERTA SI NO SE PUEDE EDITAR ==================== --}}
  @if(!$canEdit && in_array($appointment->status, ['reserved', 'confirmed']))
    <div class="mt-4 p-4 bg-amber-50 border border-amber-200 rounded-lg">
      <div class="flex items-center gap-3">
        <div class="text-amber-500 text-xl">💡</div>
        <div>
          <div class="font-medium text-amber-800">Para editar esta cita necesitas iniciar la atención</div>
          <div class="text-sm text-amber-600 mt-1">
            Usa el botón "🟠 Iniciar atención" en la parte superior para habilitar la edición de notas, diagnósticos y suministros.
          </div>
        </div>
      </div>
    </div>
  @endif
@endsection