@extends('layouts.master')
@section('title') Editar fornecedor @endsection
@section('content')
  @component('components.breadcrumb')
  @slot('li_1') Pessoas @endslot
  @slot('title') @if ($fornecedor->id) Editar Fornecedor @else Cadastrar Fornecedor @endif @endslot
  @endcomponent
  <div class="row">

    <div class="col col-xxl-8">
      <x-painel.fornecedores.insert :fornecedor="$fornecedor"/>
      <x-painel.enderecos.list :pessoa="$fornecedor->pessoa" />
    </div>
    @if ($fornecedor->id)
      <div class="col-4">
        <x-painel.fornecedores.dados-bancarios :fornecedor="$fornecedor"/>
      </div>
    @endif

  </div>

@endsection