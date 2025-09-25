@extends('layouts.app')
@section('title','Editar paciente')

@section('header-actions')
  <a href="{{ route('admin.patients.show',$patient) }}" class="btn btn-ghost">Volver</a>
@endsection

@section('content')
  <form method="post" action="{{ route('admin.patients.update',$patient) }}" class="card">
    @csrf @method('PUT')
    @include('admin.patients._form', ['patient'=>$patient])
  </form>
@endsection
