@extends('layouts.master')
@section('title') @if ($pessoa->id) Editar Pessoa @else Cadastrar Pessoa @endif @endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Pessoas @endslot
@slot('title') @if ($pessoa->id) Editar Pessoa @else Cadastrar Pessoa @endif @endslot
@endcomponent
<div class="row">

  <div class="col-xl-7 col-xxl-6">
    <x-painel.pessoas.insert :pessoa="$pessoa"/>

    {{-- Endereços --}}
    @if($pessoa->id)
      {{-- <x-painel.enderecos.list :pessoa="$pessoa"/> --}}
      <livewire:enderecos.listview :pessoa="$pessoa" />
    @endif
  </div>
  
  <div class="col-12 col-xxl-5">

    {{-- Empresa associada --}}
    @if($pessoa->id && $pessoa->tipo_pessoa == 'PF')
      <x-painel.pessoas.usuario :pessoa="$pessoa"/>
      <x-painel.pessoas.empresas :pessoa="$pessoa" :empresas="$empresas"/>
    @endif

    {{-- Dados bancários --}}
    @if($pessoa->dadosBancarios()->exists())
      {{-- <x-painel.dados-bancarios.list :pessoa="$pessoa"/> --}}
      <livewire:dados-bancarios.listview :pessoa="$pessoa" />
    @endif

    {{-- Unidades --}}
    @if($pessoa->id && $pessoa->tipo_pessoa == 'PJ')
      <x-painel.unidades.list :pessoa="$pessoa"/>
    @endif

    {{-- Avaliador --}}
    @if($pessoa->avaliador)
      <x-painel.pessoas.avaliador-info :pessoa="$pessoa"/>
    @endif

    {{-- Instrutor --}}
    @if($pessoa->instrutor)
      <x-painel.pessoas.instrutor-info :pessoa="$pessoa"/>
    @endif
  </div>

</div>

@endsection