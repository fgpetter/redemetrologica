@extends('layouts.master')
@section('title') Listagem de fornecedores @endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Pessoas @endslot
@slot('title') Listagem de fornecedores @endslot
@endcomponent

<div class="row">
  <div class="col">
    <x-painel.fornecedores.list :fornecedores="$fornecedores" :pessoas="$pessoas"/>
  </div>
</div>

@endsection

@section('script')
  <script defer>
    const element = document.getElementById('choices-single-default')
    if(element){
      const choices = new Choices(element,{
        searchFields: ['label'],
      });
    }
  </script>
@endsection