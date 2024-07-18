@extends('layouts.master')

@section('title')
  Listagem de interlaboratoriais
@endsection

@section('content')

  @component('components.breadcrumb')
    @slot('li_1') @endslot
    @slot('title')
      Listagem de interlaboratoriais
    @endslot
  @endcomponent

  <div class="row">
    <div class="col">
      <x-painel.interlabs.list :interlabs="$interlabs"/>
    </div>
  </div>
@endsection