@extends('layouts.app')
@section('title','Nuevo servicio')

@section('header-actions')
  <a href="{{ route('admin.services') }}" class="btn btn-ghost">Volver</a>
@endsection

@section('content')
  <form method="post" action="{{ route('admin.services.store') }}" class="card">
    @csrf
    @include('admin.services._form', ['service'=>$service])
  </form>
@endsection
