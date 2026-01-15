@extends('layouts.master')
@section('title')
    Listagem de Agendamento de Interlabs
@endsection
@section('content')
    <x-breadcrumb title="Listagem de Agendamento de Interlabs" />

    <div class="row">

        <div class="col">
            <livewire:interlab.agenda-interlab-table />
        </div>
    </div>
    {{-- <div class="row">
        <div class="col">
            <x-painel.agenda-interlab.list :agendainterlabs="$agenda_interlabs" />
        </div>
    </div> --}}
@endsection
