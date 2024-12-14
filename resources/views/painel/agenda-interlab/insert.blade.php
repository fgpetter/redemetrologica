@extends('layouts.master')
@section('title')
    Editar Agendamento de Interlab
@endsection
@section('content')

    <x-breadcrumb 
    li1="Agendamento de Interlab" 
    li1link="agenda-interlab-index"
    title="Cadastrar Agendamento de Interlab"/>

    <div class="row">
        <div class="col-12">
            <x-painel.agenda-interlab.insert 
                :agendainterlab="$agendainterlab" 
                :interlabs="$interlabs"
                :materiaisPadrao="$materiaisPadrao"
                :interlabDespesa="$interlabDespesa"
                :fabricantes="$fabricantes"
                :fornecedores="$fornecedores"
                :interlabParametros="$interlabParametros"
                :parametros="$parametros"
                :rodadas="$rodadas"
            />
        </div>
    </div>
@endsection