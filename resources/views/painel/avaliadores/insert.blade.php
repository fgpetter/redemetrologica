@extends('layouts.master')
@section('title') Editar Avaliador @endsection
@section('content')

  <x-breadcrumb
    li_1="Avaliadores" li_1link="avaliador-index"
    title="Editar Avaliador" />
  
  {{-- @component('components.breadcrumb')
  @slot('li_1') Pessoas @endslot
  @slot('title') @if ($avaliador->id) Editar Avaliador @else Cadastrar Avaliador @endif @endslot
  @endcomponent --}}
  
  <div class="row">

    <div class="col col-8">
      <x-painel.avaliadores.insert 
        :avaliador="$avaliador" 
        :avaliacoes="$avaliacoes" 
        :qualificacoes="$qualificacoes"
        :qualificacoeslist="$qualificacoes_list"
        :areasatuacao="$areas_atuacao"
        :enderecopessoal="$endereco_pessoal"
        :enderecocomercial="$endereco_comercial"
      />
    </div>
    @if ($avaliador->id)
      <div class="col-8">
        <x-painel.dados-bancarios.list :pessoa="$avaliador->pessoa" />
      </div>
    @endif
  </div>

@endsection