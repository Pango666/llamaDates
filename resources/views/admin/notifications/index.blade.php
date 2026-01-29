@extends('layouts.app')
@section('title', 'Notificaciones · Panel')

@section('header-actions')
  <a href="{{ route('admin.notifications.test') }}" class="btn bg-blue-600 text-white hover:bg-blue-700 flex items-center gap-2">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
    </svg>
    Nueva Notificación
  </a>
@endsection

@section('content')
<div class="max-w-7xl mx-auto">
    {{-- Header --}}
    <div class="card mb-6">
      <div class="border-b border-slate-200 pb-4">
        <h1 class="text-xl font-semibold text-slate-800 flex items-center gap-2">
          <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
          </svg>
          Centro de Notificaciones
        </h1>
        <p class="text-sm text-slate-600 mt-1">Historial unificado de correos y notificaciones push enviadas por el sistema.</p>
      </div>
    </div>

    @if(session('ok'))
        <div class="mb-6 p-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('ok') }}
        </div>
    @endif

    {{-- Stats Cards (Similar to Products) --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="card bg-blue-50 border-blue-200">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-blue-800">Total Envíos</p>
                    <p class="text-2xl font-bold text-blue-900">{{ $logs->total() }}</p>
                </div>
            </div>
        </div>
        
        <div class="card bg-emerald-50 border-emerald-200">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-emerald-100 rounded-lg">
                    <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-emerald-800">Exitosos</p>
                    <p class="text-2xl font-bold text-emerald-900">
                        {{ \App\Models\NotificationLog::where('status','sent')->count() }}
                    </p>
                </div>
            </div>
        </div>

        <div class="card bg-rose-50 border-rose-200">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-rose-100 rounded-lg">
                    <svg class="w-6 h-6 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-rose-800">Fallidos</p>
                    <p class="text-2xl font-bold text-rose-900">
                        {{ \App\Models\NotificationLog::where('status','failed')->count() }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabla estilo Products --}}
    <div class="card p-0 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr class="text-left">
                        <th class="px-4 py-3 font-semibold text-slate-700">Canal</th>
                        <th class="px-4 py-3 font-semibold text-slate-700">Destinatario</th>
                        <th class="px-4 py-3 font-semibold text-slate-700">Asunto / Contexto</th>
                        <th class="px-4 py-3 font-semibold text-slate-700">Estado</th>
                        <th class="px-4 py-3 font-semibold text-slate-700">Fecha</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-50 transition-colors">
                            {{-- Canal --}}
                            <td class="px-4 py-3">
                                @if($log->channel == 'email')
                                    <span class="inline-flex items-center px-2 py-1 rounded bg-blue-50 text-blue-700 text-xs font-medium">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                        Email
                                    </span>
                                @elseif($log->channel == 'push')
                                    <span class="inline-flex items-center px-2 py-1 rounded bg-purple-50 text-purple-700 text-xs font-medium">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                        Push
                                    </span>
                                @elseif($log->channel == 'whatsapp')
                                    <span class="inline-flex items-center px-2 py-1 rounded bg-emerald-50 text-emerald-700 text-xs font-medium">
                                        <i class="fab fa-whatsapp mr-1 text-sm"></i>
                                        WhatsApp
                                    </span>
                                @else
                                    <span class="text-slate-500">{{ $log->channel }}</span>
                                @endif
                            </td>

                            {{-- Destinatario --}}
                            <td class="px-4 py-3">
                                @if($log->user)
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-600 text-xs font-bold">
                                            {{ substr($log->user->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <p class="font-medium text-slate-800">{{ $log->user->name }}</p>
                                            <p class="text-xs text-slate-500">{{ $log->recipient }}</p>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-slate-600">{{ $log->recipient }}</span>
                                @endif
                            </td>

                            {{-- Asunto --}}
                            <td class="px-4 py-3">
                                <p class="text-slate-800 font-medium">
                                    {{ $log->payload['title'] ?? ucwords(str_replace('_', ' ', $log->type)) }}
                                </p>
                                @if($log->appointment_id)
                                    <a href="{{ route('admin.appointments.show', $log->appointment_id) }}" class="text-xs text-blue-600 hover:text-blue-800 hover:underline flex items-center gap-1 mt-0.5">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        Cita #{{ $log->appointment_id }}
                                    </a>
                                @endif
                            </td>

                            {{-- Estado --}}
                            <td class="px-4 py-3">
                                @if($log->status == 'sent')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        Enviado
                                    </span>
                                @elseif($log->status == 'failed')
                                    <div class="flex items-center gap-2 group relative">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-rose-100 text-rose-800 cursor-help">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            Falló
                                        </span>
                                        <div class="hidden group-hover:block absolute left-0 bottom-full mb-2 w-64 p-2 bg-slate-800 text-white text-xs rounded shadow-lg z-10">
                                            {{ $log->error_message }}
                                        </div>
                                    </div>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800">
                                        Pendiente
                                    </span>
                                @endif
                            </td>

                            {{-- Fecha --}}
                            <td class="px-4 py-3 text-slate-600">
                                <div class="flex flex-col">
                                    <span class="font-medium text-slate-700">{{ $log->sent_at ? $log->sent_at->format('d/m/Y') : '-' }}</span>
                                    <span class="text-xs text-slate-500">{{ $log->sent_at ? $log->sent_at->format('H:i') : '' }}</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center text-slate-500">
                                <svg class="w-16 h-16 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                </svg>
                                <h3 class="text-lg font-medium text-slate-700">Sin notificaciones</h3>
                                <p class="text-slate-500">No se han registrado envíos recientes.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Paginación --}}
    @if($logs->hasPages())
        <div class="mt-6">
            {{ $logs->links() }}
        </div>
    @endif
</div>
@endsection
