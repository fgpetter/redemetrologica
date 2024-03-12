@extends('layouts.master')
@section('title') @if ($lancamento->id) Editar Lançamento @else Cadastrar Lançamento @endif @endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Financeiro @endslot
@slot('title') @if ($lancamento->id) Editar Lançamento @else Cadastrar Lançamento @endif @endslot
@endcomponent
<div class="row">
  <div class="col">
    <x-painel.lancamento-financeiro.insert :lancamento="$lancamento" :pessoas="$pessoas" :centrosdecusto="$centrosdecusto"/>
  </div>
</div>

@endsection