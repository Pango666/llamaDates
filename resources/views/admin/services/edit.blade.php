@extends('layouts.app')
@section('title','Editar servicio')

@section('header-actions')
  <a href="{{ route('admin.services') }}" class="btn btn-ghost">Volver</a>
@endsection

@section('content')
  <form method="post" action="{{ route('admin.services.update',$service) }}" class="card">
    @csrf @method('PUT')
    @include('admin.services._form', ['service'=>$service])
  </form>
@endsection
