@extends('patient.layout')
@section('title','Mi perfil')

@section('header-actions')
  <a href="{{ route('app.appointments.index') }}" class="btn btn-ghost">
    <i class="fas fa-calendar-check"></i>
    Mis citas
  </a>
@endsection

@section('content')
  @php
    use Carbon\Carbon;

    $tabs = [
      'datos'    => ['label' => 'Datos',        'icon' => 'fa-user'],
      'password' => ['label' => 'Contraseña',   'icon' => 'fa-lock'],
      'historia' => ['label' => 'Historia clínica', 'icon' => 'fa-notes-medical'],
    ];

    $tab = request('tab','datos');

    $isActive = function($t) use ($tab) {
      return $tab === $t
        ? 'bg-blue-50 text-blue-700 border-blue-200'
        : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50';
    };

    $pillBase = 'inline-flex items-center gap-2 px-3 py-2 rounded-lg border text-sm transition';
    $inputBase = 'w-full border border-slate-200 rounded-lg px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-200';
    $labelBase = 'block text-xs text-slate-500 mb-1';
    $helpBase  = 'text-xs text-slate-500 mt-2';
  @endphp

  <div class="max-w-5xl mx-auto space-y-4">

    {{-- Tarjeta superior --}}
    <div class="card border border-slate-200">
      <div class="flex items-start justify-between gap-3 flex-wrap">
        <div>
          <div class="text-xs uppercase tracking-wide text-slate-500">Cuenta</div>
          <div class="text-lg font-semibold text-slate-800">{{ $user->name }}</div>
          <div class="text-xs text-slate-500 mt-1">
            <i class="fas fa-envelope"></i>
            {{ $user->email }}
          </div>
        </div>

        {{-- Tabs --}}
        <div class="flex flex-wrap gap-2">
          @foreach($tabs as $key => $t)
            <a href="{{ route('app.profile',['tab'=>$key]) }}"
               class="{{ $pillBase }} {{ $isActive($key) }}">
              <i class="fas {{ $t['icon'] }}"></i>
              {{ $t['label'] }}
            </a>
          @endforeach
        </div>
      </div>
    </div>

    @if($tab==='datos')
      {{-- ================== DATOS ================== --}}
      <div class="grid lg:grid-cols-2 gap-4">
        <section class="card border border-slate-200">
          <div class="flex items-center justify-between mb-3">
            <h3 class="font-semibold text-slate-800 flex items-center gap-2">
              <i class="fas fa-user text-slate-500"></i>
              Mi usuario
            </h3>
          </div>

          <form method="post" action="{{ route('app.profile.update') }}" class="grid gap-3">
            @csrf

            <div>
              <label class="{{ $labelBase }}">Nombre para mostrar</label>
              <input name="name" class="{{ $inputBase }}" value="{{ old('name',$user->name) }}" required>
            </div>

            <div>
              <label class="{{ $labelBase }}">Email</label>
              <input class="{{ $inputBase }} bg-slate-50" value="{{ $user->email }}" disabled>
            </div>

            <div class="pt-1">
              <button class="btn btn-primary">
                <i class="fas fa-save"></i>
                Guardar cambios
              </button>
            </div>
          </form>

          <p class="{{ $helpBase }}">
            * El email no se puede cambiar desde aquí.
          </p>
        </section>

        <section class="card border border-slate-200">
          <h3 class="font-semibold text-slate-800 flex items-center gap-2 mb-3">
            <i class="fas fa-id-card text-slate-500"></i>
            Datos del paciente
          </h3>

          @if($patient)
            @php
              $fullName = trim(($patient->first_name ?? '').' '.($patient->last_name ?? ''));
              $birth = $patient->birth_date ? Carbon::parse($patient->birth_date) : null;
              $age = $birth ? $birth->age : null;
            @endphp

            <div class="grid sm:grid-cols-2 gap-3 text-sm">
              <div>
                <div class="text-xs text-slate-500">Nombre</div>
                <div class="font-medium text-slate-800">{{ $fullName ?: '—' }}</div>
                @if(!is_null($age))
                  <div class="text-xs text-slate-500 mt-1">{{ $age }} años</div>
                @endif
              </div>

              <div>
                <div class="text-xs text-slate-500">Documento</div>
                <div class="font-medium text-slate-800">{{ $patient->document ?? '—' }}</div>
              </div>

              <div>
                <div class="text-xs text-slate-500">Teléfono</div>
                <div class="font-medium text-slate-800">{{ $patient->phone ?? '—' }}</div>
              </div>

              <div>
                <div class="text-xs text-slate-500">Fecha de nacimiento</div>
                <div class="font-medium text-slate-800">{{ $birth ? $birth->format('d/m/Y') : '—' }}</div>
              </div>

              <div class="sm:col-span-2">
                <div class="text-xs text-slate-500">Dirección</div>
                <div class="font-medium text-slate-800">{{ $patient->address ?? '—' }}</div>
              </div>
            </div>

            <div class="mt-3 p-3 rounded-lg border border-slate-200 bg-slate-50 text-sm text-slate-600">
              <i class="fas fa-info-circle text-slate-400"></i>
              Si quieres que el paciente pueda editar estos datos desde aquí, dime exactamente qué columnas tienes en
              <code class="px-1 rounded bg-white border">patients</code> y armamos el formulario.
            </div>
          @else
            <div class="text-sm text-slate-500">
              No se encontró el registro de paciente vinculado a tu usuario.
            </div>
          @endif
        </section>
      </div>

    @elseif($tab==='password')
      {{-- ================== CONTRASEÑA ================== --}}
      <section class="card border border-slate-200 max-w-xl mx-auto">
        <h3 class="font-semibold text-slate-800 flex items-center gap-2 mb-3">
          <i class="fas fa-lock text-slate-500"></i>
          Cambiar contraseña
        </h3>

        <form method="post" action="{{ route('app.profile.password') }}" class="grid gap-3">
          @csrf

          <div>
            <label class="{{ $labelBase }}">Contraseña actual</label>
            <input type="password" name="current_password" class="{{ $inputBase }}" required autocomplete="current-password">
          </div>

          <div class="grid sm:grid-cols-2 gap-3">
            <div>
              <label class="{{ $labelBase }}">Nueva contraseña</label>
              <input type="password" name="password" class="{{ $inputBase }}" required minlength="8" autocomplete="new-password">
              <div class="text-[11px] text-slate-500 mt-1">Mínimo 8 caracteres.</div>
            </div>
            <div>
              <label class="{{ $labelBase }}">Confirmar contraseña</label>
              <input type="password" name="password_confirmation" class="{{ $inputBase }}" required minlength="8" autocomplete="new-password">
            </div>
          </div>

          <div class="pt-1">
            <button class="btn btn-primary">
              <i class="fas fa-check"></i>
              Actualizar
            </button>
          </div>
        </form>

        <div class="mt-3 text-xs text-slate-500">
          Tip: usa una contraseña distinta a la de otras cuentas.
        </div>
      </section>

    @else
      {{-- ================== HISTORIA CLÍNICA ================== --}}
      <div class="grid lg:grid-cols-2 gap-4">

        {{-- NOTAS --}}
        <section class="card border border-slate-200">
          <div class="flex items-center justify-between gap-2 mb-3">
            <h3 class="font-semibold text-slate-800 flex items-center gap-2">
              <i class="fas fa-sticky-note text-slate-500"></i>
              Notas clínicas
            </h3>
            <span class="text-xs text-slate-500">Últimas 50</span>
          </div>

          <div class="space-y-2">
            @forelse($notes as $n)
              @php
                $st = $n->appointment->start_time ?? '00:00:00';
                $hh = strlen($st) === 5 ? $st.':00' : $st;
                $when = Carbon::parse($n->appointment->date ?? now())->setTimeFromTimeString($hh);
                $svc = $serviceNames[$n->appointment->service_id] ?? 'Servicio';
                $den = $dentistNames[$n->appointment->dentist_id] ?? 'Odontólogo';
              @endphp

              <div class="border border-slate-200 rounded-lg p-3 hover:bg-slate-50 transition">
                <div class="text-xs text-slate-500 mb-2 flex flex-wrap gap-x-3 gap-y-1">
                  <span><i class="fas fa-clock text-slate-400"></i> {{ $when->format('d/m/Y H:i') }}</span>
                  <span><i class="fas fa-tooth text-slate-400"></i> {{ $svc }}</span>
                  <span><i class="fas fa-user-md text-slate-400"></i> {{ $den }}</span>
                  @if($n->author)
                    <span><i class="fas fa-pen text-slate-400"></i> {{ $n->author->name }}</span>
                  @endif
                </div>

                <div class="text-sm text-slate-700 space-y-1">
                  @if($n->subjective)<div><span class="text-xs text-slate-500">S:</span> {{ $n->subjective }}</div>@endif
                  @if($n->objective) <div><span class="text-xs text-slate-500">O:</span> {{ $n->objective }}</div>@endif
                  @if($n->assessment)<div><span class="text-xs text-slate-500">A:</span> {{ $n->assessment }}</div>@endif
                  @if($n->plan)      <div><span class="text-xs text-slate-500">P:</span> {{ $n->plan }}</div>@endif
                </div>
              </div>
            @empty
              <div class="text-sm text-slate-500">Sin notas clínicas.</div>
            @endforelse
          </div>
        </section>

        {{-- DIAGNÓSTICOS --}}
        <section class="card border border-slate-200">
          <div class="flex items-center justify-between gap-2 mb-3">
            <h3 class="font-semibold text-slate-800 flex items-center gap-2">
              <i class="fas fa-diagnoses text-slate-500"></i>
              Diagnósticos
            </h3>
            <span class="text-xs text-slate-500">Últimos 100</span>
          </div>

          @php
            $dxStatusLabel = fn($s) => ($s === 'active') ? 'Activo' : 'Resuelto';
            $dxStatusCls = fn($s) => ($s === 'active')
              ? 'bg-amber-50 text-amber-700 border-amber-200'
              : 'bg-emerald-50 text-emerald-700 border-emerald-200';
          @endphp

          <div class="overflow-x-auto border border-slate-200 rounded-lg">
            <table class="min-w-full text-sm">
              <thead class="border-b bg-slate-50">
                <tr class="text-left text-slate-600">
                  <th class="px-3 py-2">Fecha</th>
                  <th class="px-3 py-2">Dx</th>
                  <th class="px-3 py-2">Pieza</th>
                  <th class="px-3 py-2">Estado</th>
                </tr>
              </thead>
              <tbody>
                @forelse($diagnoses as $d)
                  @php
                    $st2 = $d->appointment->start_time ?? '00:00:00';
                    $hh2 = strlen($st2) === 5 ? $st2.':00' : $st2;
                    $w = Carbon::parse($d->appointment->date ?? now())->setTimeFromTimeString($hh2);
                    $tooth = $d->tooth_code ?: '—';
                    $surface = $d->surface ? ' · '.$d->surface : '';
                  @endphp
                  <tr class="border-b hover:bg-slate-50 transition">
                    <td class="px-3 py-2 whitespace-nowrap text-slate-600">{{ $w->format('d/m/Y H:i') }}</td>
                    <td class="px-3 py-2">
                      <div class="font-medium text-slate-800">{{ $d->label }}</div>
                      @if($d->code)
                        <div class="text-xs text-slate-500">{{ $d->code }}</div>
                      @endif
                    </td>
                    <td class="px-3 py-2 text-slate-700">{{ $tooth }}{!! $surface !!}</td>
                    <td class="px-3 py-2">
                      <span class="inline-flex items-center text-xs px-2 py-1 rounded border {{ $dxStatusCls($d->status) }}">
                        {{ $dxStatusLabel($d->status) }}
                      </span>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="4" class="px-3 py-6 text-center text-slate-500">Sin diagnósticos.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </section>

      </div>
    @endif
  </div>
@endsection
