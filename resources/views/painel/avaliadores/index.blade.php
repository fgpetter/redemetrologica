@extends('layouts.master')
@section('title') Listagem de avaliadores @endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Pessoas @endslot
@slot('title') Listagem de avaliadores @endslot
@endcomponent

<div class="row">
  <div class="col">
    <x-painel.avaliadores.list :avaliadores="$avaliadores"/>
  </div>
</div>

@endsection

@section('script')
@endsection