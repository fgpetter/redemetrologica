@extends('layouts.master')
@section('title') Editar Usuário @endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Pessoas @endslot
@slot('title') @if ($pessoa->id) Editar Pessoa @else Cadastrar Pessoa @endif @endslot
@endcomponent
<div class="row">
  <div class="col-xl-6">
    <x-painel.pessoas.insert :pessoa="$pessoa"/>
    @if($pessoa->id)
    <x-painel.enderecos.list :pessoa="$pessoa"/>
    @endif
  </div>
  <div class="col-xl-6">
    @if($pessoa->id)
    <x-painel.unidades.list :pessoa="$pessoa"/>
    @endif
  </div>
</div>

@endsection

@section('script')
<script src="{{ URL::asset('build/js/app.js') }}"></script>
<script src="{{ URL::asset('build/js/pages/imask.js') }}"></script>
<script src="{{ URL::asset('build/js/custom.js') }}"></script>
@endsection