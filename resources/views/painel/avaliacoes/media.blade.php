@extends('layouts.master')
@section('title') Pesquisa de Satisfação @endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Avaliações @endslot
@slot('title') Pesquisa de Satisfação @endslot
@endcomponent

<div class="row">
  <div class="col">
    <livewire:avaliacoes.media-avaliacoes-table />
  </div>
</div>

@endsection
