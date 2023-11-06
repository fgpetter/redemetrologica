@extends('layouts.master')
@section('title') Listagem de usuários @endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Usuários @endslot
@slot('title') Listagem de usuários @endslot
@endcomponent

<div class="row">
  <div class="col-xl-7">
    <x-painel.pessoas.list :pessoas="$pessoas"/>
  </div>
  <div class="col-xl-5">
    <x-painel.pessoas.insert />
  </div>
</div>

@endsection

@section('script')
<script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection