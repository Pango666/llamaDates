@extends('layouts.app')
@section('title','Nuevo odontólogo')

@section('header-actions')
  <a href="{{ route('admin.dentists') }}" class="btn btn-ghost">Volver</a>
@endsection

@section('content')
  <form method="post" action="{{ route('admin.dentists.store') }}" class="card">
    @csrf
    @include('admin.dentists._form', ['dentist'=>$dentist])
  </form>
@endsection
