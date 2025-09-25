@extends('layouts.app')
@section('title','Nuevo paciente')

@section('header-actions')
  <a href="{{ route('admin.patients') }}" class="btn btn-ghost">Volver</a>
@endsection

@section('content')
  <form method="post" action="{{ route('admin.patients.store') }}" class="card">
    @csrf
    @include('admin.patients._form', ['patient'=>$patient])
  </form>
@endsection
