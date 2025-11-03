@extends('layouts.master')
@section('title') Listagem de Avaliações @endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Pessoas @endslot
@slot('title') Listagem de Avaliações @endslot
@endcomponent

<div class="row">
  <div class="col">

    <livewire:avaliacoes.agenda-avaliacoes-table />
    
  </div>
</div>

@endsection

@section('script')
<script defer>
  const element = document.getElementById('choices-single-default')
  if(element){
    const choices = new Choices(element,{
      searchFields: ['label'],
      allowHTML: true
    });
  }
</script>
@endsection