<section class="card mt-4">
  <div class="flex items-center justify-between mb-2">
    <h3 class="font-semibold">Consentimientos</h3>
    <div class="flex gap-2">
      <a href="{{ route('admin.patients.consents.index',$patient) }}" class="btn btn-ghost">Ver consentimientos</a>
      <a href="{{ route('admin.patients.consents.create',$patient) }}" class="btn btn-primary">+ Nuevo</a>
    </div>
  </div>
  <p class="text-sm text-slate-500">Genera PDF y sube el escaneo firmado del paciente.</p>
</section>
