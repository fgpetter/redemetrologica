@extends('layouts.master')
@section('title') Listagem de Avaliações @endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Pessoas @endslot
@slot('title') Listagem de Avaliações @endslot
@endcomponent

<div class="row">
  <div class="col">
    <x-painel.avaliacoes.list :avaliacoes="$avaliacoes" :laboratorios="$laboratorios"/>
  </div>
</div>

@endsection

@section('script')
<script src="{{ URL::asset('build/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>
<script defer>
  const element = document.getElementById('choices-single-default')
  if(element){
    const choices = new Choices(element,{
      searchFields: ['label'],
      maxItemCount: -1,
      allowHTML: true
    });
  }

</script>
@endsection