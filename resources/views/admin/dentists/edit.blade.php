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
  // Mismo script que en create
  const createUserRadios = document.querySelectorAll('input[name="create_user"]');
  const existingUserSection = document.getElementById('existing-user-section');
  const newUserSection = document.getElementById('new-user-section');
  
  function toggleUserSections() {
    const selectedValue = document.querySelector('input[name="create_user"]:checked').value;
    
    if (selectedValue === '0') {
      existingUserSection.classList.remove('hidden');
      newUserSection.classList.add('hidden');
    } else {
      newUserSection.classList.remove('hidden');
      existingUserSection.classList.add('hidden');
    }
  }
  
  createUserRadios.forEach(radio => {
    radio.addEventListener('change', toggleUserSections);
  });
  
  toggleUserSections();
});
</script>
@endsection