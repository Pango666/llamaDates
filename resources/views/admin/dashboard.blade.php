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

    {{-- KPIs --}}
    <div class="grid gap-6 md:grid-cols-4 mb-8">
      {{-- Pacientes --}}
      <div class="card bg-blue-50 border-blue-200">
        <div class="flex items-center gap-4">
          <div class="p-3 bg-blue-100 rounded-lg">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
          </div>
          <div>
            <p class="text-sm font-medium text-blue-800">Pacientes</p>
            <p class="text-2xl font-bold text-blue-900">{{ $stats['patients'] }}</p>
          </div>
        </div>
      </div>

      {{-- Odontólogos --}}
      <div class="card bg-emerald-50 border-emerald-200">
        <div class="flex items-center gap-4">
          <div class="p-3 bg-emerald-100 rounded-lg">
            <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
          </div>
          <div>
            <p class="text-sm font-medium text-emerald-800">Odontólogos</p>
            <p class="text-2xl font-bold text-emerald-900">{{ $stats['dentists'] }}</p>
          </div>
        </div>
      </div>

      {{-- Servicios --}}
      <div class="card bg-amber-50 border-amber-200">
        <div class="flex items-center gap-4">
          <div class="p-3 bg-amber-100 rounded-lg">
            <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
          </div>
          <div>
            <p class="text-sm font-medium text-amber-800">Servicios</p>
            <p class="text-2xl font-bold text-amber-900">{{ $stats['services'] }}</p>
          </div>
        </div>
      </div>

      {{-- Citas Hoy --}}
      <div class="card bg-rose-50 border-rose-200">
        <div class="flex items-center gap-4">
          <div class="p-3 bg-rose-100 rounded-lg">
            <svg class="w-6 h-6 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
          </div>
          <div>
            <p class="text-sm font-medium text-rose-800">Citas Hoy</p>
            <p class="text-2xl font-bold text-rose-900">{{ $stats['todayVisits'] }}</p>
          </div>
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
