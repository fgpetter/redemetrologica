@extends('layouts.master')
@section('title')
    Editar Agendamento de Interlab
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1') @endslot
        @slot('title')
            Cadastrar Agendamento de Interlab
        @endslot
    @endcomponent
    <div class="row">
        <div class="col-12">
            <x-painel.agenda-interlab.insert 
                :agendainterlab="$agendainterlab" 
                :interlabs="$interlabs"
                :materiaisPadrao="$materiaisPadrao"
                :interlabMateriaisPadrao="$interlabMateriaisPadrao"
            />
        </div>
    </div>
@endsection