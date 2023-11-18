@extends('layouts.master')
@section('title') Editar Usuário @endsection
@section('content')
  @component('components.breadcrumb')
  @slot('li_1') Pessoas @endslot
  @slot('title') @if ($funcionario->id) Editar Pessoa @else Cadastrar Pessoa @endif @endslot
  @endcomponent
  <div class="row">

    <div class="col-12 col-xxl-8">
      <x-painel.funcionarios.insert :funcionario="$funcionario"/>
    </div>

  </div>

@endsection

@section('script')
<script src="{{ URL::asset('build/js/pages/imask.js') }}"></script>
<script src="{{ URL::asset('build/js/custom.js') }}"></script>
@endsection