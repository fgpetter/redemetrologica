@extends('layouts.master')
@section('title') @if ($lancamento->id) Editar Lançamento @else Cadastrar Lançamento @endif @endsection
@section('content')

<x-breadcrumb 
  li1="Lançamentos Financeiros" li1link="lancamento-financeiro-index"
  :title="$lancamento->id ? 'Editar Lançamento' : 'Cadastrar Lançamento'" />

  <div class="row">
    <div class="col">
      <x-painel.lancamento-financeiro.edit 
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
    const pessoa = document.getElementById('tom-select-lancamento-pessoa')
    const plano_conta = document.getElementById('tom-select-lancamento-plano-conta')
    const sendButton = document.getElementById('send-button')
    const invalidPessoa = document.getElementById('invalid-pessoa')
    const invalidPlanoConta = document.getElementById('invalid-plano-conta')
    let selectedPessoa = (pessoa && pessoa.value) ? true : false
    let selectedPlanoConta = (plano_conta && plano_conta.value) ? true : false

    if (pessoa) {
      pessoa.addEventListener('change', function () {
        selectedPessoa = Boolean(pessoa.value)
        if (invalidPessoa) {
          invalidPessoa.classList.add('d-none')
        }
      })
    }

    if (plano_conta) {
      plano_conta.addEventListener('change', function () {
        selectedPlanoConta = Boolean(plano_conta.value)
        if (invalidPlanoConta) {
          invalidPlanoConta.classList.add('d-none')
        }
      })
    }

    if (sendButton) {
      sendButton.addEventListener('click', function validFields() {
        if (selectedPlanoConta && selectedPessoa) {
          document.getElementById('submit-button').click()
        }
        if (!selectedPlanoConta && invalidPlanoConta) {
          invalidPlanoConta.classList.remove('d-none')
        }
        if (!selectedPessoa && invalidPessoa) {
          invalidPessoa.classList.remove('d-none')
        }
      })

      $("#data_pagamento").change(function() {
        if(Date.parse($(this).val())) {
          $("input[name=status]").val('EFETIVADO');
        } else {
          $("input[name=status]").val('PROVISIONADO');
        }
      });
    }
  </script>
@endsection