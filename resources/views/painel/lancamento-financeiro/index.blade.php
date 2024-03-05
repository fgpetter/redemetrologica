@extends('layouts.master')
@section('title')
  Lançamentos Financeiros
@endsection
@section('content')
  @component('components.breadcrumb')
    @slot('li_1')
      Financeiro
    @endslot
    @slot('title')
      Lançamentos Financeiros
    @endslot
  @endcomponent

  <div class="row">
    <div class="col">
      <x-painel.lancamento-financeiro.list :lancamentosfinanceiros="$lancamentosfinanceiros"/>
    </div>
  </div>
@endsection