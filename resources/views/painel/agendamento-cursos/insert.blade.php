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
      :pessoas="$pessoas" 
      :agendacurso="$agendacurso"
      :inscritos="$inscritos"
      :cursoatual="$curso_atual"
      :instrutoratual="$instrutor_atual"
      :despesas="$despesas"
      :materiaispadrao="$materiaispadrao"
      :tipoagenda="$tipoagenda"
      />
    </div>
  </div>
@endsection

@section('script')
  <script defer>
    const pessoa = document.getElementById('pessoa')
    const empresa = document.getElementById('empresa')
    if(pessoa){
      const choices = new Choices(pessoa,{
        searchFields: ['label'],
        allowHTML: true
      });
    }
    if(empresa){
      const choices = new Choices(empresa,{
        searchFields: ['label'],
        allowHTML: true
      });
    }
  </script>
@endsection