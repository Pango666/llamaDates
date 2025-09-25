@extends('layouts.auth')

@section('title','Recuperar contraseña')
@section('subtitle','Te enviaremos un enlace de restablecimiento')

@section('content')
<form method="POST" action="{{ route('password.email') }}" class="space-y-4">
  @csrf
  <div>
    <label class="block text-sm font-medium">Email</label>
    <input type="email" name="email" value="{{ old('email') }}" required autofocus
           class="mt-1 w-full border rounded px-3 py-2">
  </div>
  <button class="w-full py-2 rounded bg-blue-600 text-white hover:bg-blue-700">Enviar enlace</button>
  <div class="text-center mt-2">
    <a href="{{ route('login') }}" class="text-sm text-slate-600 hover:underline">Volver al login</a>
  </div>
</form>
@endsection
