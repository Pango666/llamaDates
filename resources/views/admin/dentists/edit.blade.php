@extends('layouts.app')
@section('title', 'Editar Odontólogo: ' . $dentist->name)

@section('header-actions')
  <a href="{{ route('admin.dentists.show', $dentist) }}" class="btn btn-ghost flex items-center gap-2">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
    </svg>
    Volver al Detalle
  </a>
@endsection

@section('content')
  <div class="max-w-4xl mx-auto">
    <div class="card">
      <div class="border-b border-slate-200 pb-4 mb-6">
        <h1 class="text-xl font-semibold text-slate-800 flex items-center gap-2">
          <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
          </svg>
          Editar Odontólogo
        </h1>
        <p class="text-sm text-slate-600 mt-1">Modifique la información del odontólogo según sea necesario.</p>
      </div>

      <form method="post" action="{{ route('admin.dentists.update', $dentist) }}" id="dentistForm">
        @csrf @method('PUT')
        @include('admin.dentists._form', ['dentist' => $dentist])
      </form>
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