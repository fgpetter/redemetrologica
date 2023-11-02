@extends('layouts.master')
@section('title') @lang('translation.starter')  @endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Usuários @endslot
@slot('title') Listagem de usuários @endslot
@endcomponent
@php
  $users = [
    ['name' => 'Filipe Da Silva Sauro', 'id' => 1 ],
    ['name' => 'Jose Da Silva Sauro', 'id' => 2 ],
    ['name' => 'Joao Da Silva Sauro', 'id' => 3 ],
  ]
@endphp
<div class="row">
  <x-painel.users.userlist :users="$users"/>
  <x-painel.users.userinsert />
</div>

@endsection

@section('script')
<script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection