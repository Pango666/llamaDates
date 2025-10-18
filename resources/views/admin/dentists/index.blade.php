@extends('layouts.app')
@section('title', 'Odontólogos - Gestión Dental')

@section('header-actions')
  <a href="{{ route('admin.dentists.create') }}" class="btn btn-primary flex items-center gap-2">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>
    Nuevo Odontólogo
  </a>
@endsection

@section('content')
<style>
  .avatar{width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:600;font-size:.875rem}
  .specialty-badge{background:#f8fafc;border:1px solid #e2e8f0;color:#475569;padding:2px 8px;border-radius:12px;font-size:.75rem;font-weight:500}
  .table-hover tr{transition:all .2s}
  .table-hover tr:hover{background:#f8fafc!important;transform:translateY(-1px);box-shadow:0 2px 8px rgba(0,0,0,.05)}
</style>

{{-- Encabezado --}}
<div class="mb-6">
  <div class="flex items-center justify-between mb-2">
    <h1 class="text-2xl font-bold text-slate-800">Gestión de Odontólogos</h1>
    <span class="text-sm text-slate-500">{{ $dentists->total() }} resultados</span>
  </div>
  <p class="text-slate-600">Administre el equipo odontológico y sus asignaciones</p>
</div>

{{-- Filtros (GET puro) --}}
<div class="card mb-6">
  <form id="filtersForm" method="GET" class="flex flex-col lg:flex-row gap-4 items-start lg:items-end">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 flex-1">
      {{-- Búsqueda texto --}}
      <div class="col-span-2">
        <label class="block text-xs font-medium text-slate-700 mb-1">Buscar</label>
        <div class="relative">
          <input type="text" name="q" value="{{ request('q') }}" placeholder="Nombre, email o especialidad"
                 class="pl-9 pr-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 w-full">
          <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
        </div>
      </div>

      {{-- Especialidad literal (opciones de ejemplo: usa las que TÚ guardas) --}}
      <div>
        <label class="block text-xs font-medium text-slate-700 mb-1">Especialidad</label>
        <select name="specialty" class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:ring-1 focus:ring-blue-500">
          <option value="">Todas</option>
          {{-- Si son literales exactos, pon exactamente los valores que guardas en DB --}}
          @foreach(['Ortodoncia','Periodoncia','Endodoncia','Cirugía','Estética Dental','Odontología General'] as $opt)
            <option value="{{ $opt }}" @selected(request('specialty')===$opt)>{{ $opt }}</option>
          @endforeach
        </select>
      </div>

      {{-- Estado: solo 2 --}}
      <div>
        <label class="block text-xs font-medium text-slate-700 mb-1">Estado</label>
        <select name="status" class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:ring-1 focus:ring-blue-500">
          <option value="">Todos</option>
          <option value="active"   @selected(request('status')==='active')>Activo</option>
          <option value="inactive" @selected(request('status')==='inactive')>Inactivo</option>
        </select>
      </div>

      {{-- Sillón --}}
      <div>
        <label class="block text-xs font-medium text-slate-700 mb-1">Sillón</label>
        <select name="chair" class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:ring-1 focus:ring-blue-500">
          <option value="">Todos</option>
          @foreach($chairs as $chair)
            <option value="{{ $chair->id }}" @selected((string)request('chair')===(string)$chair->id)>{{ $chair->name }}</option>
          @endforeach
        </select>
      </div>
    </div>

    <div class="flex gap-2">
      <button type="submit" class="btn btn-primary whitespace-nowrap">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
        </svg>
        Aplicar
      </button>
      <a href="{{ route('admin.dentists') }}" class="btn btn-ghost whitespace-nowrap">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
        Limpiar
      </a>
    </div>
  </form>
</div>

{{-- Stats reales del server --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
  <div class="bg-white rounded-lg border border-slate-200 p-4">
    <div class="flex items-center gap-3">
      <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
      </div>
      <div>
        <p class="text-sm font-medium text-slate-600">Activos</p>
        <p class="text-2xl font-bold text-slate-800">{{ $totals['active'] }}</p>
      </div>
    </div>
  </div>

  <div class="bg-white rounded-lg border border-slate-200 p-4">
    <div class="flex items-center gap-3">
      <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
        </svg>
      </div>
      <div>
        <p class="text-sm font-medium text-slate-600">Con sillón</p>
        <p class="text-2xl font-bold text-slate-800">{{ $totals['with_chair'] }}</p>
      </div>
    </div>
  </div>
</div>

{{-- Tabla --}}
<div class="card p-0 overflow-hidden">
  <div class="overflow-x-auto">
    <table class="min-w-full text-sm table-hover">
      <thead class="bg-slate-50 border-b border-slate-200">
        <tr class="text-left">
          <th class="px-4 py-3 font-semibold text-slate-700">Odontólogo</th>
          <th class="px-4 py-3 font-semibold text-slate-700">Especialidad</th>
          <th class="px-4 py-3 font-semibold text-slate-700">Sillón</th>
          <th class="px-4 py-3 font-semibold text-slate-700">Estado</th>
          <th class="px-4 py-3 font-semibold text-slate-700">Próx. citas</th>
          <th class="px-4 py-3 font-semibold text-slate-700 text-right">Acciones</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-200">
        @forelse($dentists as $d)
          <tr>
            <td class="px-4 py-3">
              <div class="flex items-center gap-3">
                <div class="avatar">
                  {{ mb_substr($d->name,0,1) }}{{ mb_substr(trim(mb_strstr($d->name,' ') ?: ''),0,1) }}
                </div>
                <div>
                  <a class="font-semibold text-slate-800 hover:text-blue-600 hover:underline" href="{{ route('admin.dentists.show',$d) }}">
                    {{ $d->name }}
                  </a>
                  @if(!empty($d->email))
                    <p class="text-xs text-slate-500 mt-1">{{ $d->email }}</p>
                  @endif
                </div>
              </div>
            </td>
            <td class="px-4 py-3">
              @if($d->specialty)
                <span class="specialty-badge">{{ $d->specialty }}</span>
              @else
                <span class="text-slate-400">—</span>
              @endif
            </td>
            <td class="px-4 py-3">
              @if($d->chair)
                <div class="flex items-center gap-2">
                  <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                  <span class="font-medium">{{ $d->chair->name }}</span>
                </div>
              @else
                <div class="flex items-center gap-2">
                  <div class="w-2 h-2 bg-gray-400 rounded-full"></div>
                  <span class="font-medium">No asignado</span>
                </div>
              @endif
            </td>
            <td class="px-4 py-3">
              @php
                // Muestra estado según tu columna real:
                $isActive = (int)($d->is_active ?? $d->status ?? 0) === 1;
              @endphp
              <span class="px-2 py-1 rounded-full text-xs font-medium {{ $isActive?'bg-emerald-100 text-emerald-700':'bg-amber-100 text-amber-700' }}">
                {{ $isActive?'Activo':'Inactivo' }}
              </span>
            </td>
            <td class="px-4 py-3">
              <div class="flex items-center gap-2">
                <span class="font-semibold text-slate-800">{{ $nextCounts[$d->id] ?? 0 }}</span>
                <span class="text-xs text-slate-500">desde hoy</span>
              </div>
            </td>
            <td class="px-4 py-3">
              <div class="flex items-center justify-end gap-1">
                <a class="btn btn-ghost btn-sm" href="{{ route('admin.dentists.show',$d) }}" title="Ver perfil">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                  </svg>
                </a>
                <a class="btn btn-ghost btn-sm" href="{{ route('admin.dentists.edit',$d) }}" title="Editar">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                  </svg>
                </a>
                <form method="post" action="{{ route('admin.dentists.destroy',$d) }}" onsubmit="return confirm('¿Eliminar odontólogo?');">
                  @csrf @method('DELETE')
                  <button class="btn btn-danger btn-sm" type="submit" title="Eliminar">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                  </button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="px-4 py-8 text-center text-slate-500">
              No se encontraron resultados con los filtros aplicados.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

{{-- Paginación --}}
@if($dentists->hasPages())
  <div class="mt-6 flex flex-col sm:flex-row items-center justify-between gap-4">
    <div class="text-sm text-slate-600">
      Mostrando {{ $dentists->firstItem() }} - {{ $dentists->lastItem() }} de {{ $dentists->total() }}
    </div>
    <div class="flex items-center gap-2">
      {{ $dentists->links() }}
    </div>
  </div>
@endif

{{-- Auto-submit del buscador con debounce --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('filtersForm');
  const q    = form.querySelector('input[name="q"]');
  let t;
  q?.addEventListener('input', () => {
    clearTimeout(t);
    t = setTimeout(() => form.submit(), 400);
  });
});
</script>
@endsection
