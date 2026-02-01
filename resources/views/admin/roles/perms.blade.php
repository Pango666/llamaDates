@extends('layouts.app')

@php
    use Illuminate\Support\Str;

    /**
     * Nombre amigable para cada grupo de permisos (primer segmento del name).
     */
    function pretty_group_name($key) {
        return match ($key) {
            'appointments' => 'Citas',
            'patients'     => 'Pacientes',
            'billing', 'invoices' => 'Pagos y facturación',
            'services'     => 'Servicios',
            'schedules'    => 'Horarios',
            'chairs'       => 'Consultorios / Sillones',
            'users'        => 'Usuarios',
            'dentists'     => 'Odontólogos',
            'medical_history' => 'Historias clínicas',
            'consent_templates', 'patient_consents', 'consents' => 'Consentimientos',
            'clinical_notes' => 'Notas clínicas',
            'diagnoses'    => 'Diagnósticos',
            'attachments'  => 'Adjuntos clínicos',
            'inventory', 'inv' => 'Inventario',
            'roles', 'permissions' => 'Seguridad (roles y permisos)',
            'patient'      => 'App Paciente',
            'dashboard'    => 'Dashboard',
            'agenda'       => 'Agenda',
            'reports'      => 'Reportes',
            'payments'     => 'Pagos',
            default        => Str::title(str_replace('_', ' ', $key)),
        };
    }

    // Agrupamos permisos por módulo (primer segmento antes del punto).
    $groupedPerms = $perms->groupBy(function($p) {
        $parts = explode('.', $p->name);
        return $parts[0] ?? 'otros';
    });

    $current = $role->permissions->pluck('id')->all();
@endphp

@section('title', 'Permisos · ' . $role->name)

@section('header-actions')
  <a href="{{ route('admin.roles.index') }}" class="btn bg-slate-600 text-white hover:bg-slate-700 flex items-center gap-2 transition-colors">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
    </svg>
    Volver a Roles
  </a>
@endsection

@section('content')
  <div class="max-w-6xl mx-auto">
    {{-- Header --}}
    <div class="card mb-6">
      <div class="border-b border-slate-200 pb-4">
        <h1 class="text-xl font-semibold text-slate-800 flex items-center gap-2">
          <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
          </svg>
          Gestión de Permisos
        </h1>
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2 mt-2">
          <p class="text-sm text-slate-600">
            Asigne permisos al rol:
            <span class="font-medium text-blue-600">{{ $role->name }}</span>
            @if($role->label)
              <span class="text-slate-500">({{ $role->label }})</span>
            @endif
          </p>
          <div class="flex items-center gap-2 text-sm">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
              {{ $role->permissions_count }} permiso(s) actualmente
            </span>
          </div>
        </div>
      </div>
    </div>

    <form method="post" action="{{ route('admin.roles.update.perms', $role) }}" class="card">
      @csrf
      @method('PUT')

      {{-- Contador global --}}
      <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
            <div>
              <p class="text-sm font-medium text-blue-800">
                Permisos seleccionados:
                <span id="selected-count">0</span>
              </p>
              <p class="text-xs text-blue-600">
                Marque los permisos que desea asignar a este rol.  
                Los nombres técnicos son solo de referencia interna.
              </p>
            </div>
          </div>
          <button
            type="button"
            onclick="toggleAllPermissions()"
            class="btn btn-ghost flex items-center gap-2 text-sm"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/>
            </svg>
            Marcar / desmarcar todos
          </button>
        </div>
      </div>

      {{-- Grupos de permisos por módulo --}}
      <div class="space-y-4">
        @foreach($groupedPerms as $groupKey => $group)
          @php
              $moduleName = pretty_group_name($groupKey);
              $groupId = 'group-' . $groupKey;
          @endphp

          <div class="border border-slate-200 rounded-xl overflow-hidden">
            {{-- Encabezado del grupo --}}
            <div class="flex items-center justify-between px-4 py-3 bg-slate-50 border-b border-slate-200">
              <div>
                <h2 class="text-sm font-semibold text-slate-800 flex items-center gap-2">
                  <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-slate-800 text-white text-xs">
                    {{ strtoupper(substr($moduleName, 0, 1)) }}
                  </span>
                  {{ $moduleName }}
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">
                  Permisos relacionados con este módulo.
                </p>
              </div>

              <button
                type="button"
                onclick="toggleGroup('{{ $groupId }}')"
                class="btn btn-ghost text-xs flex items-center gap-1"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M5 13l4 4L19 7"/>
                </svg>
                Marcar / desmarcar módulo
              </button>
            </div>

            {{-- Lista de permisos del grupo --}}
            <div id="{{ $groupId }}" class="grid md:grid-cols-2 gap-3 p-4">
              @foreach($group as $permission)
                <label class="flex items-start gap-3 border border-slate-200 rounded-lg p-3 hover:bg-slate-50 transition-colors cursor-pointer">
                  <input
                    type="checkbox"
                    name="perms[]"
                    value="{{ $permission->id }}"
                    class="mt-1 rounded border-slate-300 text-blue-600 focus:ring-blue-500 permission-checkbox"
                    @checked(in_array($permission->id, old('perms', $current)))
                    onchange="updateSelectedCount()"
                    data-group="{{ $groupId }}"
                  >
                  <div class="flex-1">
                    {{-- Descripción amigable --}}
                    <div class="font-medium text-slate-800 mb-1">
                      {{ $permission->label ?? Str::headline($permission->name) }}
                    </div>
                    {{-- Código interno pequeño --}}
                    <div class="text-[11px] text-slate-500">
                      Código interno: <code>{{ $permission->name }}</code>
                    </div>
                    @if($permission->description)
                      <div class="text-xs text-slate-500 mt-1">
                        {{ $permission->description }}
                      </div>
                    @endif
                  </div>
                </label>
              @endforeach
            </div>
          </div>
        @endforeach
      </div>

      {{-- Acciones --}}
      <div class="flex items-center gap-4 pt-6 mt-6 border-t border-slate-200">
        <button type="submit" class="btn bg-blue-600 text-white hover:bg-blue-700 flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
          </svg>
          Guardar Permisos
        </button>
        <a href="{{ route('admin.roles.index') }}" class="btn bg-rose-600 text-white hover:bg-rose-700 flex items-center gap-2 transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
          Cancelar
        </a>
      </div>
    </form>
  </div>

  <script>
    function updateSelectedCount() {
      const checkboxes = document.querySelectorAll('.permission-checkbox:checked');
      document.getElementById('selected-count').textContent = checkboxes.length;
    }

    function toggleAllPermissions() {
      const checkboxes = document.querySelectorAll('.permission-checkbox');
      const allChecked = Array.from(checkboxes).every(ch => ch.checked);
      checkboxes.forEach(ch => ch.checked = !allChecked);
      updateSelectedCount();
    }

    function toggleGroup(groupId) {
      const groupCheckboxes = document.querySelectorAll(`.permission-checkbox[data-group="${groupId}"]`);
      if (!groupCheckboxes.length) return;

      const allChecked = Array.from(groupCheckboxes).every(ch => ch.checked);
      groupCheckboxes.forEach(ch => ch.checked = !allChecked);
      updateSelectedCount();
    }

    document.addEventListener('DOMContentLoaded', updateSelectedCount);
  </script>
@endsection
