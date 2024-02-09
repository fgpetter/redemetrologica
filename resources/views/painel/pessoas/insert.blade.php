@extends('layouts.master')
@section('title') Editar Usuário @endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Pessoas @endslot
@slot('title') @if ($pessoa->id) Editar Pessoa @else Cadastrar Pessoa @endif @endslot
@endcomponent
<div class="row">

  <div class="col-xl-7 col-xxl-6">
    <x-painel.pessoas.insert :pessoa="$pessoa"/>
    @if($pessoa->id)
    <x-painel.enderecos.list :pessoa="$pessoa"/>
    @endif
  </div>

  <div class="col-xl-5 col-xxl-6">
    @if($pessoa->id && $pessoa->tipo_pessoa == 'PJ')
    <x-painel.unidades.list :pessoa="$pessoa"/>
    @endif

    @if($pessoa->avaliador)
    <div class="card">
      <div class="card-header">
        <h4 class="card-title mb-0">Dados de Avaliador</h4>
      </div><!-- end card header -->
      <div class="card-body">
        <p> Lorem ipsum dolor sit amet consectetur adipisicing elit. 
          Quisquam libero nam officiis molestias doloremque iusto facere, 
          culpa aliquid sint illo hic nemo qui animi quibusdam fugit, totam vel eius optio. </p>
      </div>
    </div>
    @endif
  </div>

</div>

@endsection

@section('script')
<script src="{{ URL::asset('build/js/pages/imask.js') }}"></script>
<script src="{{ URL::asset('build/js/jquery-3.7.1.min.js') }}"></script>
<script src="{{ URL::asset('build/js/jquery.mask.min.js') }}"></script>
<script src="{{ URL::asset('build/js/custom.js') }}"></script>
@endsection