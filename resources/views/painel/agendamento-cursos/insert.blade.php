@extends('layouts.master')
@section('title')
    @if ($agendacurso->id) Editar Agendamento de Cursos @else Cadastrar Agendamento de Cursos @endif
@endsection
@section('content')

    <x-breadcrumb 
    li1="Agendamento de Cursos" li1link="agendamento-curso-index"
    :title="$agendacurso->id ? 'Editar Agendamento de Cursos' : 'Cadastrar Agendamento de Cursos'" />
    
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
            :materiaispadrao="$materiaispadrao"
            />
        </div>


    </div>
@endsection