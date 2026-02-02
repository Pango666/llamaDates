@extends('layouts.app')
@section('title','Movimientos de Inventario')

@section('header-actions')
  <div class="flex gap-2">
      <div class="dropdown relative group">
          <button class="btn bg-slate-100 text-slate-700 hover:bg-slate-200 flex items-center gap-2 border border-slate-300">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
              Reportes
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
          </button>
          
          {{-- Dropdown Menu with Bridge --}}
          <div class="absolute right-0 top-full pt-1 w-48 hidden group-hover:block z-50">
              <div class="bg-white rounded-lg shadow-xl border border-slate-100 overflow-hidden">
                  <a href="{{ route('admin.inv.movs.export.pdf', request()->all()) }}" class="block px-4 py-2 text-slate-700 hover:bg-blue-50 hover:text-blue-600">Exportar PDF</a>
                  <a href="{{ route('admin.inv.movs.export.csv', request()->all()) }}" class="block px-4 py-2 text-slate-700 hover:bg-blue-50 hover:text-blue-600">Exportar Excel (CSV)</a>
              </div>
          </div>
      </div>

      <a href="{{ route('admin.inv.movs.create') }}" class="btn bg-blue-600 text-white hover:bg-blue-700 flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Nuevo Movimiento
      </a>
  </div>
@endsection

@section('content')
  <div class="max-w-7xl mx-auto">

    {{-- Header --}}
    <div class="card mb-6">
      <div class="border-b border-slate-200 pb-4">
        <h1 class="text-xl font-semibold text-slate-800 flex items-center gap-2">
          <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h10M4 18h8"/>
          </svg>
          Movimientos de Inventario
        </h1>
        <p class="text-sm text-slate-600 mt-1">
          Historial completo de entradas, salidas y ajustes. Usa los filtros para generar reportes específicos.
        </p>
      </div>
    </div>

    {{-- KPIs --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        {{-- Total --}}
        <div class="card bg-slate-50 border-slate-200">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-slate-200 rounded-lg">
                    <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <div>
                   <p class="text-xs font-semibold text-slate-500 uppercase">Movimientos</p>
                   <p class="text-2xl font-bold text-slate-900">{{ number_format($stats['total_moves']) }}</p>
                </div>
            </div>
        </div>
        {{-- Entradas --}}
        <div class="card bg-emerald-50 border-emerald-200">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-emerald-100 rounded-lg">
                    <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                </div>
                <div>
                   <p class="text-xs font-semibold text-emerald-600 uppercase">Entradas</p>
                   <p class="text-2xl font-bold text-emerald-900">{{ number_format($stats['total_in']) }}</p>
                </div>
            </div>
        </div>
        {{-- Salidas --}}
        <div class="card bg-rose-50 border-rose-200">
             <div class="flex items-center gap-3">
                <div class="p-2 bg-rose-100 rounded-lg">
                    <svg class="w-6 h-6 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                </div>
                <div>
                   <p class="text-xs font-semibold text-rose-600 uppercase">Salidas</p>
                   <p class="text-2xl font-bold text-rose-900">{{ number_format($stats['total_out']) }}</p>
                </div>
            </div>
        </div>
        {{-- Costo --}}
        <div class="card bg-blue-50 border-blue-200">
             <div class="flex items-center gap-3">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                   <p class="text-xs font-semibold text-blue-600 uppercase">Inv. Ingresado</p>
                   <p class="text-xl font-bold text-blue-900">Bs {{ number_format($stats['total_cost'], 2) }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="card mb-6">
      <form method="get" class="grid gap-4 md:grid-cols-4 lg:grid-cols-8 items-end">
        {{-- Producto --}}
        <div class="col-span-1 md:col-span-2">
          <label class="block text-xs font-medium text-slate-700 mb-1">Producto</label>
          <select name="product_id" class="w-full border-slate-300 rounded-lg text-sm">
            <option value="0">Todos</option>
            @foreach($products as $p)
              <option value="{{ $p->id }}" @selected(request('product_id') == $p->id)>
                {{ $p->name }} {{ $p->sku ? "($p->sku)" : '' }}
              </option>
            @endforeach
          </select>
        </div>

        {{-- Ubicación --}}
        <div class="col-span-1 md:col-span-2">
          <label class="block text-xs font-medium text-slate-700 mb-1">Ubicación</label>
          <select name="location_id" class="w-full border-slate-300 rounded-lg text-sm">
            <option value="0">Todas</option>
            @foreach($locations as $loc)
              <option value="{{ $loc->id }}" @selected(request('location_id') == $loc->id)>{{ $loc->name }}</option>
            @endforeach
          </select>
        </div>

        {{-- Usuario --}}
        <div class="col-span-1 md:col-span-2">
          <label class="block text-xs font-medium text-slate-700 mb-1">Usuario</label>
          <select name="user_id" class="w-full border-slate-300 rounded-lg text-sm">
            <option value="0">Todos</option>
            @foreach($users as $u)
              <option value="{{ $u->id }}" @selected(request('user_id') == $u->id)>{{ $u->name }}</option>
            @endforeach
          </select>
        </div>

        {{-- Tipo --}}
        <div class="col-span-1 md:col-span-2">
          <label class="block text-xs font-medium text-slate-700 mb-1">Tipo</label>
          <select name="type" class="w-full border-slate-300 rounded-lg text-sm">
            <option value="all">Todos</option>
            <option value="in" @selected(request('type') == 'in')>Entrada</option>
            <option value="out" @selected(request('type') == 'out')>Salida</option>
            <option value="adjust" @selected(request('type') == 'adjust')>Ajuste</option>
          </select>
        </div>

        {{-- Fechas --}}
        <div class="col-span-1 md:col-span-2">
            <label class="block text-xs font-medium text-slate-700 mb-1">Desde</label>
            <input type="date" name="from" value="{{ request('from') }}" class="w-full border-slate-300 rounded-lg text-sm">
        </div>
        <div class="col-span-1 md:col-span-2">
            <label class="block text-xs font-medium text-slate-700 mb-1">Hasta</label>
            <input type="date" name="to" value="{{ request('to') }}" class="w-full border-slate-300 rounded-lg text-sm">
        </div>

        {{-- Botones --}}
        <div class="col-span-1 md:col-span-4 flex gap-2">
          <button class="btn bg-blue-600 text-white hover:bg-blue-700 text-sm h-10 px-4">Filtrar</button>
          @if(request()->anyFilled(['product_id', 'location_id', 'user_id', 'type', 'from', 'to']))
            <a href="{{ route('admin.inv.movs.index') }}" class="btn btn-ghost text-sm h-10 px-4">Limpiar</a>
          @endif
        </div>
      </form>
    </div>

    {{-- Tabla --}}
    <div class="card p-0 overflow-hidden">
      @if($movs->count())
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
              <tr>
                <th class="px-4 py-3 text-left font-semibold text-slate-700">Fecha</th>
                <th class="px-4 py-3 text-left font-semibold text-slate-700">Producto</th>
                <th class="px-4 py-3 text-left font-semibold text-slate-700">Tipo</th>
                <th class="px-4 py-3 text-right font-semibold text-slate-700">Cantidad</th>
                <th class="px-4 py-3 text-left font-semibold text-slate-700">Ubicación</th>
                <th class="px-4 py-3 text-left font-semibold text-slate-700">Usuario</th>
                <th class="px-4 py-3 text-left font-semibold text-slate-700">Detalles</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
              @foreach($movs as $mov)
                <tr class="hover:bg-slate-50">
                  <td class="px-4 py-3 text-slate-600 whitespace-nowrap">
                    {{ $mov->created_at->format('d/m/Y H:i') }}
                  </td>
                  <td class="px-4 py-3">
                    <div class="font-medium text-slate-900">{{ $mov->product->name ?? 'Producto Eliminado' }}</div>
                    <div class="text-xs text-slate-500">{{ $mov->product->sku ?? '' }}</div>
                  </td>
                  <td class="px-4 py-3">
                     @php
                      $color = match($mov->type) {
                        'in' => 'bg-emerald-100 text-emerald-800',
                        'out' => 'bg-rose-100 text-rose-800',
                        'adjust' => 'bg-amber-100 text-amber-800',
                        default => 'bg-slate-100 text-slate-800'
                      };
                      $label = match($mov->type) {
                          'in' => 'Entrada',
                          'out' => 'Salida',
                          'adjust' => 'Ajuste',
                          default => ucfirst($mov->type)
                      };
                    @endphp
                    <span class="inline-flex px-2 py-1 rounded-full text-xs font-semibold {{ $color }}">
                        {{ $label }}
                    </span>
                  </td>
                  <td class="px-4 py-3 text-right">
                    <span class="font-bold {{ $mov->type == 'out' ? 'text-rose-600' : 'text-emerald-600' }}">
                        {{ $mov->type == 'out' ? '-' : '+' }}{{ number_format($mov->qty) }}
                    </span>
                    <span class="text-xs text-slate-500 block">{{ $mov->product->unit ?? '' }}</span>
                  </td>
                  <td class="px-4 py-3 text-slate-600 text-xs">
                     <div class="flex items-center gap-1">
                        <svg class="w-3 h-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        {{ $mov->location->name ?? '—' }}
                     </div>
                  </td>
                  <td class="px-4 py-3 text-slate-600 text-xs">
                     <div class="flex items-center gap-1">
                        <svg class="w-3 h-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        {{ $mov->user->name ?? 'Sistema' }}
                     </div>
                  </td>
                  <td class="px-4 py-3 text-xs text-slate-500">
                     @if($mov->lot) <div><span class="font-semibold">Lote:</span> {{ $mov->lot }}</div> @endif
                     @if($mov->expires_at) <div><span class="font-semibold">Vence:</span> {{ $mov->expires_at->format('d/m/Y') }}</div> @endif
                     @if($mov->unit_cost && $mov->type=='in') <div class="text-emerald-600">Bs {{ number_format($mov->unit_cost, 2) }}</div> @endif
                     @if($mov->note) <div class="italic text-slate-400 mt-1">"{{ Str::limit($mov->note, 30) }}"</div> @endif
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @else
        <div class="text-center py-12">
            <svg class="w-16 h-16 text-slate-200 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
            <p class="text-slate-500">No exiten movimientos para esta búsqueda.</p>
        </div>
      @endif
    </div>

    @if($movs->hasPages())
      <div class="mt-4">
        {{ $movs->links() }}
      </div>
    @endif
  </div>
@endsection
