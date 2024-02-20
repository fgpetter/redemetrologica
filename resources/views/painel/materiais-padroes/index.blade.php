@extends('layouts.master')
@section('title')
  Listagem de materiais padrões
@endsection
@section('content')
  @component('components.breadcrumb')
    @slot('li_1')
      materiais padrões
    @endslot
    @slot('title')
      Listagem de materiais padrões
    @endslot
  @endcomponent

  <div class="row">
    <div class="col-xxl-10">
      <x-painel.materiais-padroes.list :materiais="$materiais" />
    </div>
  </div>
@endsection

@section('script')
  <script src="{{ URL::asset('build/js/pages/imask.js') }}"></script>
  <script src="{{ URL::asset('build/js/jquery-3.7.1.min.js') }}"></script>
  <script src="{{ URL::asset('build/js/jquery.mask.min.js') }}"></script>
  <script src="{{ URL::asset('build/js/custom.js') }}"></script>
@endsection
