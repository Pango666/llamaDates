@extends('layouts.app')
@section('title','Registros de Auditoría')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

  {{-- Header --}}
  <div class="card">
    <div class="border-b border-slate-200 pb-4">
      <h1 class="text-xl font-semibold text-slate-800 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center">
          <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
          </svg>
        </div>
        Registros de Auditoría
      </h1>
      <p class="text-sm text-slate-600 mt-2 ml-13">
        Historial de todas las acciones realizadas en el sistema. Quién creó, modificó o desactivó cada registro.
      </p>
    </div>
  </div>

  {{-- Filtros --}}
  <div class="card">
    <form method="get" class="grid gap-4 md:grid-cols-3 lg:grid-cols-6 items-end">
      {{-- Búsqueda --}}
      <div class="col-span-1 md:col-span-2">
        <label class="block text-xs font-medium text-slate-700 mb-1">Buscar registro</label>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Nombre del registro..."
               class="w-full border-slate-300 rounded-lg text-sm">
      </div>

      {{-- Usuario --}}
      <div>
        <label class="block text-xs font-medium text-slate-700 mb-1">Usuario</label>
        <select name="user_id" class="w-full border-slate-300 rounded-lg text-sm">
          <option value="">Todos</option>
          @foreach($users as $u)
            <option value="{{ $u->id }}" @selected(request('user_id') == $u->id)>{{ $u->name }}</option>
          @endforeach
        </select>
      </div>

      {{-- Acción --}}
      <div>
        <label class="block text-xs font-medium text-slate-700 mb-1">Acción</label>
        <select name="action" class="w-full border-slate-300 rounded-lg text-sm">
          <option value="">Todas</option>
          <option value="created" @selected(request('action') == 'created')>Creó</option>
          <option value="updated" @selected(request('action') == 'updated')>Modificó</option>
          <option value="deleted" @selected(request('action') == 'deleted')>Eliminó</option>
          <option value="toggled" @selected(request('action') == 'toggled')>Activó/Desactivó</option>
        </select>
      </div>

      {{-- Módulo --}}
      <div>
        <label class="block text-xs font-medium text-slate-700 mb-1">Módulo</label>
        <select name="model" class="w-full border-slate-300 rounded-lg text-sm">
          <option value="">Todos</option>
          @foreach($models as $key => $label)
            <option value="{{ $key }}" @selected(request('model') == $key)>{{ $label }}</option>
          @endforeach
        </select>
      </div>

      {{-- Desde --}}
      <div>
        <label class="block text-xs font-medium text-slate-700 mb-1">Desde</label>
        <input type="date" name="from" value="{{ request('from') }}" class="w-full border-slate-300 rounded-lg text-sm">
      </div>

      {{-- Hasta --}}
      <div>
        <label class="block text-xs font-medium text-slate-700 mb-1">Hasta</label>
        <input type="date" name="to" value="{{ request('to') }}" class="w-full border-slate-300 rounded-lg text-sm">
      </div>

      {{-- Botones --}}
      <div class="col-span-1 md:col-span-2 flex gap-2">
        <button class="btn bg-blue-600 text-white hover:bg-blue-700 text-sm h-10 px-4">Filtrar</button>
        @if(request()->anyFilled(['search', 'user_id', 'action', 'model', 'from', 'to']))
          <a href="{{ route('admin.audit.index') }}" class="btn btn-ghost text-sm h-10 px-4">Limpiar</a>
        @endif
      </div>
    </form>
  </div>

  {{-- Tabla de Logs --}}
  <div class="card p-0 overflow-hidden">
    @if($logs->count())
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-slate-50 border-b border-slate-200">
            <tr>
              <th class="px-4 py-3 text-left font-semibold text-slate-700">Fecha</th>
              <th class="px-4 py-3 text-left font-semibold text-slate-700">Usuario</th>
              <th class="px-4 py-3 text-left font-semibold text-slate-700">Acción</th>
              <th class="px-4 py-3 text-left font-semibold text-slate-700">Módulo</th>
              <th class="px-4 py-3 text-left font-semibold text-slate-700">Registro</th>
              <th class="px-4 py-3 text-left font-semibold text-slate-700">Detalles</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            @foreach($logs as $log)
              @php
                $actionColor = match($log->action) {
                    'created' => 'bg-emerald-100 text-emerald-800',
                    'updated' => 'bg-blue-100 text-blue-800',
                    'deleted' => 'bg-rose-100 text-rose-800',
                    'toggled' => 'bg-amber-100 text-amber-800',
                    default   => 'bg-slate-100 text-slate-800',
                };
                $actionIcon = match($log->action) {
                    'created' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>',
                    'updated' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>',
                    'deleted' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>',
                    'toggled' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>',
                    default   => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
                };
              @endphp
              <tr class="hover:bg-slate-50/50 transition-colors">
                {{-- Fecha --}}
                <td class="px-4 py-3 text-slate-600 whitespace-nowrap text-xs">
                  <div>{{ $log->created_at->format('d/m/Y') }}</div>
                  <div class="text-slate-400">{{ $log->created_at->format('H:i:s') }}</div>
                </td>

                {{-- Usuario --}}
                <td class="px-4 py-3">
                  <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-full bg-gradient-to-br from-violet-400 to-purple-500 flex items-center justify-center text-white text-xs font-bold">
                      {{ $log->user ? strtoupper(substr($log->user->name, 0, 1)) : '?' }}
                    </div>
                    <span class="text-sm font-medium text-slate-800">{{ $log->user->name ?? 'Sistema' }}</span>
                  </div>
                </td>

                {{-- Acción --}}
                <td class="px-4 py-3">
                  <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold {{ $actionColor }}">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $actionIcon !!}</svg>
                    {{ $log->action_label }}
                  </span>
                </td>

                {{-- Módulo --}}
                <td class="px-4 py-3">
                  <span class="text-xs font-medium text-slate-600 bg-slate-100 px-2 py-1 rounded-lg">
                    {{ $log->model_name }}
                  </span>
                </td>

                {{-- Registro afectado --}}
                <td class="px-4 py-3">
                  <div class="text-sm font-medium text-slate-800">{{ $log->auditable_label ?: '#' . $log->auditable_id }}</div>
                  <div class="text-[10px] text-slate-400">ID: {{ $log->auditable_id }}</div>
                </td>

                {{-- Detalles (cambios) --}}
                <td class="px-4 py-3">
                  @if($log->action === 'updated' && !empty($log->changed_fields))
                    <button onclick="toggleDetails({{ $log->id }})"
                            class="text-xs text-blue-600 hover:text-blue-800 font-medium flex items-center gap-1">
                      <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                      </svg>
                      {{ count($log->changed_fields) }} campo(s)
                    </button>
                    <div id="details-{{ $log->id }}" class="hidden mt-2 p-2 bg-slate-50 rounded-lg border text-xs max-w-xs">
                      @foreach($log->changed_fields as $field => $change)
                        <div class="mb-1.5 last:mb-0">
                          <span class="font-semibold text-slate-700">{{ $field }}:</span>
                          <div class="flex items-center gap-1 mt-0.5">
                            <span class="line-through text-rose-500">{{ Str::limit($change['old'] ?? '(vacío)', 40) }}</span>
                            <svg class="w-3 h-3 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                            <span class="text-emerald-600 font-medium">{{ Str::limit($change['new'] ?? '(vacío)', 40) }}</span>
                          </div>
                        </div>
                      @endforeach
                    </div>
                  @elseif($log->action === 'created')
                    <span class="text-xs text-emerald-600">Registro nuevo</span>
                  @elseif($log->action === 'deleted')
                    <span class="text-xs text-rose-600">Registro eliminado</span>
                  @elseif($log->action === 'toggled')
                    @php
                      $newActive = $log->new_values['is_active'] ?? null;
                    @endphp
                    <span class="text-xs {{ $newActive ? 'text-emerald-600' : 'text-rose-600' }}">
                      {{ $newActive ? 'Activado' : 'Desactivado' }}
                    </span>
                  @else
                    <span class="text-xs text-slate-400">—</span>
                  @endif
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @else
      <div class="text-center py-12">
        <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-4">
          <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
          </svg>
        </div>
        <h4 class="font-semibold text-slate-700 mb-1">No hay registros de auditoría</h4>
        <p class="text-sm text-slate-500">Las acciones del sistema aparecerán aquí automáticamente.</p>
      </div>
    @endif
  </div>

  @if($logs->hasPages())
    <div class="mt-4">
      {{ $logs->links() }}
    </div>
  @endif

</div>

@push('scripts')
<script>
function toggleDetails(id) {
    const el = document.getElementById('details-' + id);
    if (el) el.classList.toggle('hidden');
}
</script>
@endpush
@endsection
