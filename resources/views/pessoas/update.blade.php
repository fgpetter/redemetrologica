@extends('layouts.master')
@section('title') Editar Usuário @endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Usuários @endslot
@slot('title') Editar Usuário @endslot
@endcomponent

<div class="row">
  <div class="col-xl-6">
    <x-painel.users.insert :user="$user"/>
  </div>
</div>

@endsection

@section('script')
<script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection