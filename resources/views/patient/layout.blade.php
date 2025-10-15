@extends('layouts.app')

@section('header-actions')
  {{-- <a href="{{ route('app.dashboard') }}" class="btn btn-ghost">Inicio</a>
  <a href="{{ route('app.appointments.index') }}" class="btn btn-ghost">Mis citas</a>
  <a href="{{ route('app.invoices.index') }}" class="btn btn-ghost">Mis facturas</a> --}}
  {{-- <a href="{{ route('app.consents.index') }}" class="btn btn-ghost">Consentimientos</a> --}}
  <a href="{{ route('app.profile') }}" class="btn btn-ghost">Perfil</a>
@endsection

@section('content')
  <div class="max-w-5xl mx-auto">
    @yield('pt')
  </div>
@endsection
