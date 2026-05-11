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
      <x-painel.lancamento-financeiro.list-lancamentos
        :lancamentosfinanceiros="$lancamentosfinanceiros"
        :pessoas="$pessoas"
        :mesesanos="$mesesanos"
        :centrosdecusto="$centrosdecusto"
        :planosconta="$planosconta"
        :modalidadepagamento="$modalidadepagamento"
        :pessoasModal="$pessoasModal"
      />
    </div>
  </div>
@endsection
