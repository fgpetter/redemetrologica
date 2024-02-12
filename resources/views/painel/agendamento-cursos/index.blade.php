@extends('layouts.master')
@section('title')
    Listagem de Agendamento de Cursos
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Pessoas
        @endslot
        @slot('title')
            Listagem de Agendamento de Cursos
        @endslot
    @endcomponent

    <div class="row">
        <div class="col">
            <x-painel.agendamento-cursos.list />
        </div>
    </div>
@endsection

@section('script')
@endsection
