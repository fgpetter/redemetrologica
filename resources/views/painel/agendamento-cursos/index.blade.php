@extends('layouts.master')
@section('title')
    Lista Agendamento de Cursos
@endsection
@section('content')
    <x-breadcrumb 
        li1="Cursos" li1link="curso-index"
        title="Lista Agendamento de Cursos"/>

    <div class="row">
        <div class="col">
            <x-painel.agendamento-cursos.list :agendacursos="$agendacursos" :tipoagenda="$tipoagenda" />
        </div>
    </div>
@endsection