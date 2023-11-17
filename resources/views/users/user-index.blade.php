@extends('layouts.master')
@section('title') Listagem de usuários @endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Usuários @endslot
@slot('title') Listagem de usuários @endslot
@endcomponent

<div class="row">
  <div class="col-xl-7">
    <x-painel.users.list :users="$users"/>
  </div>
  <div class="col-xl-5">
    <x-painel.users.insert />
  </div>
</div>

@endsection