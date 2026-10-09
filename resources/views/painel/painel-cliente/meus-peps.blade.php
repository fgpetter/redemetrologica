@extends('layouts.master')

@section('title') Meus PEPs @endsection

@section('content')
  @component('components.breadcrumb')
    @slot('li_1') Inicio @endslot
    @slot('title') Meus PEPs @endslot
  @endcomponent

  <div class="row">
    <livewire:painel-cliente.meus-peps />
  </div>
@endsection
