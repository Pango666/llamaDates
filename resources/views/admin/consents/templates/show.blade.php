<section class="card mt-6">
  <div class="border-b border-slate-200 pb-4 mb-4">
    <h3 class="font-semibold text-slate-800 flex items-center gap-2">
      <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
      </svg>
      Consentimientos del Paciente
    </h3>
    <p class="text-sm text-slate-600 mt-1">Genere PDF y suba el escaneo firmado del paciente.</p>
  </div>

  <div class="flex items-center justify-between">
    <div class="flex items-center gap-2 text-sm text-slate-600">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      Gestione los consentimientos informados
    </div>
    <div class="flex gap-2">
      <a 
        href="{{ route('admin.patients.consents.index', $patient) }}" 
        class="btn btn-ghost flex items-center gap-2"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
        </svg>
        Ver Consentimientos
      </a>
      <a 
        href="{{ route('admin.patients.consents.create', $patient) }}" 
        class="btn bg-blue-600 text-white hover:bg-blue-700 flex items-center gap-2"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Nuevo Consentimiento
      </a>
    </div>
  </div>
</section>