@extends('layouts.master')
@section('title') Listagem de usuários @endsection
@section('content')
<x-breadcrumb 
  li1="Usuários" li1link="user-index"
  title="Listagem de usuários"/>

<div class="row">
  {{-- <div class="col-12">
    <x-painel.users.insert-list />
  </div> --}}
  <div class="col-12">
    <x-painel.users.list :users="$users"/>
  </div>
</div>

@endsection