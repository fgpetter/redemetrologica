@extends('layouts.master')
@section('title') Editar Avaliador @endsection
@section('content')
  @component('components.breadcrumb')
  @slot('li_1') Pessoas @endslot
  @slot('title') @if ($avaliador->id) Editar Avaliador @else Cadastrar Avaliador @endif @endslot
  @endcomponent
  <div class="row">

    <div class="col col-8">
      <x-painel.avaliadores.insert 
        :avaliador="$avaliador" 
        :avaliacoes="$avaliacoes" 
        :qualificacoes="$qualificacoes"
        :qualificacoeslist="$qualificacoes_list"
      />
    </div>
    @if ($avaliador->id)
      <div class="col-4">
        <x-painel.avaliadores.dados-bancarios :avaliador="$avaliador" />      
      </div>
    @endif

  </div>

@endsection