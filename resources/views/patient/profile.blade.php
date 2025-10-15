@extends('layouts.app')
@section('title','Mi perfil')

@section('header-actions')
  <a href="{{ route('app.appointments.index') }}" class="btn btn-ghost">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7"/></svg>
    Mis citas
  </a>
@endsection

@section('content')
  @php
    $tabs = [
      'datos'    => 'Datos',
      'password' => 'Contraseña',
      'historia' => 'Historia clínica',
    ];
    $isActive = fn($t) => request('tab','datos')===$t ? 'nav-active' : '';
  @endphp

  <div class="card">
    <div class="flex flex-wrap gap-2 mb-3">
      @foreach($tabs as $key=>$label)
        <a href="{{ route('app.profile',['tab'=>$key]) }}" class="nav-item {{ $isActive($key) }}">
          @if($key==='datos')
            <svg class="w-4 h-4 inline" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M5.121 17.804A7 7 0 0112 15a7 7 0 016.879 2.804M15 11a3 3 0 10-6 0 3 3 0 006 0z"/></svg>
          @elseif($key==='password')
            <svg class="w-4 h-4 inline" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 15v2m-6 4h12a2 2 0 002-2v-7a2 2 0 00-2-2h-1V7a5 5 0 10-10 0v3H6a2 2 0 00-2 2v7a2 2 0 002 2z"/></svg>
          @else
            <svg class="w-4 h-4 inline" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M8 7h8M8 11h8M8 15h6M4 19h16a2 2 0 002-2V7a2 2 0 00-2-2H9l-2-2H4a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
          @endif
          <span class="ms-1">{{ $label }}</span>
        </a>
      @endforeach
    </div>

    @if($tab==='datos')
      {{-- ================== DATOS ================== --}}
      <div class="grid md:grid-cols-2 gap-4">
        <section>
          <h3 class="font-semibold mb-2">Mi usuario</h3>
          <form method="post" action="{{ route('app.profile.update') }}" class="grid gap-3">
            @csrf
            <div>
              <label class="block text-xs text-slate-500 mb-1">Nombre para mostrar</label>
              <input name="name" class="w-full border rounded px-3 py-2" value="{{ old('name',$user->name) }}" required>
            </div>
            <div>
              <label class="block text-xs text-slate-500 mb-1">Email</label>
              <input class="w-full border rounded px-3 py-2 bg-slate-50" value="{{ $user->email }}" disabled>
            </div>
            <div>
              <button class="btn btn-primary">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M5 13l4 4L19 7"/></svg>
                Guardar
              </button>
            </div>
          </form>
        </section>

        <section>
          <h3 class="font-semibold mb-2">Datos del paciente</h3>
          @if($patient)
            <div class="grid grid-cols-2 gap-2 text-sm">
              <div>
                <div class="text-xs text-slate-500">Nombre</div>
                <div class="font-medium">{{ $patient->first_name }} {{ $patient->last_name }}</div>
              </div>
              <div>
                <div class="text-xs text-slate-500">Documento</div>
                <div class="font-medium">{{ $patient->document ?? '—' }}</div>
              </div>
              <div>
                <div class="text-xs text-slate-500">Teléfono</div>
                <div class="font-medium">{{ $patient->phone ?? '—' }}</div>
              </div>
              <div>
                <div class="text-xs text-slate-500">Fecha de nacimiento</div>
                <div class="font-medium">{{ $patient->birth_date ?? '—' }}</div>
              </div>
              <div class="col-span-2">
                <div class="text-xs text-slate-500">Dirección</div>
                <div class="font-medium">{{ $patient->address ?? '—' }}</div>
              </div>
            </div>
            <p class="text-xs text-slate-500 mt-2">¿Quieres editar estos datos? Dime qué campos exactos tienes en <code>patients</code> y lo habilito aquí.</p>
          @else
            <div class="text-sm text-slate-500">No se encontró el registro de paciente vinculado a tu usuario.</div>
          @endif
        </section>
      </div>
    @elseif($tab==='password')
      {{-- ================== CONTRASEÑA ================== --}}
      <section class="max-w-md">
        <h3 class="font-semibold mb-2">Cambiar contraseña</h3>
        <form method="post" action="{{ route('app.profile.password') }}" class="grid gap-3">
          @csrf
          <div>
            <label class="block text-xs text-slate-500 mb-1">Contraseña actual</label>
            <input type="password" name="current_password" class="w-full border rounded px-3 py-2" required>
          </div>
          <div>
            <label class="block text-xs text-slate-500 mb-1">Nueva contraseña</label>
            <input type="password" name="password" class="w-full border rounded px-3 py-2" required minlength="8">
          </div>
          <div>
            <label class="block text-xs text-slate-500 mb-1">Confirmar contraseña</label>
            <input type="password" name="password_confirmation" class="w-full border rounded px-3 py-2" required minlength="8">
          </div>
          <div>
            <button class="btn btn-primary">
              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M5 13l4 4L19 7"/></svg>
              Actualizar
            </button>
          </div>
        </form>
      </section>
    @else
      {{-- ================== HISTORIA CLÍNICA ================== --}}
      <div class="grid md:grid-cols-2 gap-4">
        <section>
          <h3 class="font-semibold mb-2">Notas clínicas (últimas 50)</h3>
          <div class="space-y-2">
            @forelse($notes as $n)
              @php
                $h = strlen($n->appointment->start_time ?? '')===5 ? $n->appointment->start_time.':00' : ($n->appointment->start_time ?? '00:00:00');
                $when = \Carbon\Carbon::parse($n->appointment->date ?? now())->setTimeFromTimeString($h);
              @endphp
              <div class="border rounded p-2">
                <div class="text-xs text-slate-500">
                  {{ $when->format('Y-m-d H:i') }}
                  · {{ $serviceNames[$n->appointment->service_id] ?? 'Servicio' }}
                  · {{ $dentistNames[$n->appointment->dentist_id] ?? 'Odontólogo' }}
                  @if($n->author) · {{ $n->author->name }} @endif
                </div>
                @if($n->subjective)<div><span class="text-xs text-slate-500">S:</span> {{ $n->subjective }}</div>@endif
                @if($n->objective) <div><span class="text-xs text-slate-500">O:</span> {{ $n->objective }}</div>@endif
                @if($n->assessment)<div><span class="text-xs text-slate-500">A:</span> {{ $n->assessment }}</div>@endif
                @if($n->plan)      <div><span class="text-xs text-slate-500">P:</span> {{ $n->plan }}</div>@endif
              </div>
            @empty
              <div class="text-sm text-slate-500">Sin notas clínicas.</div>
            @endforelse
          </div>
        </section>

        <section>
          <h3 class="font-semibold mb-2">Diagnósticos (últimos 100)</h3>
          <div class="overflow-x-auto border rounded">
            <table class="min-w-full text-sm">
              <thead class="border-b bg-slate-50">
                <tr class="text-left">
                  <th class="px-3 py-2">Fecha</th>
                  <th class="px-3 py-2">Dx</th>
                  <th class="px-3 py-2">Pieza</th>
                  <th class="px-3 py-2">Estado</th>
                </tr>
              </thead>
              <tbody>
                @forelse($diagnoses as $d)
                  @php
                    $hh = strlen($d->appointment->start_time ?? '')===5 ? $d->appointment->start_time.':00' : ($d->appointment->start_time ?? '00:00:00');
                    $w  = \Carbon\Carbon::parse($d->appointment->date ?? now())->setTimeFromTimeString($hh);
                  @endphp
                  <tr class="border-b">
                    <td class="px-3 py-2 whitespace-nowrap">{{ $w->format('Y-m-d H:i') }}</td>
                    <td class="px-3 py-2">
                      {{ $d->label }}
                      @if($d->code) <span class="text-xs text-slate-500">({{ $d->code }})</span> @endif
                    </td>
                    <td class="px-3 py-2">{{ $d->tooth_code ?: '—' }} @if($d->surface) · {{ $d->surface }} @endif</td>
                    <td class="px-3 py-2">{{ $d->status==='active' ? 'Activo' : 'Resuelto' }}</td>
                  </tr>
                @empty
                  <tr><td colspan="4" class="px-3 py-4 text-center text-slate-500">Sin diagnósticos.</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </section>
      </div>
    @endif
  </div>
@endsection
