@extends('layouts.app')
@section('title','Nuevo Odontólogo')

@section('header-actions')
  <a href="{{ route('admin.dentists') }}" class="btn btn-ghost flex items-center gap-2">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
    </svg>
    Volver al Listado
  </a>
@endsection

@section('content')
  <div class="max-w-4xl mx-auto">
    <div class="card">
      <div class="border-b border-slate-200 pb-4 mb-6">
        <h1 class="text-xl font-semibold text-slate-800 flex items-center gap-2">
          <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
          </svg>
          Registrar Nuevo Odontólogo
        </h1>
        <p class="text-sm text-slate-600 mt-1">Complete la información del odontólogo. Los campos marcados con <span class="text-red-500">*</span> son obligatorios.</p>
      </div>

      <form method="post" action="{{ route('admin.dentists.store') }}" id="dentistForm">
        @csrf
        @include('admin.dentists._form', ['dentist' => $dentist])
      </form>
    </div>

    {{-- Información de ayuda --}}
    <div class="mt-6 grid gap-4 md:grid-cols-2">
      <div class="card bg-blue-50 border-blue-200">
        <div class="flex items-start gap-3">
          <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <div>
            <h3 class="font-medium text-blue-800">Información importante</h3>
            <ul class="text-sm text-blue-700 mt-2 space-y-1">
              <li>• El nombre debe ser completo para identificarlo fácilmente</li>
              <li>• La especialidad ayuda a filtrar búsquedas</li>
              <li>• El sillón asignado determina su lugar de trabajo</li>
            </ul>
          </div>
        </div>
      </div>

      <div class="card bg-amber-50 border-amber-200">
        <div class="flex items-start gap-3">
          <svg class="w-5 h-5 text-amber-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
          </svg>
          <div>
            <h3 class="font-medium text-amber-800">Usuario del sistema</h3>
            <ul class="text-sm text-amber-700 mt-2 space-y-1">
              <li>• Puede vincular un usuario existente</li>
              <li>• O crear uno nuevo automáticamente</li>
              <li>• El usuario tendrá rol "odontólogo"</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  const radios = document.querySelectorAll('input[name="create_user"]');
  const existingSection = document.getElementById('existing-user-section');
  const newSection = document.getElementById('new-user-section');

  const newName = document.querySelector('input[name="new_user_name"]');
  const newEmail = document.querySelector('input[name="new_user_email"]');
  const newPass = document.querySelector('input[name="new_user_password"]');

  function setRequired(el, val) {
    if (!el) return;
    if (val) el.setAttribute('required', 'required');
    else el.removeAttribute('required');
  }

  function clearNewUserFields() {
    if (newName) newName.value = '';
    if (newEmail) newEmail.value = '';
    if (newPass) newPass.value = '';
  }

  function toggle() {
    const val = document.querySelector('input[name="create_user"]:checked')?.value || '0';
    // Oculta/mostrar
    if (val === '1') { // crear
      newSection?.classList.remove('hidden');
      existingSection?.classList.add('hidden');
      setRequired(newName, true);
      setRequired(newEmail, true);
      setRequired(newPass, true);
    } else if (val === '0') { // existente
      existingSection?.classList.remove('hidden');
      newSection?.classList.add('hidden');
      setRequired(newName, false);
      setRequired(newEmail, false);
      setRequired(newPass, false);
      clearNewUserFields();
    } else { // none
      existingSection?.classList.add('hidden');
      newSection?.classList.add('hidden');
      setRequired(newName, false);
      setRequired(newEmail, false);
      setRequired(newPass, false);
      clearNewUserFields();
    }
  }

  radios.forEach(r => r.addEventListener('change', toggle));
  toggle(); // estado inicial
});
</script>
@endsection