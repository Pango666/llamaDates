@extends('layouts.app')
@section('title', 'Perfil - ' . $dentist->name)

@section('header-actions')
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.dentists') }}" class="btn btn-ghost flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Volver al listado
        </a>
        <a href="{{ route('admin.dentists.edit', $dentist) }}" class="btn btn-primary flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            Editar Perfil
        </a>
    </div>
@endsection

@section('content')
<style>
    .profile-avatar {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
        font-size: 2.5rem;
        margin: 0 auto;
        border: 4px solid white;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    .status-badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .status-active { background: #dcfce7; color: #166534; }
    .status-inactive { background: #fef3c7; color: #92400e; }
    .status-busy { background: #fee2e2; color: #991b1b; }
    .status-available { background: #dbeafe; color: #1e40af; }
    
    .appointment-status {
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 0.7rem;
        font-weight: 500;
    }
    
    .metric-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        border: 1px solid #e2e8f0;
        transition: all 0.3s ease;
    }
    
    .metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    }
    
    .progress-ring {
        transform: rotate(-90deg);
    }
    
    .progress-ring-circle {
        transition: stroke-dashoffset 0.35s;
        transform: rotate(90deg);
        transform-origin: 50% 50%;
    }
</style>

<div class="mb-6">
    <div class="flex flex-col lg:flex-row items-start lg:items-center gap-6">
        <div class="profile-avatar">
            {{ substr($dentist->name, 0, 1) }}{{ substr(strstr($dentist->name, ' ') ?: '', 1, 1) }}
        </div>
        <div class="flex-1">
            <h1 class="text-3xl font-bold text-slate-800 mb-2">{{ $dentist->name }}</h1>
            <div class="flex flex-wrap items-center gap-4">
                @if($dentist->specialty)
                    <span class="px-3 py-1 bg-slate-100 text-slate-700 rounded-full text-sm font-medium">
                        {{ $dentist->specialty }}
                    </span>
                @endif
                @php
                    $statusClass = match($dentist->status) {
                        1 => 'status-active',
                        0 => 'status-inactive',
                        'busy' => 'status-busy',
                        'available' => 'status-available',
                        default => 'status-inactive'
                    };
                    $statusText = match($dentist->status) {
                        1 => 'Activo',
                        0 => 'Inactivo',
                        'busy' => 'En consulta',
                        'available' => 'Disponible',
                        default => 'Inactivo'
                    };
                @endphp
                <span class="status-badge {{ $statusClass }}">{{ $statusText }}</span>
                @if($dentist->chair)
                    <div class="flex items-center gap-2 text-slate-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                        </svg>
                        <span class="font-medium">Sillón {{ $dentist->chair->name }}</span>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Métricas Rápidas -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="metric-card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-slate-600 mb-1">Citas Hoy</p>
                <p class="text-2xl font-bold text-slate-800">{{ $todayAppointments ?? 0 }}</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
        </div>
        <div class="mt-3 text-xs text-slate-500">
            {{ $completedToday ?? 0 }} completadas
        </div>
    </div>
    
    <div class="metric-card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-slate-600 mb-1">Esta Semana</p>
                <p class="text-2xl font-bold text-slate-800">{{ $weekAppointments ?? 0 }}</p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
        </div>
        <div class="mt-3 text-xs text-slate-500">
            {{ $weekCompletion ?? 0 }}% tasa de completado
        </div>
    </div>
    
    {{-- pacientes especificos --}}
    {{-- <div class="metric-card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-slate-600 mb-1">Pacientes Activos</p>
                <p class="text-2xl font-bold text-slate-800">{{ $activePatients ?? 0 }}</p>
            </div>
            <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
        </div>
        <div class="mt-3 text-xs text-slate-500">
            En tratamiento activo
        </div>
    </div> --}}
    
    {{-- Metricas de Rating --}}
    {{-- <div class="metric-card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-slate-600 mb-1">Rating</p>
                <p class="text-2xl font-bold text-slate-800">{{ $rating ?? '4.8' }}/5</p>
            </div>
            <div class="w-12 h-12 bg-amber-100 rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                </svg>
            </div>
        </div>
        <div class="mt-3 text-xs text-slate-500">
            Basado en {{ $reviews ?? 42 }} evaluaciones
        </div>
    </div> --}}
</div>

<div class="grid gap-6 lg:grid-cols-3">
    <!-- Información del Odontólogo -->
    <section class="card lg:col-span-1">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-slate-800">Información Personal</h3>
            <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
        </div>
        
        <div class="space-y-4">
            <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-lg">
                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-slate-600">Email</p>
                    <p class="text-slate-800">{{ $dentist->user->email ?? 'No asignado' }}</p>
                </div>
            </div>
            
            <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-lg">
                <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-slate-600">Teléfono</p>
                    <p class="text-slate-800">{{ $dentist->phone ?? 'No especificado' }}</p>
                </div>
            </div>
            
            @if($dentist->chair)
            <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-lg">
                <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-slate-600">Sillón Asignado</p>
                    <p class="text-slate-800">{{ $dentist->chair->name }}</p>
                </div>
            </div>
            @endif
            
            <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-lg">
                <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-slate-600">Miembro desde</p>
                    <p class="text-slate-800">{{ $dentist->created_at?->format('d M Y') ?? 'N/A' }}</p>
                </div>
            </div>
        </div>
        
        <!-- Especialidades -->
        @if($dentist->specialty)
        <div class="mt-6 pt-4 border-t border-slate-200">
            <h4 class="text-sm font-semibold text-slate-700 mb-3">Especialidades</h4>
            <div class="flex flex-wrap gap-2">
                @foreach(explode(',', $dentist->specialty) as $spec)
                    <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-medium">
                        {{ trim($spec) }}
                    </span>
                @endforeach
            </div>
        </div>
        @endif
    </section>

    <!-- Próximas Citas -->
    <section class="card lg:col-span-2">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-slate-800">Próximas Citas</h3>
            <span class="text-sm text-slate-500">{{ count($upcoming) }} programadas</span>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr class="text-left">
                        <th class="px-4 py-3 font-semibold text-slate-700">Fecha y Hora</th>
                        <th class="px-4 py-3 font-semibold text-slate-700">Paciente</th>
                        <th class="px-4 py-3 font-semibold text-slate-700">Servicio</th>
                        <th class="px-4 py-3 font-semibold text-slate-700">Estado</th>
                        <th class="px-4 py-3 font-semibold text-slate-700 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($upcoming as $a)
                        @php
                            $statusConfig = [
                                'reserved'   => ['class' => 'bg-slate-100 text-slate-700', 'text' => 'Reservado'],
                                'confirmed'  => ['class' => 'bg-blue-100 text-blue-700', 'text' => 'Confirmado'],
                                'in_service' => ['class' => 'bg-amber-100 text-amber-700', 'text' => 'En atención'],
                                'done'       => ['class' => 'bg-emerald-100 text-emerald-700', 'text' => 'Atendido'],
                                'no_show'    => ['class' => 'bg-rose-100 text-rose-700', 'text' => 'No asistió'],
                                'canceled'   => ['class' => 'bg-slate-200 text-slate-500 line-through', 'text' => 'Cancelado'],
                            ][$a->status] ?? ['class' => 'bg-slate-100 text-slate-700', 'text' => $a->status];
                        @endphp
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 py-3">
                                <div class="font-medium text-slate-800">
                                    {{ \Illuminate\Support\Carbon::parse($a->date)->format('d M Y') }}
                                </div>
                                <div class="text-xs text-slate-500">
                                    {{ \Illuminate\Support\Str::substr($a->start_time,0,5) }} - {{ \Illuminate\Support\Str::substr($a->end_time,0,5) }}
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-slate-800">
                                    {{ $a->patient->first_name }} {{ $a->patient->last_name }}
                                </div>
                                <div class="text-xs text-slate-500">
                                    {{ $a->patient->phone ?? 'Sin teléfono' }}
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="font-medium text-slate-800">{{ $a->service->name }}</span>
                                <div class="text-xs text-slate-500 mt-1">
                                    {{ $a->notes ? \Illuminate\Support\Str::limit($a->notes, 30) : 'Sin notas' }}
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="appointment-status {{ $statusConfig['class'] }}">
                                    {{ $statusConfig['text'] }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.appointments.show', $a) }}" 
                                       class="btn btn-ghost btn-sm" title="Ver detalles">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center">
                                <div class="flex flex-col items-center justify-center text-slate-400">
                                    <svg class="w-12 h-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <p class="text-lg font-medium text-slate-500">No hay citas programadas</p>
                                    <p class="text-sm mt-1">No hay próximas citas para este odontólogo</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if(count($upcoming) > 0)
        <div class="mt-4 pt-4 border-t border-slate-200">
            <a href="{{ route('admin.appointments.create', ['dentist_id' => $dentist->id]) }}" 
               class="btn btn-primary w-full justify-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Agendar Nueva Cita
            </a>
        </div>
        @endif
    </section>
</div>

<!-- Historial Reciente (Opcional) -->
@if(isset($recentAppointments) && count($recentAppointments) > 0)
<section class="card mt-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-slate-800">Historial Reciente</h3>
        <span class="text-sm text-slate-500">Últimas 10 atenciones</span>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr class="text-left">
                    <th class="px-4 py-3 font-semibold text-slate-700">Fecha</th>
                    <th class="px-4 py-3 font-semibold text-slate-700">Paciente</th>
                    <th class="px-4 py-3 font-semibold text-slate-700">Procedimiento</th>
                    <th class="px-4 py-3 font-semibold text-slate-700">Duración</th>
                    <th class="px-4 py-3 font-semibold text-slate-700">Estado</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @foreach($recentAppointments as $appointment)
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3">{{ $appointment->date->format('d/m/Y') }}</td>
                    <td class="px-4 py-3">{{ $appointment->patient->full_name }}</td>
                    <td class="px-4 py-3">{{ $appointment->service->name }}</td>
                    <td class="px-4 py-3">{{ $appointment->duration }} min</td>
                    <td class="px-4 py-3">
                        <span class="appointment-status bg-emerald-100 text-emerald-700">
                            Completado
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>
@endif
@endsection