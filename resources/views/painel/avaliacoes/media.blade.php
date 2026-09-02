@extends('layouts.master')
@section('title') Média de Avaliações @endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Avaliações @endslot
@slot('title') Média de Avaliações @endslot
@endcomponent

<div class="row">
  <div class="col">
    <livewire:avaliacoes.media-avaliacoes-table />
  </div>
</div>

@endsection
