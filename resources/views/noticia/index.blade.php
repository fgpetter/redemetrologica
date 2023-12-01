@extends('layouts.master')
@section('title') Listagem de pessoas @endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Pessoas @endslot
@slot('title') Listagem de pessoas @endslot
@endcomponent

<div class="row">
  <div class="col">
    <x-painel.pessoas.list :pessoas="$pessoas"/>
  </div>
</div>

@endsection

@section('script')
<script src="{{ URL::asset('build/js/app.js') }}"></script>
<script src="{{ URL::asset('build/js/pages/imask.js') }}"></script>
<script src="{{ URL::asset('build/js/custom.js') }}"></script>
@endsection