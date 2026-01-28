@extends('layouts.app')
@section('title','Mi Perfil')

@section('header-actions')
  <a href="{{ route('admin.dashboard') }}" class="btn btn-ghost">
    <i class="fas fa-arrow-left"></i>
    Volver
  </a>
@endsection

@section('content')
  @php
    $tabs = [
      'datos'    => ['label' => 'Datos Personales', 'icon' => 'fa-user'],
      'password' => ['label' => 'Seguridad',        'icon' => 'fa-lock'],
    ];

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

  <div class="max-w-4xl mx-auto space-y-6">

    {{-- Tarjeta superior --}}
    <div class="card border border-slate-200 p-5">
      <div class="flex items-start justify-between gap-4 flex-wrap">
        <div class="flex items-center gap-4">
             <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 text-2xl">
                 <i class="fas fa-user"></i>
             </div>
             <div>
                <div class="text-xs uppercase tracking-wide text-slate-500 font-semibold">Mi Cuenta</div>
                <h2 class="text-xl font-bold text-slate-800">{{ $user->name }}</h2>
                <div class="text-sm text-slate-500 mt-1 flex items-center gap-2">
                    <i class="fas fa-envelope"></i>
                    {{ $user->email }}
                    @if($user->roles->isNotEmpty())
                        <span class="px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 text-xs font-semibold">
                            {{ ucfirst($user->roles->first()->name) }}
                        </span>
                    @endif
                </div>
             </div>
        </div>

        {{-- Tabs --}}
        <div class="flex flex-wrap gap-2">
          @foreach($tabs as $key => $t)
            <a href="{{ route('admin.profile',['tab'=>$key]) }}"
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
      <section class="card border border-slate-200 max-w-2xl mx-auto">
          <div class="flex items-center gap-2 mb-4 pb-3 border-b border-slate-100">
            <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                <i class="fas fa-user-edit"></i>
            </div>
            <h3 class="font-semibold text-slate-800">Editar Información</h3>
          </div>

          <form method="post" action="{{ route('admin.profile.update') }}" class="grid gap-4">
            @csrf

            <div class="grid sm:grid-cols-2 gap-4">
                <div class="col-span-2 sm:col-span-1">
                  <label class="{{ $labelBase }}">Nombre Completo</label>
                  <input name="name" class="{{ $inputBase }}" value="{{ old('name',$user->name) }}" required>
                </div>
                
                <div class="col-span-2 sm:col-span-1">
                  <label class="{{ $labelBase }}">Correo Electrónico</label>
                  <input class="{{ $inputBase }} bg-slate-50 text-slate-500 cursor-not-allowed" value="{{ $user->email }}" disabled title="El correo no se puede cambiar">
                </div>
            </div>

            <div class="pt-2 flex justify-end">
              <button class="btn btn-primary">
                <i class="fas fa-save"></i>
                Guardar Cambios
              </button>
            </div>
          </form>

          <p class="{{ $helpBase }}">
            <i class="fas fa-info-circle mr-1"></i> Para cambiar tu dirección de correo electrónico, contacta a un administrador.
          </p>
      </section>

    @elseif($tab==='password')
      {{-- ================== CONTRASEÑA ================== --}}
      <section class="card border border-slate-200 max-w-xl mx-auto">
        <div class="flex items-center gap-2 mb-4 pb-3 border-b border-slate-100">
            <div class="w-8 h-8 rounded-lg bg-red-50 text-red-600 flex items-center justify-center">
                <i class="fas fa-lock"></i>
            </div>
            <h3 class="font-semibold text-slate-800">Cambiar Contraseña</h3>
        </div>

        <form method="post" action="{{ route('admin.profile.password') }}" class="grid gap-4">
          @csrf

          <div>
            <label class="{{ $labelBase }}">Contraseña Actual</label>
            <div class="relative">
                <input type="password" name="current_password" class="{{ $inputBase }} pr-10" required autocomplete="current-password">
                <i class="fas fa-key absolute right-3 top-2.5 text-slate-400"></i>
            </div>
          </div>

          <div class="grid sm:grid-cols-2 gap-4 border-t border-slate-100 pt-4 mt-2">
            <div>
              <label class="{{ $labelBase }}">Nueva Contraseña</label>
              <input type="password" name="password" class="{{ $inputBase }}" required minlength="8" autocomplete="new-password">
            </div>
            <div>
              <label class="{{ $labelBase }}">Confirmar Contraseña</label>
              <input type="password" name="password_confirmation" class="{{ $inputBase }}" required minlength="8" autocomplete="new-password">
            </div>
          </div>
          
          <div class="text-[11px] text-slate-500">
            <i class="fas fa-shield-alt mr-1"></i> La contraseña debe tener al menos 8 caracteres.
          </div>

          <div class="pt-2 flex justify-end">
            <button class="btn btn-primary">
              <i class="fas fa-check-circle"></i>
              Actualizar Contraseña
            </button>
          </div>
        </form>
      </section>

    @endif
  </div>
@endsection
