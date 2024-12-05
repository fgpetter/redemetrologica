@extends('layouts.master')
@section('title') Editar Usuário @endsection
@section('content')
<x-breadcrumb 
  li1="Usuários" li1link="user-index"
  title="Editar Usuário"/>

<div class="row">
  <div class="col-12 col-sm-5">
    <x-painel.users.insert :user="$user"/>
  </div>
  @can('admin')
    <div class="col-12 col-sm-5">
      <x-painel.users.permissions :user="$user" :permissions="$permissions"/>
    </div>
  @endcan
</div>

@endsection