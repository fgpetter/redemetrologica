@extends('layouts.master')
@section('title') Editar Laboratório @endsection
@section('content')
  @component('components.breadcrumb')
  @slot('li_1') Pessoas @endslot
  @slot('title') Editar Laboratório @endslot
  @endcomponent
  <div class="row">

    <div class="col-12">
      <x-painel.laboratorios.insert :laboratorio="$laboratorio" :areasatuacao="$areasatuacao" />
    </div>

  </div>

@endsection