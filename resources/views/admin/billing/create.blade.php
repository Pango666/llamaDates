@extends('layouts.app')
@section('title', 'Nuevo Recibo')

@section('header-actions')
  <a href="{{ route('admin.billing') }}" class="btn bg-slate-600 text-white hover:bg-slate-700 flex items-center gap-2 transition-colors">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
    </svg>
    Volver a Recibos
  </a>
@endsection

@section('content')
  <div class="max-w-7xl mx-auto">
    {{-- Header --}}
    <div class="card mb-6">
      <div class="border-b border-slate-200 pb-4">
        <h1 class="text-xl font-semibold text-slate-800 flex items-center gap-2">
          <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
          Crear Nuevo Recibo (Presencial)
        </h1>
        <p class="text-sm text-slate-600 mt-1">
          Cada fila de servicios generará una cita pagada para el paciente.
        </p>
      </div>
    </div>

    @php
      // Para autocompletar por CI si quieres (ya estaba)
      $patientsByCi = $patients
        ->whereNotNull('ci')
        ->mapWithKeys(function ($p) {
          return [
            $p->ci => [
              'id'         => $p->id,
              'first_name' => $p->first_name,
              'last_name'  => $p->last_name,
              'phone'      => $p->phone,
            ],
          ];
        });

      $servicesForJs = $services->map(function ($s) {
        return [
          'id'    => $s->id,
          'name'  => $s->name,
          'price' => $s->price,
        ];
      });

      $dentistsForJs = $dentists->map(function ($d) {
        return [
          'id'   => $d->id,
          'name' => $d->name,
        ];
      });
    @endphp

    {{-- datalists compartidos --}}
    <datalist id="patients_list">
      @foreach($patients as $p)
        <option
          value="{{ trim(($p->first_name ?? '').' '.($p->last_name ?? '')) }}@if($p->ci) · CI {{ $p->ci }}@endif"
          data-id="{{ $p->id }}"
        ></option>
      @endforeach
    </datalist>

    <datalist id="services_list">
      @foreach($services as $svc)
        <option
          value="{{ $svc->name }}"
          data-id="{{ $svc->id }}"
          data-price="{{ $svc->price ?? 0 }}"
        ></option>
      @endforeach
    </datalist>

    <datalist id="dentists_list">
      @foreach($dentists as $dentist)
        <option
          value="{{ $dentist->name }}"
          data-id="{{ $dentist->id }}"
        ></option>
      @endforeach
    </datalist>

    <form method="post" action="{{ route('admin.billing.store') }}" id="invoice-form">
      @csrf

      {{-- Datos de Paciente y Recibo --}}
      <div class="card mb-6">
        <h3 class="font-semibold text-slate-800 mb-4 flex items-center gap-2">
          <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
          </svg>
          Datos del Paciente y Recibo
        </h3>
        
        <div class="grid gap-4 md:grid-cols-3">
          {{-- Paciente existente (con buscador en el mismo campo) --}}
          <div class="md:col-span-2 space-y-2">
            <label class="block text-sm font-medium text-slate-700">
              Paciente (opcional)
            </label>

            <input
              type="text"
              id="patient_search"
              class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
              placeholder="Escribe para buscar paciente..."
              list="patients_list"
              autocomplete="off"
            >
            <input type="hidden" name="patient_id" id="patient_id" value="{{ old('patient_id') }}">

            <p class="text-xs text-slate-500">
              Si no eliges uno, puedes registrar un paciente nuevo más abajo.
            </p>
          </div>
          
          {{-- Notas --}}
          <div class="space-y-2">
            <label class="block text-sm font-medium text-slate-700">
              Notas
            </label>
            <input 
              type="text" 
              name="notes" 
              value="{{ old('notes') }}" 
              class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
              placeholder="Notas adicionales..."
            >
          </div>
        </div>

        {{-- Paciente nuevo por CI --}}
        <div class="mt-6 pt-4 border-t border-dashed border-slate-200">
          <h4 class="font-semibold text-slate-800 mb-2 text-sm">
            Paciente nuevo / búsqueda por CI
          </h4>
          <div class="grid gap-4 md:grid-cols-4">
            <div class="space-y-1">
              <label class="block text-xs font-medium text-slate-700">CI</label>
              <input 
                type="text" 
                name="ci" 
                id="ci"
                value="{{ old('ci') }}" 
                class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
                placeholder="Documento de identidad"
              >
            </div>
            <div class="space-y-1 md:col-span-2">
              <label class="block text-xs font-medium text-slate-700">Nombre(s)</label>
              <input 
                type="text" 
                name="first_name" 
                id="first_name"
                value="{{ old('first_name') }}" 
                class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
                placeholder="Nombres"
              >
            </div>
            <div class="space-y-1">
              <label class="block text-xs font-medium text-slate-700">Apellido(s)</label>
              <input 
                type="text" 
                name="last_name" 
                id="last_name"
                value="{{ old('last_name') }}" 
                class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
                placeholder="Apellidos"
              >
            </div>
            <div class="space-y-1">
              <label class="block text-xs font-medium text-slate-700">Teléfono</label>
              <input 
                type="text" 
                name="phone" 
                id="phone"
                value="{{ old('phone') }}" 
                class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
                placeholder="Teléfono"
              >
            </div>
          </div>
          <p class="text-xs text-slate-500 mt-2">
            O seleccionas un paciente existente <strong>o</strong> ingresas CI + nombre y apellido para crear uno nuevo.
          </p>
        </div>
      </div>

      {{-- Ítems: cada fila = cita --}}
      <div class="card mb-6">
        <div class="flex items-center justify-between mb-4 pb-4 border-b border-slate-200">
          <h3 class="font-semibold text-slate-800 flex items-center gap-2">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            Servicios a cobrar (cada fila = una cita)
          </h3>
          <button 
            type="button" 
            class="btn bg-green-600 text-white hover:bg-green-700 flex items-center gap-2"
            onclick="addItemRow()"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Agregar Servicio
          </button>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-xs md:text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
              <tr class="text-left">
                <th class="px-4 py-3 font-semibold text-slate-700">Servicio</th>
                <th class="px-4 py-3 font-semibold text-slate-700">Odontólogo</th>
                <th class="px-4 py-3 font-semibold text-slate-700">Fecha</th>
                <th class="px-4 py-3 font-semibold text-slate-700">Hora</th>
                <th class="px-4 py-3 font-semibold text-slate-700 text-right">Precio</th>
                <th class="px-4 py-3 font-semibold text-slate-700 text-right">Total</th>
                <th class="px-4 py-3 font-semibold text-slate-700 text-right">Acciones</th>
              </tr>
            </thead>
            <tbody id="items-body">
              @php $rowIndex = 0; @endphp
              <tr class="item-row border-b hover:bg-slate-50 transition-colors">
                {{-- Servicio --}}
                <td class="px-4 py-3">
                  <input
                    type="text"
                    class="service-search w-full border border-slate-300 rounded-lg px-3 py-2 mb-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
                    placeholder="Escribe para buscar servicio..."
                    list="services_list"
                    autocomplete="off"
                  >
                  <input type="hidden" name="items[0][service_id]" class="service-id-hidden">
                  <input type="hidden" name="items[0][quantity]" value="1">
                  <input type="text"
                         name="items[0][description]"
                         placeholder="Descripción del servicio..."
                         class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors">
                </td>

                {{-- Odontólogo --}}
                <td class="px-4 py-3">
                  <input
                    type="text"
                    class="dentist-search w-full border border-slate-300 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
                    placeholder="Escribe para buscar odontólogo..."
                    list="dentists_list"
                    autocomplete="off"
                  >
                  <input type="hidden" name="items[0][dentist_id]" class="dentist-id-hidden">
                </td>

                {{-- Fecha --}}
                <td class="px-4 py-3">
                  <input type="date"
                         name="items[0][date]"
                         value="{{ now()->toDateString() }}"
                         class="row-date w-full border border-slate-300 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
                         >
                </td>

                {{-- Hora --}}
                <td class="px-4 py-3">
                  <select name="items[0][start_time]"
                          class="row-time w-full border border-slate-300 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors">
                    <option value="">— Completa servicio, odontólogo y fecha —</option>
                  </select>
                </td>

                {{-- Precio / Total --}}
                <td class="px-4 py-3">
                  <input type="number" 
                         name="items[0][unit_price]" 
                         value="0.00" 
                         min="0" 
                         step="0.01" 
                         class="w-full border border-slate-300 rounded-lg px-3 py-2 text-right focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
                         oninput="recalcRow(this)">
                </td>
                <td class="px-4 py-3 text-right font-semibold text-blue-600">
                  <span class="row-total">0.00</span>
                  <div class="row-total-display text-[11px] font-normal text-slate-500">Bs 0.00</div>
                </td>

                {{-- Acciones --}}
                <td class="px-4 py-3 text-right">
                  <button type="button" 
                          class="btn bg-red-600 text-white hover:bg-red-700 flex items-center gap-1"
                          onclick="removeItemRow(this)">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Eliminar
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        {{-- Totales --}}
        <div class="mt-6 pt-6 border-t border-slate-200">
          <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4 max-w-2xl ml-auto">
            <div class="space-y-2">
              <label class="block text-sm font-medium text-slate-700">Descuento (Bs)</label>
              <input 
                type="number" 
                name="discount" 
                value="{{ old('discount', 0) }}" 
                min="0" 
                step="0.01"
                class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
                oninput="recalcTotals()"
              >
            </div>
            
            <div class="space-y-2">
              <label class="block text-sm font-medium text-slate-700">Impuesto %</label>
              <input 
                type="number" 
                name="tax_percent" 
                value="{{ old('tax_percent', 0) }}" 
                min="0" 
                max="100" 
                step="0.01"
                class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
                oninput="recalcTotals()"
              >
            </div>
            
            <div class="lg:col-span-2 space-y-2">
              <label class="block text-sm font-medium text-slate-700">Total del Recibo</label>
              <div class="bg-slate-50 border border-slate-300 rounded-lg px-4 py-3">
                <div class="text-2xl font-bold text-blue-600 text-right">
                  Bs <span id="grand-total">0.00</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      {{-- Acciones --}}
      <div class="flex items-center gap-4 pt-6 border-t border-slate-200">
        <button type="submit" class="btn bg-blue-600 text-white hover:bg-blue-700 flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
          </svg>
          Guardar y Emitir Recibo
        </button>
        <div class="flex-1">
          <p class="text-sm text-slate-600">
            El Recibo se creará con estado <strong>"Emitida"</strong> y se generarán las citas según las filas de arriba.
          </p>
        </div>
        <a href="{{ route('admin.billing') }}" class="btn bg-rose-600 text-white hover:bg-rose-700 flex items-center gap-2 transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
          Cancelar
        </a>
      </div>
    </form>
  </div>

  @push('scripts')
  <script>
    let rowIndex = {{ $rowIndex }};
    const patientsByCi   = @json($patientsByCi);
    const servicesList   = @json($servicesForJs);
    const dentistsList   = @json($dentistsForJs);
    const availabilityUrl = "{{ route('admin.appointments.availability') }}";

    // --- Paciente por CI (abajo) ---
    const ciInput         = document.getElementById('ci');
    const firstNameInput  = document.getElementById('first_name');
    const lastNameInput   = document.getElementById('last_name');
    const phoneInput      = document.getElementById('phone');
    const patientHidden   = document.getElementById('patient_id');
    const patientSearch   = document.getElementById('patient_search');

    if (ciInput) {
      ciInput.addEventListener('blur', function () {
        const ci = this.value.trim();
        if (ci && patientsByCi[ci]) {
          const p = patientsByCi[ci];
          if (patientHidden)  patientHidden.value  = p.id;
          if (patientSearch)  patientSearch.value  = (p.first_name ?? '') + ' ' + (p.last_name ?? '');
          if (firstNameInput) firstNameInput.value = p.first_name || '';
          if (lastNameInput)  lastNameInput.value  = p.last_name  || '';
          if (phoneInput)     phoneInput.value     = p.phone      || '';
        }
      });
    }

    // --- helper para datalist patients ---
    function findPatientByLabel(label) {
      const val = (label || '').trim().toLowerCase();
      let id = '';
      document.querySelectorAll('#patients_list option').forEach(opt => {
        const ov = (opt.value || '').trim().toLowerCase();
        if (ov === val) {
          id = opt.dataset.id || '';
        }
      });
      return id;
    }

    if (patientSearch && patientHidden) {
      const applyPatient = () => {
        const id = findPatientByLabel(patientSearch.value);
        patientHidden.value = id;
      };
      patientSearch.addEventListener('change', applyPatient);
      patientSearch.addEventListener('blur', applyPatient);
    }

    // --- Totales ---
    function recalcRow(el) {
      const tr   = el.closest('tr');
      const qty  = 1;
      const unit = parseFloat(tr.querySelector('input[name*="[unit_price]"]').value || '0');
      const total = (qty * unit).toFixed(2);

      const spanTotal  = tr.querySelector('.row-total');
      const spanDisplay = tr.querySelector('.row-total-display');

      if (spanTotal)   spanTotal.textContent = total;
      if (spanDisplay) spanDisplay.textContent = 'Bs ' + total;

      recalcTotals();
    }

    function recalcTotals() {
      let sub = 0;
      document.querySelectorAll('#items-body .row-total').forEach(function (s) {
        const v = parseFloat(s.textContent || '0');
        if (!isNaN(v)) sub += v;
      });

      const discountInput = document.querySelector('input[name="discount"]');
      const taxInput      = document.querySelector('input[name="tax_percent"]');

      const discount = parseFloat(discountInput?.value || '0');
      const taxp     = parseFloat(taxInput?.value || '0');

      const after = Math.max(0, sub - discount);
      const tax   = after * (taxp / 100);
      const total = (after + tax).toFixed(2);

      const grandSpan = document.getElementById('grand-total');
      if (grandSpan) grandSpan.textContent = total;
    }

    function removeItemRow(btn) {
      const tbody = document.getElementById('items-body');
      if (!tbody) return;
      const rows = tbody.querySelectorAll('tr.item-row');
      if (rows.length <= 1) return;
      btn.closest('tr').remove();
      recalcTotals();
    }

    // --- helpers datalist servicios / dentistas ---
    function findServiceByLabel(label) {
      const val = (label || '').trim().toLowerCase();
      let found = { id: '', price: 0 };
      document.querySelectorAll('#services_list option').forEach(opt => {
        const ov = (opt.value || '').trim().toLowerCase();
        if (ov === val) {
          found.id    = opt.dataset.id || '';
          found.price = parseFloat(opt.dataset.price || '0') || 0;
        }
      });
      return found;
    }

    function findDentistByLabel(label) {
      const val = (label || '').trim().toLowerCase();
      let id = '';
      document.querySelectorAll('#dentists_list option').forEach(opt => {
        const ov = (opt.value || '').trim().toLowerCase();
        if (ov === val) {
          id = opt.dataset.id || '';
        }
      });
      return id;
    }

    // --- Cargar horarios por fila, evitando duplicados mismo dentista+fecha+hora ---
    async function loadSlotsForRow(tr) {
      const serviceHidden = tr.querySelector('.service-id-hidden');
      const dentistHidden = tr.querySelector('.dentist-id-hidden');
      const dateInput     = tr.querySelector('.row-date');
      const timeSel       = tr.querySelector('.row-time');

      if (!serviceHidden || !dentistHidden || !dateInput || !timeSel) return;

      const serviceId = (serviceHidden.value || '').trim();
      const dentistId = (dentistHidden.value || '').trim();
      const dateVal   = (dateInput.value || '').trim();

      if (!serviceId || !dentistId || !dateVal) {
        timeSel.innerHTML = '<option value="">— Completa servicio, odontólogo y fecha —</option>';
        return;
      }

      // Horarios ya usados para ese dentista+fecha en OTRAS filas
      const taken = new Set();
      document.querySelectorAll('#items-body tr.item-row').forEach(function (row) {
        if (row === tr) return;
        const dHid = row.querySelector('.dentist-id-hidden');
        const dInp = row.querySelector('.row-date');
        const tSel = row.querySelector('.row-time');
        if (!dHid || !dInp || !tSel) return;
        if (dHid.value === dentistId && dInp.value === dateVal && tSel.value) {
          taken.add(tSel.value);
        }
      });

      timeSel.innerHTML = '<option value="">Cargando horarios...</option>';

      try {
        const params = new URLSearchParams({
          dentist_id: dentistId,
          service_id: serviceId,
          date:       dateVal,
        });

        const resp = await fetch(availabilityUrl + '?' + params.toString(), {
          headers: { 'Accept': 'application/json' }
        });

        if (!resp.ok) {
          timeSel.innerHTML = '<option value="">No se pudieron cargar horarios</option>';
          return;
        }

        const slots = await resp.json();
        if (!Array.isArray(slots) || slots.length === 0) {
          timeSel.innerHTML = '<option value="">No hay horarios disponibles</option>';
          return;
        }

        timeSel.innerHTML = '<option value="">— Selecciona hora —</option>';
        slots.forEach(function (h) {
          if (taken.has(h)) return; // no repetir
          const opt = document.createElement('option');
          opt.value = h;
          opt.textContent = h;
          timeSel.appendChild(opt);
        });

      } catch (e) {
        console.error(e);
        timeSel.innerHTML = '<option value="">Error al cargar horarios</option>';
      }
    }

    // --- Vincular buscadores por fila ---
    function bindRowSearch(tr) {
      const serviceInput  = tr.querySelector('.service-search');
      const dentistInput  = tr.querySelector('.dentist-search');
      const serviceHidden = tr.querySelector('.service-id-hidden');
      const dentistHidden = tr.querySelector('.dentist-id-hidden');
      const dateInput     = tr.querySelector('.row-date');

      if (serviceInput && serviceHidden) {
        const applyService = () => {
          const result = findServiceByLabel(serviceInput.value);
          serviceHidden.value = result.id;

          const unitInput = tr.querySelector('input[name*="[unit_price]"]');
          if (unitInput && result.id && result.price > 0) {
            unitInput.value = result.price.toFixed(2);
            recalcRow(unitInput);
          }

          loadSlotsForRow(tr);
        };
        serviceInput.addEventListener('change', applyService);
        serviceInput.addEventListener('blur', applyService);
      }

      if (dentistInput && dentistHidden) {
        const applyDentist = () => {
          const id = findDentistByLabel(dentistInput.value);
          dentistHidden.value = id;
          loadSlotsForRow(tr);
        };
        dentistInput.addEventListener('change', applyDentist);
        dentistInput.addEventListener('blur', applyDentist);
      }

      if (dateInput) {
        dateInput.addEventListener('change', () => loadSlotsForRow(tr));
      }
    }

    function addItemRow() {
      rowIndex++;
      const tbody = document.getElementById('items-body');
      if (!tbody) return;

      const today = new Date().toISOString().slice(0,10);

      const tr = document.createElement('tr');
      tr.className = 'item-row border-b hover:bg-slate-50 transition-colors';
      tr.innerHTML = `
        <td class="px-4 py-3">
          <input
            type="text"
            class="service-search w-full border border-slate-300 rounded-lg px-3 py-2 mb-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
            placeholder="Escribe para buscar servicio..."
            list="services_list"
            autocomplete="off"
          >
          <input type="hidden" name="items[${rowIndex}][service_id]" class="service-id-hidden">
          <input type="hidden" name="items[${rowIndex}][quantity]" value="1">
          <input type="text"
                 name="items[${rowIndex}][description]"
                 placeholder="Descripción del servicio..."
                 class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors">
        </td>
        <td class="px-4 py-3">
          <input
            type="text"
            class="dentist-search w-full border border-slate-300 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
            placeholder="Escribe para buscar odontólogo..."
            list="dentists_list"
            autocomplete="off"
          >
          <input type="hidden" name="items[${rowIndex}][dentist_id]" class="dentist-id-hidden">
        </td>
        <td class="px-4 py-3">
          <input type="date"
                 name="items[${rowIndex}][date]"
                 value="${today}"
                 class="row-date w-full border border-slate-300 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors">
        </td>
        <td class="px-4 py-3">
          <select name="items[${rowIndex}][start_time]"
                  class="row-time w-full border border-slate-300 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors">
            <option value="">— Completa servicio, odontólogo y fecha —</option>
          </select>
        </td>
        <td class="px-4 py-3">
          <input type="number"
                 name="items[${rowIndex}][unit_price]"
                 value="0.00"
                 min="0"
                 step="0.01"
                 class="w-full border border-slate-300 rounded-lg px-3 py-2 text-right focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
                 oninput="recalcRow(this)">
        </td>
        <td class="px-4 py-3 text-right font-semibold text-blue-600">
          <span class="row-total">0.00</span>
          <div class="row-total-display text-[11px] font-normal text-slate-500">Bs 0.00</div>
        </td>
        <td class="px-4 py-3 text-right">
          <button type="button"
                  class="btn bg-red-600 text-white hover:bg-red-700 flex items-center gap-1"
                  onclick="removeItemRow(this)">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
            Eliminar
          </button>
        </td>
      `;
      tbody.appendChild(tr);
      bindRowSearch(tr);
      recalcTotals();
    }

    document.addEventListener('DOMContentLoaded', function () {
      recalcTotals();
      const firstRow = document.querySelector('#items-body tr.item-row');
      if (firstRow) bindRowSearch(firstRow);
    });
  </script>
  @endpush
@endsection
