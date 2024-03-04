@extends('layouts.master')
@section('title')
  Listagem de plano de contas
@endsection
@section('content')
  @component('components.breadcrumb')
    @slot('li_1')
      plano de contas
    @endslot
    @slot('title')
      Listagem de plano de contas
    @endslot
  @endcomponent

  <div class="row">
    <div class="col-xxl-10">
      <x-painel.plano-conta.list :planocontas="$planocontas" :centrocustos="$centrocustos"/>
    </div>
  </div>
@endsection