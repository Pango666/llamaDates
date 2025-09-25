@extends('layouts.app')
@section('title','Detalle de cita')

@section('header-actions')
  <a href="{{ route('admin.appointments.index') }}" class="btn btn-ghost">Volver</a>
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
  @endphp

  <div class="grid gap-4 md:grid-cols-3">
    {{-- ==================== Información principal ==================== --}}
    <section class="card md:col-span-2">
      <h3 class="font-semibold mb-3">Información</h3>

      <div class="grid gap-3 md:grid-cols-2">
        <div>
          <div class="text-xs text-slate-500">Paciente</div>
          <div class="font-medium">{{ $appointment->patient->last_name }}, {{ $appointment->patient->first_name }}</div>
        </div>
        <div>
          <div class="text-xs text-slate-500">Odontólogo</div>
          <div class="font-medium">{{ $appointment->dentist->name }}</div>
        </div>
        <div>
          <div class="text-xs text-slate-500">Servicio</div>
          <div class="font-medium">{{ $appointment->service->name }}</div>
        </div>
        <div>
          <div class="text-xs text-slate-500">Fecha y hora</div>
          <div class="font-medium">
            {{ \Illuminate\Support\Carbon::parse($appointment->date)->toDateString() }} ·
            {{ \Illuminate\Support\Str::substr($appointment->start_time,0,5) }}–{{ \Illuminate\Support\Str::substr($appointment->end_time,0,5) }}
          </div>
        </div>
        <div class="md:col-span-2">
          <div class="text-xs text-slate-500">Estado</div>
          <span class="badge {{ $badge }}">{{ [
            'reserved'=>'Reservado','confirmed'=>'Confirmado','in_service'=>'En atención',
            'done'=>'Atendido','no_show'=>'No asistió','canceled'=>'Cancelado'
          ][$appointment->status] ?? $appointment->status }}</span>
        </div>
        <div class="md:col-span-2">
          <div class="text-xs text-slate-500">Notas</div>
          <div class="mt-1 whitespace-pre-line">{{ $appointment->notes ?: '—' }}</div>
        </div>

        @if($appointment->status==='canceled')
          <div class="md:col-span-2">
            <div class="p-2 rounded bg-slate-50 text-slate-600 text-sm">
              Cancelada {{ $appointment->canceled_at ? 'el '.$appointment->canceled_at->format('Y-m-d H:i') : '' }}
              @if($appointment->canceled_reason) · Motivo: {{ $appointment->canceled_reason }} @endif
            </div>
          </div>
        @endif
      </div>
    </section>

    {{-- ==================== Acciones (estado + cobro + accesos) ==================== --}}
    <aside class="card space-y-4">
      {{-- Flujo rápido de estados --}}
      <div>
        <h3 class="font-semibold mb-2">Flujo rápido</h3>
        <form action="{{ route('admin.appointments.status',$appointment) }}" method="post" class="grid gap-2">
          @csrf
          <div class="grid grid-cols-2 gap-2">
            <button name="status" value="in_service" class="btn {{ $appointment->status==='in_service' ? 'btn-ghost' : '' }}"
              @disabled($appointment->status==='in_service')>Iniciar atención</button>

            <button name="status" value="done" class="btn"
              @disabled($appointment->status==='done')>Finalizar (Atendido)</button>
          </div>
          <div class="grid grid-cols-2 gap-2">
            <button name="status" value="no_show" class="btn btn-ghost"
              @disabled($appointment->status==='no_show')>No asistió</button>

            <button name="status" value="canceled" class="btn btn-danger"
              @disabled($appointment->status==='canceled')>Cancelar</button>
          </div>
        </form>
      </div>

      {{-- Cobro de la visita --}}
      <div class="border-t pt-3">
        <h3 class="font-semibold mb-2">Cobro de la visita</h3>

        @if($invoice)
          <div class="text-sm">
            <div><span class="text-slate-500">Factura:</span>
              <a class="text-blue-600 hover:underline" href="{{ route('admin.invoices.show',$invoice) }}">{{ $invoice->number }}</a>
            </div>
            <div><span class="text-slate-500">Estado:</span>
              <span class="badge {{ $isPaid ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                {{ $isPaid ? 'Pagada' : 'Pendiente' }}
              </span>
            </div>
            @if($totals)
              <div><span class="text-slate-500">Total:</span> Bs {{ number_format($totals['grand'],2) }}</div>
              <div><span class="text-slate-500">Pagado:</span> Bs {{ number_format($totals['paid'],2) }}</div>
              <div><span class="text-slate-500">Saldo:</span> Bs {{ number_format($totals['due'],2) }}</div>
            @endif
          </div>

          <div class="mt-2 flex gap-2">
            <a class="btn btn-ghost" href="{{ route('admin.invoices.show',$invoice) }}">Ver factura</a>
            @unless($isPaid)
              <a class="btn" href="{{ route('admin.invoices.show',$invoice) }}">Cobrar</a>
            @endunless
          </div>
        @else
          <p class="text-sm text-slate-600 mb-2">Aún no hay factura para esta cita.</p>
          @if(\Illuminate\Support\Facades\Route::has('admin.invoices.createFromAppointment'))
            <a class="btn" href="{{ route('admin.invoices.createFromAppointment',$appointment->id) }}">Facturar esta visita</a>
          @endif
        @endif
      </div>

      {{-- Accesos de la visita --}}
      <div class="border-t pt-3">
        <h3 class="font-semibold mb-2">Acciones de la visita</h3>
        <div class="flex flex-col gap-2">
          <a class="btn btn-ghost"
             href="{{ route('admin.odontograms.open', ['patient'=>$appointment->patient_id, 'appointment_id'=>$appointment->id]) }}">
            Odontograma de la visita
          </a>

          @if(\Illuminate\Support\Facades\Route::has('admin.notes.create'))
            <a class="btn btn-ghost"
               href="{{ route('admin.notes.create', ['patient_id'=>$appointment->patient_id, 'appointment_id'=>$appointment->id]) }}">
              + Nota clínica (SOAP)
            </a>
          @endif

          <a class="btn btn-ghost"
             href="{{ route('admin.patients.consents.create', ['patient'=>$appointment->patient_id, 'appointment_id'=>$appointment->id]) }}">
            + Nuevo consentimiento (PDF)
          </a>
          <a class="btn btn-ghost"
             href="{{ route('admin.patients.consents.index', $appointment->patient_id) }}">
            Ver consentimientos del paciente
          </a>
        </div>
      </div>
    </aside>

    {{-- ==================== Notas clínicas (SOAP) ==================== --}}
    <section class="card md:col-span-3">
      <div class="flex items-center justify-between mb-2">
        <h3 class="font-semibold">Notas clínicas</h3>
      </div>

      @if($canEdit)
        <form method="post" action="{{ route('admin.appointments.notes.store',$appointment) }}" class="grid md:grid-cols-2 gap-3 mb-4">
          @csrf
          <input type="hidden" name="type" value="SOAP">
          <div>
            <label class="block text-xs text-slate-500 mb-1">Subjective</label>
            <textarea name="subjective" rows="2" class="w-full border rounded px-3 py-2" placeholder="Motivo de consulta, dolor, etc."></textarea>
          </div>
          <div>
            <label class="block text-xs text-slate-500 mb-1">Objective</label>
            <textarea name="objective" rows="2" class="w-full border rounded px-3 py-2" placeholder="Hallazgos clínicos..."></textarea>
          </div>
          <div>
            <label class="block text-xs text-slate-500 mb-1">Assessment</label>
            <textarea name="assessment" rows="2" class="w-full border rounded px-3 py-2" placeholder="Impresión diagnóstica..."></textarea>
          </div>
          <div>
            <label class="block text-xs text-slate-500 mb-1">Plan</label>
            <textarea name="plan" rows="2" class="w-full border rounded px-3 py-2" placeholder="Tratamiento propuesto..."></textarea>
          </div>

          {{-- Vitales --}}
          <div class="md:col-span-2 grid grid-cols-2 md:grid-cols-4 gap-2">
            <div>
              <label class="block text-xs text-slate-500 mb-1">PA</label>
              <input name="vitals[bp]" class="w-full border rounded px-3 py-2" placeholder="120/80">
            </div>
            <div>
              <label class="block text-xs text-slate-500 mb-1">Temp (°C)</label>
              <input name="vitals[temp]" class="w-full border rounded px-3 py-2" placeholder="36.7">
            </div>
            <div>
              <label class="block text-xs text-slate-500 mb-1">FC</label>
              <input name="vitals[hr]" class="w-full border rounded px-3 py-2" placeholder="75">
            </div>
            <div>
              <label class="block text-xs text-slate-500 mb-1">SpO2</label>
              <input name="vitals[spo2]" class="w-full border rounded px-3 py-2" placeholder="98%">
            </div>
          </div>

          <div class="md:col-span-2">
            <button class="btn btn-primary">Guardar nota</button>
          </div>
        </form>
      @else
        <p class="text-xs text-slate-500 mb-3">Para registrar notas, primero <b>Inicia la atención</b>.</p>
      @endif

      <div class="space-y-3">
        @forelse($notes as $n)
          <div class="border rounded p-3">
            <div class="text-xs text-slate-500 mb-1">
              {{ $n->created_at->format('Y-m-d H:i') }}
              @if($n->author) · {{ $n->author->name }} @endif
            </div>
            @if($n->subjective)<div><span class="text-xs text-slate-500">S:</span> {{ $n->subjective }}</div>@endif
            @if($n->objective) <div><span class="text-xs text-slate-500">O:</span> {{ $n->objective }}</div>@endif
            @if($n->assessment)<div><span class="text-xs text-slate-500">A:</span> {{ $n->assessment }}</div>@endif
            @if($n->plan)      <div><span class="text-xs text-slate-500">P:</span> {{ $n->plan }}</div>@endif
            @if($n->vitals)
              <div class="mt-1 text-xs text-slate-600">Vitales:
                @foreach($n->vitals as $k=>$v) <span class="px-1">{{ $k }}: {{ $v }}</span> @endforeach
              </div>
            @endif

            @if($canEdit)
              <form method="post" action="{{ route('admin.notes.destroy',$n) }}" onsubmit="return confirm('¿Eliminar nota?');" class="mt-2">
                @csrf @method('DELETE')
                <button class="btn btn-ghost">Eliminar</button>
              </form>
            @endif
          </div>
        @empty
          <div class="text-sm text-slate-500">Sin notas.</div>
        @endforelse
      </div>
    </section>

    {{-- ==================== Diagnósticos ==================== --}}
    <section class="card md:col-span-3">
      <div class="flex items-center justify-between mb-2">
        <h3 class="font-semibold">Diagnósticos</h3>
      </div>

      @if($canEdit)
        <form method="post" action="{{ route('admin.appointments.diagnoses.store',$appointment) }}" class="grid md:grid-cols-6 gap-2 mb-3">
          @csrf
          <div class="md:col-span-2">
            <label class="block text-xs text-slate-500 mb-1">Etiqueta</label>
            <input name="label" class="w-full border rounded px-2 py-2" placeholder="Caries dental" required>
          </div>
          <div>
            <label class="block text-xs text-slate-500 mb-1">Código</label>
            <input name="code" class="w-full border rounded px-2 py-2" placeholder="K02.1">
          </div>
          <div>
            <label class="block text-xs text-slate-500 mb-1">Pieza</label>
            <input name="tooth_code" class="w-full border rounded px-2 py-2" placeholder="26">
          </div>
          <div>
            <label class="block text-xs text-slate-500 mb-1">Superficie</label>
            <select name="surface" class="w-full border rounded px-2 py-2">
              <option value="">—</option>
              <option>O</option><option>M</option><option>D</option>
              <option>B</option><option>L</option><option>I</option>
            </select>
          </div>
          <div>
            <label class="block text-xs text-slate-500 mb-1">Estado</label>
            <select name="status" class="w-full border rounded px-2 py-2">
              <option value="active">Activo</option>
              <option value="resolved">Resuelto</option>
            </select>
          </div>
          <div class="md:col-span-6">
            <input name="notes" class="w-full border rounded px-2 py-2" placeholder="Notas (opcional)">
          </div>
          <div class="md:col-span-6">
            <button class="btn btn-ghost">Agregar diagnóstico</button>
          </div>
        </form>
      @else
        <p class="text-xs text-slate-500 mb-3">Para agregar diagnósticos, primero <b>Inicia la atención</b>.</p>
      @endif

      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="border-b">
            <tr class="text-left">
              <th class="px-3 py-2">Fecha</th>
              <th class="px-3 py-2">Dx</th>
              <th class="px-3 py-2">Pieza</th>
              <th class="px-3 py-2">Estado</th>
              <th class="px-3 py-2">Notas</th>
              <th class="px-3 py-2 text-right">Acciones</th>
            </tr>
          </thead>
          <tbody>
            @forelse($diagnoses as $d)
              <tr class="border-b">
                <td class="px-3 py-2">{{ $d->created_at->format('Y-m-d H:i') }}</td>
                <td class="px-3 py-2">
                  {{ $d->label }}
                  @if($d->code) <span class="text-xs text-slate-500">({{ $d->code }})</span> @endif
                </td>
                <td class="px-3 py-2">
                  {{ $d->tooth_code ?: '—' }} @if($d->surface) · {{ $d->surface }} @endif
                </td>
                <td class="px-3 py-2">{{ $d->status==='active'?'Activo':'Resuelto' }}</td>
                <td class="px-3 py-2">{{ $d->notes ?: '—' }}</td>
                <td class="px-3 py-2 text-right">
                  @if($canEdit)
                    <form method="post" action="{{ route('admin.diagnoses.destroy',$d) }}" onsubmit="return confirm('¿Eliminar diagnóstico?');">
                      @csrf @method('DELETE')
                      <button class="btn btn-ghost">Eliminar</button>
                    </form>
                  @endif
                </td>
              </tr>
            @empty
              <tr><td colspan="6" class="px-3 py-6 text-center text-slate-500">Sin diagnósticos.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </section>

    {{-- ==================== Adjuntos ==================== --}}
    <section class="card md:col-span-3">
      <div class="flex items-center justify-between mb-2">
        <h3 class="font-semibold">Adjuntos</h3>
      </div>

      @if($canEdit)
        <form method="post" action="{{ route('admin.appointments.attachments.store',$appointment) }}" enctype="multipart/form-data" class="flex flex-col md:flex-row gap-2 mb-3">
          @csrf
          <input type="file" name="files[]" multiple class="border rounded px-2 py-2" accept="image/*,application/pdf">
          <input type="text" name="notes" class="border rounded px-2 py-2 flex-1" placeholder="Notas (opcional)">
          <select name="type" class="border rounded px-2 py-2">
            <option value="">Tipo</option>
            <option value="xray">Radiografía</option>
            <option value="photo">Foto</option>
            <option value="pdf">PDF</option>
            <option value="doc">Doc</option>
          </select>
          <button class="btn btn-ghost">Subir</button>
        </form>
      @else
        <p class="text-xs text-slate-500 mb-3">Para subir archivos, primero <b>Inicia la atención</b>.</p>
      @endif

      <div class="grid md:grid-cols-2 gap-3">
        @forelse($attachments as $a)
          <div class="border rounded p-3">
            <div class="text-xs text-slate-500 mb-1">
              {{ $a->created_at->format('Y-m-d H:i') }} · {{ strtoupper($a->type ?: 'file') }}
            </div>
            <div class="font-medium break-words">{{ $a->original_name }}</div>
            @if($a->notes)<div class="text-sm text-slate-600">{{ $a->notes }}</div>@endif
            <div class="mt-2 flex gap-2">
              <a class="btn btn-ghost" href="{{ asset('storage/'.$a->path) }}" target="_blank">Ver</a>
              @if($canEdit)
                <form method="post" action="{{ route('admin.attachments.destroy',$a) }}" onsubmit="return confirm('¿Eliminar archivo?');">
                  @csrf @method('DELETE')
                  <button class="btn btn-danger">Eliminar</button>
                </form>
              @endif
            </div>
          </div>
        @empty
          <div class="text-sm text-slate-500">Sin adjuntos.</div>
        @endforelse
      </div>
    </section>
  </div>
@endsection
