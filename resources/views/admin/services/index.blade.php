@extends('layouts.app')
@section('title', 'Servicios')

@section('header-actions')
    <a href="{{ route('admin.services.create') }}" class="btn btn-primary flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        Nuevo servicio
    </a>
@endsection

@section('content')
    <!-- Filtros mejorados -->
    <form method="GET" id="filtersForm" class="contents">
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
            <div class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Búsqueda -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Buscar servicio</label>
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-slate-400" 
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <input type="text" 
                               name="q" 
                               value="{{ $q ?? '' }}" 
                               placeholder="Nombre del servicio..."
                               class="w-full pl-10 pr-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                    </div>
                </div>

                <!-- Estado -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Estado</label>
                    <select name="state" 
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                        <option value="all" @selected(($state ?? 'all') === 'all')>Todos los estados</option>
                        <option value="active" @selected(($state ?? 'all') === 'active')>Activos</option>
                        <option value="inactive" @selected(($state ?? 'all') === 'inactive')>Inactivos</option>
                    </select>
                </div>

                <!-- Ordenamiento -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Ordenar por</label>
                    <select name="sort" 
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                        <option value="name" @selected(($sort ?? 'name') === 'name')>Nombre A-Z</option>
                        <option value="price" @selected(($sort ?? 'name') === 'price')>Precio</option>
                        <option value="duration" @selected(($sort ?? 'name') === 'duration')>Duración</option>
                        <option value="created_at" @selected(($sort ?? 'name') === 'created_at')>Más recientes</option>
                    </select>
                </div>
            </div>

            <!-- Botones de acción -->
            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary flex items-center gap-2" id="applyFilters">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    Filtrar
                </button>
                
                @if(($q ?? '') !== '' || ($state ?? 'all') !== 'all' || ($sort ?? 'name') !== 'name')
                    <a href="{{ route('admin.services') }}" class="btn btn-outline flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Limpiar
                    </a>
                @endif
            </div>
        </div>
    </form>

    <!-- Estadísticas rápidas -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="card bg-gradient-to-br from-blue-50 to-blue-100 border-blue-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-blue-700">Total servicios</p>
                    <p class="text-2xl font-bold text-blue-900">{{ $services->total() }}</p>
                </div>
                <div class="p-3 bg-blue-500 rounded-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="card bg-gradient-to-br from-emerald-50 to-emerald-100 border-emerald-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-emerald-700">Activos</p>
                    <p class="text-2xl font-bold text-emerald-900">{{ $activeCount ?? 0 }}</p>
                </div>
                <div class="p-3 bg-emerald-500 rounded-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="card bg-gradient-to-br from-slate-50 to-slate-100 border-slate-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-700">Inactivos</p>
                    <p class="text-2xl font-bold text-slate-900">{{ $inactiveCount ?? 0 }}</p>
                </div>
                <div class="p-3 bg-slate-500 rounded-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="card bg-gradient-to-br from-purple-50 to-purple-100 border-purple-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-purple-700">Precio promedio</p>
                    <p class="text-2xl font-bold text-purple-900">${{ number_format($averagePrice ?? 0, 2) }}</p>
                </div>
                <div class="p-3 bg-purple-500 rounded-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de servicios - Vista de tarjetas -->
    @if($services->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($services as $service)
                <div class="card hover:shadow-lg transition-all duration-300 border-l-4 
                           {{ $service->active ? 'border-l-emerald-500' : 'border-l-slate-400' }}">
                    <div class="flex justify-between items-start mb-4">
                        <h3 class="text-lg font-semibold text-slate-900 truncate">{{ $service->name }}</h3>
                        <span class="badge {{ $service->active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-700' }} text-xs">
                            {{ $service->active ? 'Activo' : 'Inactivo' }}
                        </span>
                    </div>

                    <div class="space-y-3 mb-4">
                        <div class="flex items-center gap-2 text-slate-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span class="text-sm">{{ $service->duration_min }} minutos</span>
                        </div>

                        <div class="flex items-center gap-2 text-slate-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                            </svg>
                            <span class="text-sm font-semibold text-green-600">${{ number_format($service->price, 2) }}</span>
                        </div>
                    </div>

                    <div class="flex gap-2 pt-4 border-t border-slate-200">
                        <a href="{{ route('admin.services.edit', $service) }}" 
                           class="btn btn-ghost btn-sm flex items-center gap-1 flex-1 justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            Editar
                        </a>

                        <form method="post" action="{{ route('admin.services.toggle', $service) }}" class="flex-1">
                            @csrf
                            <button type="submit" 
                                    class="w-full btn btn-outline btn-sm flex items-center gap-1 justify-center 
                                           {{ $service->active ? 'text-orange-600 border-orange-300 hover:bg-orange-50' : 'text-emerald-600 border-emerald-300 hover:bg-emerald-50' }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                {{ $service->active ? 'Desactivar' : 'Activar' }}
                            </button>
                        </form>

                        <form method="post" 
                              action="{{ route('admin.services.destroy', $service) }}"
                              onsubmit="return confirm('¿Estás seguro de eliminar este servicio? Esta acción no se puede deshacer.');"
                              class="flex-1">
                            @csrf @method('DELETE')
                            <button type="submit" 
                                    class="w-full btn btn-danger btn-sm flex items-center gap-1 justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                Eliminar
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <!-- Estado vacío mejorado -->
        <div class="card text-center py-12">
            <div class="max-w-md mx-auto">
                <svg class="w-16 h-16 text-slate-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                <h3 class="text-lg font-semibold text-slate-900 mb-2">No se encontraron servicios</h3>
                <p class="text-slate-600 mb-6">No hay servicios que coincidan con tus criterios de búsqueda.</p>
                <a href="{{ route('admin.services.create') }}" class="btn btn-primary">
                    Crear primer servicio
                </a>
            </div>
        </div>
    @endif

    <!-- Paginación mejorada -->
    @if($services->hasPages())
        <div class="mt-8 flex flex-col sm:flex-row items-center justify-between gap-4">
            <p class="text-sm text-slate-600">
                Mostrando {{ $services->firstItem() }} - {{ $services->lastItem() }} de {{ $services->total() }} servicios
            </p>
            <div class="flex gap-2">
                {{ $services->links() }}
            </div>
        </div>
    @endif

    <style>
        .card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            border: 1px solid #e2e8f0;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 500;
            font-size: 0.875rem;
            transition: all 0.2s;
            text-decoration: none;
            border: 1px solid transparent;
        }

        .btn-primary {
            background: #3b82f6;
            color: white;
            border-color: #3b82f6;
        }

        .btn-primary:hover {
            background: #2563eb;
            border-color: #2563eb;
        }

        .btn-outline {
            background: transparent;
            color: #64748b;
            border-color: #cbd5e1;
        }

        .btn-outline:hover {
            background: #f8fafc;
            border-color: #94a3b8;
        }

        .btn-ghost {
            background: transparent;
            color: #64748b;
            border-color: transparent;
        }

        .btn-ghost:hover {
            background: #f1f5f9;
            color: #475569;
        }

        .btn-danger {
            background: #ef4444;
            color: white;
            border-color: #ef4444;
        }

        .btn-danger:hover {
            background: #dc2626;
            border-color: #dc2626;
        }

        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.75rem;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
        }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
      const form = document.getElementById('filtersForm');

      // Búsqueda rápida con debounce
      const quickSearch = document.querySelector('input[name="q"]');
      let t;
      if (quickSearch) {
        quickSearch.addEventListener('input', function() {
          clearTimeout(t);
          t = setTimeout(() => form.submit(), 400);
        });
      }

      // (Opcional) botón limpiar si lo agregas con id="clearFilters"
      const clearBtn = document.getElementById('clearFilters');
      if (clearBtn) {
        clearBtn.addEventListener('click', function(e) {
          e.preventDefault();
          form.reset();
          window.location = "{{ route('admin.services') }}";
        });
      }
    });
    </script>
@endsection
