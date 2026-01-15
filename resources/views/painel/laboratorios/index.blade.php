@extends('layouts.master')
@section('title') Listagem de Laboratórios @endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Pessoas @endslot
@slot('title') Listagem de Laboratórios @endslot
@endcomponent

<div class="row">
  <div class="col">
    <x-painel.laboratorios.list :laboratorios="$laboratorios" :pessoas="$pessoas"/>
  </div>
</div>

@endsection

@section('script')
<script defer>
  const element = document.getElementById('pessoa')
  if(element){
    const choices = new Choices(element,{
      searchFields: ['label'],
      allowHTML: true
    });
  }

</script>
@endsection