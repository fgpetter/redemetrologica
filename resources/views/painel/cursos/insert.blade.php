@extends('layouts.master')
@section('title') Editar Curso @endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Cursos @endslot
@slot('title') @if ($curso->id) Editar Curso @else Cadastrar Curso @endif @endslot
@endcomponent
<div class="row">

  <div class="col-xl-7 col-xxl-6">
    <x-painel.cursos.insert :curso="$curso"/>
  </div>

  <div class="col-xl-5 col-xxl-6">
    @if($curso->id)
      <x-painel.cursos.arquivos-curso :curso="$curso"/>
    @endif
  </div>

  <div class="col-xl-5 col-xxl-6">
  </div>

</div>

@endsection