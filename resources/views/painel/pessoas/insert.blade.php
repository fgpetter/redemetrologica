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
    @if($pessoa->id)
      <x-painel.enderecos.list :pessoa="$pessoa"/>
    @endif
  </div>

  <div class="col-xl-5 col-xxl-6">
    @if($pessoa->id && $pessoa->tipo_pessoa == 'PJ')
      <x-painel.unidades.list :pessoa="$pessoa"/>
    @endif

    @if($pessoa->avaliador)
      <x-painel.pessoas.avaliador-info :pessoa="$pessoa"/>
    @endif

    @if($pessoa->instrutor)
      <x-painel.pessoas.instrutor-info :pessoa="$pessoa"/>
    @endif
  </div>

</div>

@endsection