@extends('layouts.app')
@section('title','Permisos · '.$role->name)

@section('content')
<form method="post" action="{{ route('admin.roles.perms.update',$role) }}" class="card space-y-3">
  @csrf
  <div class="grid md:grid-cols-3 gap-2">
    @php $current = $role->permissions->pluck('id')->all(); @endphp
    @foreach($perms as $p)
      <label class="flex items-center gap-2 border rounded p-2">
        <input type="checkbox" name="perms[]" value="{{ $p->id }}" @checked(in_array($p->id, old('perms',$current)))>
        <div>
          <div class="font-medium">{{ $p->name }}</div>
          <div class="text-xs text-slate-500">{{ $p->label }}</div>
        </div>
      </label>
    @endforeach
  </div>
  <div>
    <button class="btn btn-primary">Guardar</button>
    <a href="{{ route('admin.roles.index') }}" class="btn btn-ghost">Cancelar</a>
  </div>
</form>
@endsection
