@extends('layouts.app')
@section('title', 'Perfil de Paciente - ' . $patient->full_name)

@section('header-actions')
  <div class="flex flex-wrap gap-2">
    <a href="{{ route('admin.patients.index') }}" class="btn btn-ghost flex items-center gap-2">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
      </svg>
      Volver al Listado
    </a>
    <a href="{{ route('admin.patients.edit',$patient) }}" class="btn btn-ghost flex items-center gap-2">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
      </svg>
      Editar
    </a>
    <a href="{{ route('admin.patients.record',$patient) }}" class="btn btn-ghost flex items-center gap-2">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
      </svg>
      Historia Completa
    </a>
    <a href="{{ route('admin.odontograms.open',$patient) }}" class="btn btn-ghost flex items-center gap-2">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
      </svg>
      Odontograma
    </a>
    <a href="{{ route('admin.patients.plans.index',$patient) }}" class="btn btn-ghost flex items-center gap-2">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
      </svg>
      Ver Planes
    </a>
    <a href="{{ route('admin.patients.plans.create',$patient) }}" class="btn btn-ghost flex items-center gap-2">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
      </svg>
      Nuevo Plan
    </a>
    <a href="{{ route('admin.appointments.create', ['patient_id'=>$patient->id]) }}" class="btn btn-primary flex items-center gap-2">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
      </svg>
      Nueva Cita
    </a>
  </div>
@endsection

@section('content')
  {{-- Header del paciente --}}
  <div class="card bg-gradient-to-r from-blue-50 to-indigo-50 border-blue-200 mb-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
      <div class="flex items-center gap-4">
        <div class="w-16 h-16 bg-blue-600 rounded-full flex items-center justify-center">
          <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
          </svg>
        </div>
        <div>
          <h1 class="text-2xl font-bold text-slate-800">{{ $patient->last_name }}, {{ $patient->first_name }}</h1>
          <div class="flex flex-wrap gap-4 mt-2 text-sm text-slate-600">
            @if($patient->ci)
              <span class="flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                CI: {{ $patient->ci }}
              </span>
            @endif
            @if(isset($age))
              <span class="flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                {{ $age }} años
              </span>
            @endif
            @if($patient->phone)
              <span class="flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                </svg>
                {{ $patient->phone }}
              </span>
            @endif
          </div>
        </div>
      </div>
      <div class="flex gap-2">
        <span class="badge bg-blue-100 text-blue-800 border border-blue-200">
          Paciente desde {{ $patient->created_at?->format('M Y') ?? 'N/A' }}
        </span>
      </div>
    </div>
  </div>

  <div class="grid gap-6 md:grid-cols-3">
    {{-- Información del paciente --}}
    <section class="card">
      <div class="flex items-center gap-2 mb-4">
        <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
        </svg>
        <h3 class="font-semibold text-slate-800">Información Personal</h3>
      </div>

      <div class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">Nombres</label>
            <p class="text-sm font-medium text-slate-800">{{ $patient->first_name }}</p>
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">Apellidos</label>
            <p class="text-sm font-medium text-slate-800">{{ $patient->last_name }}</p>
          </div>
        </div>

        <div>
          <label class="block text-xs font-medium text-slate-500 mb-1 flex items-center gap-1">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            Fecha de Nacimiento
          </label>
          <p class="text-sm text-slate-700">
            {{ $patient->birthdate ? \Carbon\Carbon::parse($patient->birthdate)->format('d/m/Y') : 'No registrada' }}
            @if(isset($age))
              <span class="text-xs text-slate-500 ml-2">({{ $age }} años)</span>
            @endif
          </p>
        </div>

        <div>
          <label class="block text-xs font-medium text-slate-500 mb-1 flex items-center gap-1">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
            Correo Electrónico
          </label>
          <p class="text-sm text-slate-700 break-all">
            {{ $patient->email ?: 'No registrado' }}
          </p>
        </div>

        <div>
          <label class="block text-xs font-medium text-slate-500 mb-1 flex items-center gap-1">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
            </svg>
            Teléfono
          </label>
          <p class="text-sm text-slate-700">
            {{ $patient->phone ?: 'No registrado' }}
          </p>
        </div>

        <div>
          <label class="block text-xs font-medium text-slate-500 mb-1 flex items-center gap-1">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            Dirección
          </label>
          <p class="text-sm text-slate-700 whitespace-pre-line">
            {{ $patient->address ?: 'No registrada' }}
          </p>
        </div>

        <div class="pt-3 border-t border-slate-200">
          <label class="block text-xs font-medium text-slate-500 mb-1">Fecha de Registro</label>
          <p class="text-sm text-slate-700">
            {{ $patient->created_at?->format('d/m/Y H:i') ?? 'No disponible' }}
          </p>
        </div>
      </div>
    </section>

    {{-- Citas recientes --}}
    <section class="card md:col-span-2">
      <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-2">
          <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
          </svg>
          <h3 class="font-semibold text-slate-800">Últimas Citas</h3>
        </div>
        <a href="{{ route('admin.appointments.index', ['patient_id'=>$patient->id]) }}" 
           class="btn btn-ghost text-sm flex items-center gap-1">
          Ver todas
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
          </svg>
        </a>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-slate-50 border-b">
            <tr class="text-left">
              <th class="px-4 py-3 font-medium text-slate-600">Fecha</th>
              <th class="px-4 py-3 font-medium text-slate-600">Horario</th>
              <th class="px-4 py-3 font-medium text-slate-600">Servicio</th>
              <th class="px-4 py-3 font-medium text-slate-600">Odontólogo</th>
              <th class="px-4 py-3 font-medium text-slate-600">Estado</th>
              <th class="px-4 py-3 font-medium text-slate-600 text-right">Acciones</th>
            </tr>
          </thead>
          <tbody>
          @forelse($appointments as $a)
            @php
              $badge = [
                'reserved'   => 'bg-yellow-100 text-yellow-800 border border-yellow-200',
                'confirmed'  => 'bg-blue-100 text-blue-800 border border-blue-200',
                'in_service' => 'bg-orange-100 text-orange-800 border border-orange-200',
                'done'       => 'bg-green-100 text-green-800 border border-green-200',
                'no_show'    => 'bg-red-100 text-red-800 border border-red-200',
                'canceled'   => 'bg-slate-100 text-slate-500 border border-slate-300 line-through',
              ][$a->status] ?? 'bg-slate-100 text-slate-700';
            @endphp
            <tr class="border-b hover:bg-slate-50">
              <td class="px-4 py-3 whitespace-nowrap font-medium text-slate-700">
                {{ \Illuminate\Support\Carbon::parse($a->date)->format('d/m/Y') }}
              </td>
              <td class="px-4 py-3 whitespace-nowrap text-slate-600">
                {{ \Illuminate\Support\Str::substr($a->start_time,0,5) }}–{{ \Illuminate\Support\Str::substr($a->end_time,0,5) }}
              </td>
              <td class="px-4 py-3 text-slate-700">{{ $a->service->name }}</td>
              <td class="px-4 py-3 text-slate-700">{{ $a->dentist->name }}</td>
              <td class="px-4 py-3">
                <span class="badge {{ $badge }} text-xs font-medium">
                  {{ [
                    'reserved'=>'Reservado',
                    'confirmed'=>'Confirmado',
                    'in_service'=>'En atención',
                    'done'=>'Atendido',
                    'no_show'=>'No asistió',
                    'canceled'=>'Cancelado'
                  ][$a->status] ?? $a->status }}
                </span>
              </td>
              <td class="px-4 py-3 text-right">
                <a href="{{ route('admin.appointments.show',$a->id) }}" 
                   class="btn btn-ghost text-xs hover:bg-blue-50 hover:text-blue-600">
                  Ver
                </a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="px-4 py-8 text-center text-slate-500">
                <div class="flex flex-col items-center gap-2">
                  <svg class="w-12 h-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                  </svg>
                  <div>
                    <div class="font-medium text-slate-600">No hay citas registradas</div>
                    <div class="text-sm text-slate-500 mt-1">Programe la primera cita para este paciente</div>
                  </div>
                </div>
              </td>
            </tr>
          @endforelse
          </tbody>
        </table>
      </div>
    </section>
  </div>

  {{-- Planes de tratamiento --}}
  <section class="card mt-6">
    <div class="flex items-center justify-between mb-4">
      <div class="flex items-center gap-2">
        <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
        </svg>
        <h3 class="font-semibold text-slate-800">Planes de Tratamiento</h3>
      </div>
      <div class="flex gap-2">
        <a href="{{ route('admin.patients.plans.index',$patient) }}" 
           class="btn btn-ghost flex items-center gap-1">
          Ver todos
        </a>
        <a href="{{ route('admin.patients.plans.create',$patient) }}" 
           class="btn btn-primary flex items-center gap-1">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          Nuevo Plan
        </a>
      </div>
    </div>

    @if(isset($plans) && $plans->count())
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-slate-50 border-b">
            <tr class="text-left">
              <th class="px-4 py-3 font-medium text-slate-600">Título del Plan</th>
              <th class="px-4 py-3 font-medium text-slate-600">Estado</th>
              <th class="px-4 py-3 font-medium text-slate-600 text-right">Total Estimado</th>
              <th class="px-4 py-3 font-medium text-slate-600">Creado</th>
              <th class="px-4 py-3 font-medium text-slate-600 text-right">Acciones</th>
            </tr>
          </thead>
          <tbody>
          @foreach($plans as $p)
            @php
              $badge = [
                'draft'       => 'bg-slate-100 text-slate-700 border border-slate-300',
                'approved'    => 'bg-blue-100 text-blue-800 border border-blue-200',
                'in_progress' => 'bg-green-100 text-green-800 border border-green-200',
              ][$p->status] ?? 'bg-slate-100 text-slate-700';
            @endphp
            <tr class="border-b hover:bg-slate-50">
              <td class="px-4 py-3 font-medium text-slate-800">{{ $p->title }}</td>
              <td class="px-4 py-3">
                <span class="badge {{ $badge }} text-xs font-medium">
                  {{ str_replace('_',' ', ucfirst($p->status)) }}
                </span>
              </td>
              <td class="px-4 py-3 text-right font-semibold text-slate-800">
                Bs {{ number_format($p->estimate_total, 2) }}
              </td>
              <td class="px-4 py-3 text-slate-600">
                {{ $p->created_at?->format('d/m/Y') }}
              </td>
              <td class="px-4 py-3">
                <div class="flex justify-end gap-1">
                  <a href="{{ route('admin.plans.edit',$p) }}" 
                     class="btn btn-ghost text-xs p-2 hover:bg-blue-50 hover:text-blue-600"
                     title="Abrir plan">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                  </a>
                  <a href="{{ route('admin.plans.print',$p) }}" 
                     class="btn btn-ghost text-xs p-2 hover:bg-orange-50 hover:text-orange-600"
                     title="Imprimir">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                  </a>
                  <a href="{{ route('admin.plans.pdf',$p) }}" 
                     class="btn btn-ghost text-xs p-2 hover:bg-red-50 hover:text-red-600"
                     title="Descargar PDF">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                  </a>
                  <a href="{{ route('admin.plans.invoice.create',$p) }}" 
                     class="btn btn-ghost text-xs p-2 hover:bg-green-50 hover:text-green-600"
                     title="Facturar">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
                    </svg>
                  </a>
                </div>
              </td>
            </tr>
          @endforeach
          </tbody>
        </table>
      </div>
    @else
      <div class="text-center py-8 text-slate-500">
        <svg class="w-16 h-16 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
        </svg>
        <p class="font-medium text-slate-600">No hay planes de tratamiento</p>
        <p class="text-sm text-slate-500 mt-1">Cree el primer plan para este paciente</p>
      </div>
    @endif
  </section>
@endsection