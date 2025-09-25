@extends('layouts.auth')

@section('title','Restablecer contraseña')
@section('subtitle','Ingresa una nueva contraseña')

@section('content')
<form method="POST" action="{{ route('password.update') }}" class="space-y-4">
  @csrf
  <input type="hidden" name="token" value="{{ $token }}">
  <input type="hidden" name="email" value="{{ $email ?? old('email') }}">

  <div>
    <label class="block text-sm font-medium">Nueva contraseña</label>
    <input type="password" name="password" required class="mt-1 w-full border rounded px-3 py-2">
  </div>
  <div>
    <label class="block text-sm font-medium">Confirmar contraseña</label>
    <input type="password" name="password_confirmation" required class="mt-1 w-full border rounded px-3 py-2">
  </div>

  <button class="w-full py-2 rounded bg-blue-600 text-white hover:bg-blue-700">Restablecer</button>
</form>
@endsection
