@extends('layouts.master')
@section('title')
    @if ($agendacurso->id) Editar Agendamento de Cursos @else Cadastrar Agendamento de Cursos @endif
@endsection
@section('content')

  <x-breadcrumb 
  li1="Agendamento de Cursos" li1link="agendamento-curso-index"
  :title="$agendacurso->id ? 'Editar Agendamento de Cursos' : 'Cadastrar Agendamento de Cursos'" />
  
  <div class="row">
    <div class="col col-xxl-12">
      <x-painel.agendamento-cursos.edit 
      :instrutores="$instrutores" 
      :cursos="$cursos" 
      :empresas="$empresas" 
      :pessoas="$pessoas" 
      :agendacurso="$agendacurso"
      :cursoatual="$curso_atual"
      :instrutoratual="$instrutor_atual"
      :despesas="$despesas"
      :materiaispadrao="$materiaispadrao"
      :tipoagenda="$tipoagenda"
      />
    </div>
  </div>
@endsection
