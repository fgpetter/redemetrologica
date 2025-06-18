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
      <x-painel.lancamento-financeiro.list-lancamentos :lancamentosfinanceiros="$lancamentosfinanceiros" :pessoas="$pessoas" :mesesanos="$mesesanos"/>
    </div>
  </div>
@endsection

@section('script')
  <script defer>
    const pessoa = document.getElementById('pessoa')
    if(pessoa){
      const choices = new Choices(pessoa,{
        searchFields: ['label'],
        maxItemCount: -1
      });
    }
  </script>
@endsection