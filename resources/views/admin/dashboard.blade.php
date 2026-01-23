@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
@php
  // Traducciones de estados (solo estos 6)
  $statusMap = [
    'reserved'   => 'Reservada',
    'confirmed'  => 'Confirmada',
    'in_service' => 'En atención',
    'done'       => 'Finalizada',
    'no_show'    => 'No asistió',
    'canceled'   => 'Cancelada',
  ];
@endphp

  <div class="max-w-7xl mx-auto">
    {{-- Header --}}
    <div class="card mb-6">
      <div class="border-b border-slate-200 pb-4">
        <h1 class="text-xl font-semibold text-slate-800 flex items-center gap-2">
          <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
          </svg>
          Panel de Control
        </h1>

        <p class="text-sm text-slate-600 mt-1">Resumen general y gestión de citas del día.</p>

        {{-- Fecha traducida (Blade/Carbon) --}}
        <p class="text-sm text-slate-600 mt-1">
          Hoy:
          <span class="font-medium text-slate-800">
            {{ $day->locale('es')->translatedFormat('l d F Y') }}
          </span>
        </p>
      </div>
    </div>

    {{-- KPIs Premium --}}
    <div class="grid gap-6 md:grid-cols-4 mb-8">
      {{-- Pacientes --}}
      <div class="card group bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-100 hover:shadow-lg hover:scale-[1.02] transition-all duration-300 cursor-pointer" onclick="window.location='{{ route('admin.patients.index') }}'">
        <div class="flex items-center gap-4">
          <div class="p-3 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl shadow-lg shadow-blue-500/25 group-hover:shadow-blue-500/40 transition-shadow">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
          </div>
          <div>
            <p class="text-sm font-medium text-blue-600">Pacientes</p>
            <p class="text-3xl font-bold text-slate-800">{{ $stats['patients'] }}</p>
          </div>
        </div>
        <div class="mt-3 pt-3 border-t border-blue-100">
          <span class="text-xs text-blue-600 font-medium group-hover:text-blue-700 inline-flex items-center gap-1">
            Ver todos
            <svg class="w-3 h-3 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
          </span>
        </div>
      </div>

      {{-- Odontólogos --}}
      <div class="card group bg-gradient-to-br from-emerald-50 to-teal-50 border border-emerald-100 hover:shadow-lg hover:scale-[1.02] transition-all duration-300 cursor-pointer" onclick="window.location='{{ route('admin.dentists') }}'">
        <div class="flex items-center gap-4">
          <div class="p-3 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl shadow-lg shadow-emerald-500/25 group-hover:shadow-emerald-500/40 transition-shadow">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <div>
            <p class="text-sm font-medium text-emerald-600">Odontólogos</p>
            <p class="text-3xl font-bold text-slate-800">{{ $stats['dentists'] }}</p>
          </div>
        </div>
        <div class="mt-3 pt-3 border-t border-emerald-100">
          <span class="text-xs text-emerald-600 font-medium group-hover:text-emerald-700 inline-flex items-center gap-1">
            Ver equipo
            <svg class="w-3 h-3 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
          </span>
        </div>
      </div>

      {{-- Servicios --}}
      <div class="card group bg-gradient-to-br from-amber-50 to-orange-50 border border-amber-100 hover:shadow-lg hover:scale-[1.02] transition-all duration-300 cursor-pointer" onclick="window.location='{{ route('admin.services') }}'">
        <div class="flex items-center gap-4">
          <div class="p-3 bg-gradient-to-br from-amber-500 to-orange-600 rounded-xl shadow-lg shadow-amber-500/25 group-hover:shadow-amber-500/40 transition-shadow">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
            </svg>
          </div>
          <div>
            <p class="text-sm font-medium text-amber-600">Servicios</p>
            <p class="text-3xl font-bold text-slate-800">{{ $stats['services'] }}</p>
          </div>
        </div>
        <div class="mt-3 pt-3 border-t border-amber-100">
          <span class="text-xs text-amber-600 font-medium group-hover:text-amber-700 inline-flex items-center gap-1">
            Ver catálogo
            <svg class="w-3 h-3 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
          </span>
        </div>
      </div>

      {{-- Citas Hoy --}}
      <div class="card group bg-gradient-to-br from-rose-50 to-pink-50 border border-rose-100 hover:shadow-lg hover:scale-[1.02] transition-all duration-300 cursor-pointer" onclick="window.location='{{ route('admin.appointments.index') }}'">
        <div class="flex items-center gap-4">
          <div class="p-3 bg-gradient-to-br from-rose-500 to-pink-600 rounded-xl shadow-lg shadow-rose-500/25 group-hover:shadow-rose-500/40 transition-shadow">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
          </div>
          <div>
            <p class="text-sm font-medium text-rose-600">Citas Hoy</p>
            <p class="text-3xl font-bold text-slate-800">{{ $stats['todayVisits'] }}</p>
          </div>
        </div>
        <div class="mt-3 pt-3 border-t border-rose-100">
          <span class="text-xs text-rose-600 font-medium group-hover:text-rose-700 inline-flex items-center gap-1">
            Ver agenda
            <svg class="w-3 h-3 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
          </span>
        </div>
      </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
      {{-- Calendario --}}
      <section class="lg:col-span-2">
        <div class="card" id="calendar-wrap">
          @include('admin.partials._calendar', ['month' => $month, 'day' => $day, 'perDay' => $perDay])
        </div>
      </section>

      {{-- Lista del día --}}
      <aside>
        <div class="card" id="daylist-wrap">
          @include('admin.partials._day_list', ['day' => $day, 'appointments' => $appointments])
        </div>
      </aside>
    </div>

    {{-- Metadatos para JS --}}
    <template id="dash-meta"
      data-url="{{ route('admin.dashboard.data') }}"
      data-month="{{ $month->format('Y-m') }}"
      data-day="{{ $day->toDateString() }}"></template>

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const meta = document.getElementById('dash-meta');
        if (!meta) return;

        const baseUrl = meta.dataset.url;
        let currentMonth = meta.dataset.month;
        let currentDay = meta.dataset.day;

        // ✅ Esto ya NO rompe Blade (porque es variable PHP, no array literal)
        const APPOINTMENT_STATUS_MAP = @json($statusMap, JSON_UNESCAPED_UNICODE);

        function translateStatus(status) {
            if (!status) return '-';
            const key = String(status).trim();
            return APPOINTMENT_STATUS_MAP[key] || key.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
        }

        function applyStatusTranslations(root = document) {
            root.querySelectorAll('[data-appointment-status]').forEach(function(el) {
                el.textContent = translateStatus(el.getAttribute('data-appointment-status'));
            });
        }

        function handleCalendarNavigation() {
            document.querySelectorAll('[data-nav]').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const action = this.dataset.nav;

                    let newMonth = currentMonth;

                    if (action === 'prev') {
                        const date = new Date(currentMonth + '-01T00:00:00');
                        date.setMonth(date.getMonth() - 1);
                        newMonth = date.toISOString().slice(0, 7);
                    } else if (action === 'next') {
                        const date = new Date(currentMonth + '-01T00:00:00');
                        date.setMonth(date.getMonth() + 1);
                        newMonth = date.toISOString().slice(0, 7);
                    } else if (action === 'today') {
                        const today = new Date();
                        newMonth = today.toISOString().slice(0, 7);
                        currentDay = today.toISOString().slice(0, 10);
                    }

                    updateCalendar(newMonth, currentDay);
                });
            });

            const calendarGrid = document.getElementById('calendar-grid');
            if (calendarGrid) {
                calendarGrid.addEventListener('click', function(e) {
                    const dayBtn = e.target.closest('[data-day]');
                    if (dayBtn) {
                        currentDay = dayBtn.dataset.day;
                        updateCalendar(currentMonth, currentDay);
                    }
                });
            }
        }

        function updateCalendar(month, day) {
            const url = `${baseUrl}?month=${month}&day=${day}`;

            const calendarWrap = document.getElementById('calendar-wrap');
            const daylistWrap = document.getElementById('daylist-wrap');

            if (calendarWrap) calendarWrap.style.opacity = '0.5';
            if (daylistWrap) daylistWrap.style.opacity = '0.5';

            fetch(url, {
                headers: {'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html'}
            })
            .then(r => {
                if (!r.ok) throw new Error(`HTTP ${r.status}`);
                return r.text();
            })
            .then(html => {
                const doc = new DOMParser().parseFromString(html, 'text/html');

                const newCalendar = doc.getElementById('calendar-wrap');
                const newDayList = doc.getElementById('daylist-wrap');

                if (newCalendar && calendarWrap) calendarWrap.innerHTML = newCalendar.innerHTML;
                if (newDayList && daylistWrap) daylistWrap.innerHTML = newDayList.innerHTML;

                currentMonth = month;
                currentDay = day;

                setTimeout(() => {
                    handleCalendarNavigation();
                    applyStatusTranslations(document);

                    if (calendarWrap) calendarWrap.style.opacity = '1';
                    if (daylistWrap) daylistWrap.style.opacity = '1';
                }, 50);
            })
            .catch(err => {
                console.error(err);
                if (calendarWrap) calendarWrap.style.opacity = '1';
                if (daylistWrap) daylistWrap.style.opacity = '1';
                alert('No se pudo actualizar el calendario. Recarga la página.');
            });
        }

        handleCalendarNavigation();
        applyStatusTranslations(document);
    });
    </script>
    @endpush
  </div>
@endsection
