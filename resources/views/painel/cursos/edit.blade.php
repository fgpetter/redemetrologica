@extends('layouts.master')
@section('title') Editar Curso @endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Cursos @endslot
@slot('title') @if ($curso->id) Editar Curso @else Cadastrar Curso @endif @endslot
@endcomponent
<div class="row">

  <div class="col-xl-7 col-xxl-6">
    <x-painel.cursos.edit :curso="$curso"/>
  </div>

  <div class="col-xl-5 col-xxl-6">
    @if($curso->id)
      <x-painel.cursos.arquivos-curso :curso="$curso"/>

      <div class="card">
        <div class="card-body d-flex justify-content-between align-items-center">
          <span>Baixar doc com os dados do curso</span>
          <a href="{{ route('curso-download-documento', $curso->uid) }}" class="btn btn-primary">Baixar</a>
        </div>
      </div>
    @endif
  </div>

</div>

@endsection