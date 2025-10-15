@extends('layouts.app')
@section('title','Nuevo Paciente')

@section('header-actions')
  <a href="{{ route('admin.patients.index') }}" class="btn btn-ghost flex items-center gap-2">
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
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
          </svg>
          Registrar Nuevo Paciente
        </h1>
        <p class="text-sm text-slate-600 mt-1">Complete la información del paciente. Los campos marcados con <span class="text-red-500">*</span> son obligatorios.</p>
      </div>

      <form method="post" action="{{ route('admin.patients.store') }}" id="patientForm">
        @csrf
        @include('admin.patients._form', ['patient' => $patient])
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
              <li>• Los datos de contacto ayudan en notificaciones</li>
              <li>• La fecha de nacimiento es necesaria para calcular la edad</li>
              <li>• El CI/Documento debe ser único por paciente</li>
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
            <h3 class="font-medium text-amber-800">Datos recomendados</h3>
            <ul class="text-sm text-amber-700 mt-2 space-y-1">
              <li>• Email para recordatorios automáticos</li>
              <li>• Teléfono para contactos urgentes</li>
              <li>• Dirección para referencias</li>
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
  const form = document.getElementById('patientForm');
  const birthdateInput = document.querySelector('input[name="birthdate"]');
  
  // Establecer fecha máxima (hoy) para fecha de nacimiento
  if (birthdateInput) {
    const today = new Date().toISOString().split('T')[0];
    birthdateInput.setAttribute('max', today);
  }

  // Validación de email si se proporciona
  const emailInput = document.querySelector('input[name="email"]');
  if (emailInput) {
    emailInput.addEventListener('blur', function() {
      if (this.value && !this.validity.valid) {
        this.classList.add('border-red-500', 'bg-red-50');
      } else {
        this.classList.remove('border-red-500', 'bg-red-50');
      }
    });
  }

  // Validación de teléfono básica
  const phoneInput = document.querySelector('input[name="phone"]');
  if (phoneInput) {
    phoneInput.addEventListener('input', function() {
      // Permitir solo números, espacios, +, - y paréntesis
      this.value = this.value.replace(/[^\d\s+\-()]/g, '');
    });
  }
});
</script>
@endsection