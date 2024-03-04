@extends('layouts.master')
@section('title')
  Listagem de bancos
@endsection
@section('content')
  @component('components.breadcrumb')
    @slot('li_1')
      bancos
    @endslot
    @slot('title')
      Listagem de bancos
    @endslot
  @endcomponent

  <div class="row">
    <div class="col-xxl-10">
      <x-painel.bancos.list :bancos="$bancos" />
    </div>
  </div>
@endsection