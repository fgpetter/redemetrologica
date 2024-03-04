@extends('layouts.master')
@section('title')
  Listagem de materiais padrões
@endsection
@section('content')
  @component('components.breadcrumb')
    @slot('li_1')
      materiais padrões
    @endslot
    @slot('title')
      Listagem de materiais padrões
    @endslot
  @endcomponent

  <div class="row">
    <div class="col-xxl-10">
      <x-painel.materiais-padroes.list :materiais="$materiais" />
    </div>
  </div>
@endsection