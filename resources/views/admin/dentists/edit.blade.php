@extends('layouts.app')
@section('title','Editar odontólogo')

@section('header-actions')
  <a href="{{ route('admin.dentists.show',$dentist) }}" class="btn btn-ghost">Volver</a>
@endsection

@section('content')
  <form method="post" action="{{ route('admin.dentists.update',$dentist) }}" class="card">
    @csrf @method('PUT')
    @include('admin.dentists._form', ['dentist'=>$dentist])
  </form>
@endsection
