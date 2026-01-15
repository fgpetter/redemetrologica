@extends('layouts.master')
@section('title') @if ($lancamento->id) Editar Lançamento @else Cadastrar Lançamento @endif @endsection
@section('content')

<x-breadcrumb 
  li1="Lançamentos Financeiros" li1link="lancamento-financeiro-index"
  :title="$lancamento->id ? 'Editar Lançamento' : 'Cadastrar Lançamento'" />

  <div class="row">
    <div class="col">
      <x-painel.lancamento-financeiro.insert 
        :lancamento="$lancamento" 
        :enderecocobranca="$enderecocobranca" 
        :pessoas="$pessoas" 
        :centrosdecusto="$centrosdecusto"
        :planosconta="$planosconta"
        :modalidadepagamento="$modalidadepagamento"
      />
    </div>
  </div>
@endsection

@section('script')
  <script defer>
    const pessoa = document.getElementById('pessoa')
    const plano_conta = document.getElementById('plano_conta')
    const sendButton = document.getElementById('send-button')
    const invalidPessoa = document.getElementById('invalid-pessoa')
    const invalidPlanoConta = document.getElementById('invalid-plano-conta')
    let selectedPessoa = (pessoa.value) ? true : false
    let selectedPlanoConta = (plano_conta.value) ? true : false

    if(pessoa){
      const choices = new Choices(pessoa,{
        searchFields: ['label'],
        maxItemCount: -1,
        allowHTML: true
      });
      pessoa.addEventListener('change', function(){
        selectedPessoa = pessoa.value
        invalidPessoa.classList.add("d-none")
      })
    }

    if(plano_conta){
      const choices = new Choices(plano_conta,{
        searchFields: ['label'],
        maxItemCount: -1,
        allowHTML: true
      });
      plano_conta.addEventListener('change', function(){
        selectedPlanoConta = plano_conta.value
        invalidPlanoConta.classList.add("d-none")
      })
    }

    sendButton.addEventListener('click', function validFields() {
      if(selectedPlanoConta && selectedPessoa){
        document.getElementById('submit-button').click()
      }
      if(!selectedPlanoConta) { invalidPlanoConta.classList.remove("d-none") }
      if(!selectedPessoa) { invalidPessoa.classList.remove("d-none") }
    })


    $("#data_pagamento").change(function() {
      if(Date.parse($(this).val())) {
        $("input[name=status]").val('EFETIVADO');
      } else {
        $("input[name=status]").val('PROVISIONADO');
      }
    });
  </script>
@endsection