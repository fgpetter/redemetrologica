@extends('layouts.master')

@section('title') Editar Interlaboratorial @endsection

@section('content')

  @component('components.breadcrumb')
    @slot('li_1') Interlabs @endslot
    @slot('title') @if ($interlab->id) Editar Interlaboratorial @else Cadastrar Interlaboratorial @endif @endslot
  @endcomponent
  
  <div class="row">

    <div class="col-xl-7 col-xxl-6">
      <x-painel.interlabs.insert :interlab="$interlab" :thumbs="$thumbs"/>
    </div>

    <div class="col-xl-5 col-xxl-6">
    </div>

  </div>

@endsection