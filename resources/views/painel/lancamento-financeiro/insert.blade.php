@extends('layouts.master')
@section('title') @if ($lancamento->id) Editar Lançamento @else Cadastrar Lançamento @endif @endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Financeiro @endslot
@slot('title') @if ($lancamento->id) Editar Lançamento @else Cadastrar Lançamento @endif @endslot
@endcomponent
<div class="row">
  <div class="col">
    <x-painel.lancamento-financeiro.insert 
      :lancamento="$lancamento" 
      :pessoas="$pessoas" 
      :centrosdecusto="$centrosdecusto"
      :planosconta="$planosconta"
      :modalidadepagamento="$modalidadepagamento"
    />
  </div>
</div>

@endsection

@section('script')
  <script src="{{ URL::asset('build/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>
  <script defer>
    const pessoa = document.getElementById('pessoa')
    const plano_conta = document.getElementById('plano_conta')
    if(pessoa){
      const choices = new Choices(pessoa,{
        searchFields: ['label'],
        maxItemCount: -1
      });
    }
    if(plano_conta){
      const choices = new Choices(plano_conta,{
        searchFields: ['label'],
        maxItemCount: -1
      });
    }

    $("#data_pagamento").change(function() {
      if(Date.parse($(this).val())) {
        $("input[name=status]").val('EFETIVADO');
      } else {
        $("input[name=status]").val('PROVISIONADO');
      }
    });
  </script>
@endsection