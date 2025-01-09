@extends('layouts.master')
@section('title')
    Contas a receber
@endsection
@section('content')
  @component('components.breadcrumb')
    @slot('li_1')
      Financeiro
    @endslot
    @slot('title')
      Contas a receber
    @endslot
  @endcomponent

  <div class="row">
    <div class="col">
      <x-painel.lancamento-financeiro.list-areceber 
      :lancamentosfinanceiros="$lancamentosfinanceiros" 
      :pessoas="$pessoas" 
      :cursos="$cursos"
      :agendainterlabs="$agendainterlabs"/>
    </div>
  </div>
@endsection

@section('script')
  <script src="{{ URL::asset('build/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>
  <script defer>
    const pessoa = document.getElementById('pessoa')
    if(pessoa){
      const choices = new Choices(pessoa,{
        searchFields: ['label'],
        maxItemCount: -1
      });
    }
  </script>
@endsection