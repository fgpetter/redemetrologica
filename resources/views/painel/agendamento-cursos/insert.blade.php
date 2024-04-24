@extends('layouts.master')
@section('title')
    Editar Agendamento de Cursos
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Pessoas
        @endslot
        @slot('title')
            {{-- @if ($Instrutor->id)
                Editar Instrutor
            @else
                Cadastrar Instrutor
            @endif --}}
            Cadastrar Agendamento de Cursos
        @endslot
    @endcomponent
    <div class="row">
        <div class="col col-xxl-8">
            <x-painel.agendamento-cursos.insert 
            :instrutores="$instrutores" 
            :cursos="$cursos" 
            :empresas="$empresas" 
            :agendacurso="$agendacurso"
            :inscritos="$inscritos"
            :cursoatual="$curso_atual"
            :instrutoratual="$instrutor_atual"
            :despesas="$despesas"
            />
        </div>


    </div>
@endsection