@extends('layouts.master')
@section('title')
    @if ($agendainterlab->id) Editar Agendamento de Interlab @else Cadastrar Agendamento de Interlab @endif
@endsection
@section('content')

    <x-breadcrumb 
    li1="Agendamento de Interlab" 
    li1link="agenda-interlab-index"
    :title="$agendainterlab->id ? 'Editar Agendamento de Interlab' : 'Cadastrar Agendamento de Interlab'"/>

    <div class="row">
        <div class="col-12">
            <x-painel.agenda-interlab.insert 
                :pessoas="$pessoas" 
                :agendainterlab="$agendainterlab" 
                :interlabs="$interlabs"
                :materiaisPadrao="$materiaisPadrao"
                :interlabDespesa="$interlabDespesa"
                :fabricantes="$fabricantes"
                :fornecedores="$fornecedores"
                :interlabParametros="$interlabParametros"
                :parametros="$parametros"
                :rodadas="$rodadas"
                :intelabinscritos="$intelabinscritos"
                :interlabempresasinscritas="$interlabempresasinscritas"
                :idinterlab="$idinterlab"
            />
        </div>
    </div>
@endsection