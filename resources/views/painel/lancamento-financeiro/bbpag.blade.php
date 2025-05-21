@extends('layouts.master')
@section('title')
  BBPAG - Lançamentos Financeiros
@endsection
@section('content')
  @component('components.breadcrumb')
    @slot('li_1')
      Financeiro
    @endslot
    @slot('title')
     BBPAG - Lançamentos Financeiros
    @endslot
  @endcomponent

  <div class="row">
    <div class="col">
      {{-- Componente Livewire GeraArquivo --}}
      <livewire:bbpag.gera-arquivo />
    </div>
  </div>
@endsection


