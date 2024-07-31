@extends('layouts.master')
@section('title')
    Listagem de Agendamento de Cursosstyle="min-height:
@endsection
@section('content')
    <x-breadcrumb 
        li1="Cursos" li1link="curso-index"
        title="Listagem de Agendamento de Cursos"/>

    <div class="row">
        <div class="col">
            <x-painel.agendamento-cursos.list :agendacursos="$agenda_cursos"/>
        </div>
    </div>
@endsection