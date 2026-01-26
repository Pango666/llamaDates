@extends('layouts.app')

@section('title', 'Historial de Correos')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Historial de Correos</h1>
            <p class="text-slate-500">Registro de todas las notificaciones enviadas por el sistema.</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="px-6 py-4 font-semibold text-slate-700">Fecha</th>
                    <th class="px-6 py-4 font-semibold text-slate-700">Destinatario</th>
                    <th class="px-6 py-4 font-semibold text-slate-700">Asunto</th>
                    <th class="px-6 py-4 font-semibold text-slate-700">Estado</th>
                    <th class="px-6 py-4 font-semibold text-slate-700 text-right">Detalles</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($logs as $log)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-6 py-4 text-slate-600">
                        {{ $log->created_at->format('d/m/Y H:i') }}
                    </td>
                    <td class="px-6 py-4 font-medium text-slate-800">
                        {{ $log->to }}
                    </td>
                    <td class="px-6 py-4 text-slate-600">
                        {{ $log->subject }}
                    </td>
                    <td class="px-6 py-4">
                        @if($log->status === 'sent')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                Enviado
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700">
                                <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                Fallido
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        @if($log->error)
                            <button onclick="alert('Error: {{ addslashes($log->error) }}')" class="text-red-600 hover:text-red-700 font-medium text-xs underline">
                                Ver Error
                            </button>
                        @else
                             <span class="text-slate-400">-</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-slate-400 italic">
                        No hay registros de correos enviados aún.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        
        @if($logs->hasPages())
        <div class="px-6 py-4 border-t border-slate-100">
            {{ $logs->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
