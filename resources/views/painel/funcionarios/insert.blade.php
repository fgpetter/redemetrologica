@extends('layouts.master')
@section('title') Editar Funcionário @endsection
@section('content')
  @component('components.breadcrumb')
  @slot('li_1') Pessoas @endslot
  @slot('title') @if ($funcionario->id) Editar Funcionario @else Cadastrar Funcionario @endif @endslot
  @endcomponent
  <div class="row">

    <div class="col col-xxl-8">
      <x-painel.funcionarios.insert :funcionario="$funcionario"/>
    </div>
    @if ($funcionario->id)
      <div class="col-4">
        <x-painel.funcionarios.dados-bancarios :funcionario="$funcionario"/>
      </div>
    @endif

  </div>

@endsection

@section('script')
<script src="{{ URL::asset('build/js/pages/imask.js') }}"></script>
<script src="{{ URL::asset('build/js/custom.js') }}"></script>
@endsection