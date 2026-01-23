@php
  $isEdit = $patient->exists;
@endphp

<div class="grid gap-6 md:grid-cols-2">
  {{-- Información Personal --}}
  <div class="space-y-2">
    <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
      <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
      </svg>
      Nombres
      <span class="text-red-500">*</span>
    </label>
    <input 
      name="first_name" 
      value="{{ old('first_name', $patient->first_name) }}" 
      required
      class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
      placeholder="Ingrese los nombres del paciente"
    >
    @error('first_name')
      <p class="text-red-500 text-xs mt-1 flex items-center gap-1">
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        {{ $message }}
      </p>
    @enderror
  </div>

  <div class="space-y-2">
    <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
      <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
      </svg>
      Apellidos
      <span class="text-red-500">*</span>
    </label>
    <input 
      name="last_name" 
      value="{{ old('last_name', $patient->last_name) }}" 
      required
      class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
      placeholder="Ingrese los apellidos del paciente"
    >
    @error('last_name')
      <p class="text-red-500 text-xs mt-1 flex items-center gap-1">
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        {{ $message }}
      </p>
    @enderror
  </div>

  {{-- Documentación --}}
  <div class="space-y-2">
    <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
      <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
      </svg>
      CI / Documento de Identidad
    </label>
    <input 
      name="ci" 
      value="{{ old('ci', $patient->ci) }}" 
      class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
      placeholder="Ej: 12345678"
    >
    @error('ci')
      <p class="text-red-500 text-xs mt-1 flex items-center gap-1">
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        {{ $message }}
      </p>
    @enderror
  </div>

  <div class="space-y-2">
    <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
      <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
      </svg>
      Fecha de Nacimiento
    </label>
    <input 
      type="date" 
      name="birthdate" 
      value="{{ old('birthdate', $patient->birthdate) }}"
      class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
    >
    @error('birthdate')
      <p class="text-red-500 text-xs mt-1 flex items-center gap-1">
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        {{ $message }}
      </p>
    @enderror
  </div>

  {{-- Información de Contacto --}}
  <div class="space-y-2">
    <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
      <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
      </svg>
      Correo Electrónico
    </label>
    <input 
      type="email" 
      name="email" 
      value="{{ old('email', $patient->email) }}"
      class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
      placeholder="paciente@ejemplo.com"
    >
    @error('email')
      <p class="text-red-500 text-xs mt-1 flex items-center gap-1">
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        {{ $message }}
      </p>
    @enderror
  </div>

  <div class="space-y-2">
    <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
      <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
      </svg>
      Teléfono
    </label>
    <input 
      name="phone" 
      value="{{ old('phone', $patient->phone) }}"
      class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
      placeholder="Ej: +591 12345678"
    >
    @error('phone')
      <p class="text-red-500 text-xs mt-1 flex items-center gap-1">
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        {{ $message }}
      </p>
    @enderror
  </div>

  {{-- Dirección --}}
  <div class="md:col-span-2 space-y-2">
    <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
      <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
      </svg>
      Dirección
    </label>
    <textarea 
      name="address" 
      rows="3" 
      class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors resize-none"
      placeholder="Ingrese la dirección completa del paciente"
    >{{ old('address', $patient->address) }}</textarea>
    @error('address')
      <p class="text-red-500 text-xs mt-1 flex items-center gap-1">
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        {{ $message }}
      </p>
    @enderror
  </div>
</div>

{{-- Acciones del Formulario --}}
{{-- <div class="flex gap-3 pt-6 mt-6 border-t border-slate-200">
  <button 
    type="submit" 
    class="btn bg-blue-600 text-white hover:bg-blue-700 flex items-center gap-2 transition-colors"
  >
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
    </svg>
    {{ $isEdit ? 'Actualizar Paciente' : 'Registrar Paciente' }}
  </button>
  
  <a 
    href="{{ $isEdit ? route('admin.patients.show', $patient) : route('admin.patients.index') }}" 
    class="btn btn-ghost flex items-center gap-2 transition-colors"
  >
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
    </svg>
    Cancelar
  </a>
</div> --}}

@php
  // Obtener historia médica actual si existe
  $medHistory = $patient->exists ? ($patient->medicalHistory ?? null) : null;
@endphp

{{-- ============= SECCIÓN DE INFORMACIÓN MÉDICA ============= --}}
<div class="md:col-span-2 mt-6 pt-6 border-t border-slate-200">
  <details class="group" {{ $isEdit ? 'open' : '' }}>
    <summary class="flex items-center justify-between cursor-pointer p-3 bg-rose-50 border border-rose-200 rounded-lg hover:bg-rose-100 transition-colors">
      <div class="flex items-center gap-2">
        <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
        </svg>
        <span class="text-sm font-semibold text-rose-800">Información Médica (Alergias, Medicamentos, etc.)</span>
      </div>
      <svg class="w-4 h-4 text-rose-600 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
      </svg>
    </summary>
    
    <div class="mt-4 p-4 bg-white border border-slate-200 rounded-lg space-y-4">
      {{-- Alergias --}}
      <div class="space-y-2">
        <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
          <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
          </svg>
          Alergias Conocidas
        </label>
        <textarea 
          name="allergies" 
          rows="2" 
          class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:border-rose-500 focus:ring-1 focus:ring-rose-500 transition-colors resize-none"
          placeholder="Ej: Penicilina, Látex, Anestésicos locales... Dejar vacío si no tiene alergias conocidas."
        >{{ old('allergies', $medHistory->allergies ?? '') }}</textarea>
        <p class="text-xs text-slate-500">Ingrese las alergias separadas por comas o en líneas separadas.</p>
      </div>

      {{-- Medicamentos actuales --}}
      <div class="space-y-2">
        <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
          <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
          </svg>
          Medicamentos Actuales
        </label>
        <textarea 
          name="medications" 
          rows="2" 
          class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors resize-none"
          placeholder="Ej: Losartán 50mg diario, Metformina 850mg..."
        >{{ old('medications', $medHistory->medications ?? '') }}</textarea>
        <p class="text-xs text-slate-500">Liste los medicamentos que el paciente toma actualmente.</p>
      </div>

      {{-- Enfermedades sistémicas --}}
      <div class="space-y-2">
        <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
          <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
          </svg>
          Enfermedades Sistémicas
        </label>
        <textarea 
          name="systemic_diseases" 
          rows="2" 
          class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:border-purple-500 focus:ring-1 focus:ring-purple-500 transition-colors resize-none"
          placeholder="Ej: Diabetes, Hipertensión, Enfermedades cardíacas..."
        >{{ old('systemic_diseases', $medHistory->systemic_diseases ?? '') }}</textarea>
      </div>

      {{-- Checkboxes: Fumador y Embarazada --}}
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        {{-- Fumador --}}
        <label class="flex items-center gap-3 p-3 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50 transition-colors">
          <input 
            type="checkbox" 
            name="smoker" 
            value="1"
            class="w-5 h-5 text-amber-600 border-slate-300 rounded focus:ring-amber-500"
            {{ old('smoker', $medHistory->smoker ?? false) ? 'checked' : '' }}
          >
          <div>
            <span class="text-sm font-medium text-slate-700 flex items-center gap-2">
              <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/>
              </svg>
              Paciente Fumador
            </span>
            <span class="text-xs text-slate-500">Marque si el paciente fuma actualmente</span>
          </div>
        </label>

        {{-- Embarazada --}}
        <label class="flex items-center gap-3 p-3 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50 transition-colors">
          <input 
            type="checkbox" 
            name="pregnant" 
            value="1"
            class="w-5 h-5 text-pink-600 border-slate-300 rounded focus:ring-pink-500"
            {{ old('pregnant', $medHistory->pregnant ?? false) ? 'checked' : '' }}
          >
          <div>
            <span class="text-sm font-medium text-slate-700 flex items-center gap-2">
              <svg class="w-4 h-4 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
              </svg>
              Paciente Embarazada
            </span>
            <span class="text-xs text-slate-500">Marque si aplica</span>
          </div>
        </label>
      </div>

      {{-- Antecedentes quirúrgicos --}}
      <div class="space-y-2">
        <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
          <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
          </svg>
          Antecedentes Quirúrgicos
        </label>
        <textarea 
          name="surgical_history" 
          rows="2" 
          class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:border-slate-500 focus:ring-1 focus:ring-slate-500 transition-colors resize-none"
          placeholder="Ej: Apendicectomía (2015), Extracción de muelas del juicio..."
        >{{ old('surgical_history', $medHistory->surgical_history ?? '') }}</textarea>
      </div>

      {{-- Hábitos --}}
      <div class="space-y-2">
        <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
          <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          Hábitos Relevantes
        </label>
        <textarea 
          name="habits" 
          rows="2" 
          class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:border-green-500 focus:ring-1 focus:ring-green-500 transition-colors resize-none"
          placeholder="Ej: Bruxismo, Onicofagia (morderse las uñas), Consumo de alcohol..."
        >{{ old('habits', $medHistory->habits ?? '') }}</textarea>
      </div>
    </div>
  </details>
</div>

{{-- Botón de guardar --}}
<div class="md:col-span-2 flex gap-3 pt-6 mt-6 border-t border-slate-200">
  <button 
    type="submit" 
    class="btn bg-blue-600 text-white hover:bg-blue-700 flex items-center gap-2 transition-colors"
  >
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
    </svg>
    {{ $isEdit ? 'Actualizar Paciente' : 'Registrar Paciente' }}
  </button>
  
  <a 
    href="{{ $isEdit ? route('admin.patients.show', $patient) : route('admin.patients.index') }}" 
    class="btn btn-ghost flex items-center gap-2 transition-colors"
  >
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
    </svg>
    Cancelar
  </a>
</div>