@extends('layouts.master')
@section('title') Editar Avaliação @endsection
@section('content')
  @component('components.breadcrumb')
  @slot('li_1') Avaliacoes @endslot
  @slot('title') Editar Avaliação @endslot
  @endcomponent
  <div class="row">

    <div class="col col-12">
      <x-painel.avaliacoes.insert 
        :avaliacao="$avaliacao" 
        :laboratorio="$laboratorio" 
        :avaliadores="$avaliadores" 
        :tipoavaliacao="$tipo_avaliacao" 
        :totalavaliadores="$totalavaliadores" 
      />
    </div>

  </div>
@endsection