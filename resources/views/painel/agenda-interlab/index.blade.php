@extends('layouts.master')
@section('title')
    Listagem de Agendamento de Interlabs
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
        @endslot
        @slot('title')
            Listagem de Agendamento de Interlabs
        @endslot
    @endcomponent

    <div class="row">
        <div class="col">
            <x-painel.agenda-interlab.list :agendainterlabs="$agenda_interlabs"/>
        </div>
    </div>
@endsection
