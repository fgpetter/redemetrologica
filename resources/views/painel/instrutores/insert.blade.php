@extends('layouts.master')
@section('title')
    Editar Instrutor
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Pessoas
        @endslot
        @slot('title')
            @if ($instrutor->id)
                Editar Instrutor
            @else
                Cadastrar Instrutor
            @endif
        @endslot
    @endcomponent
    <div class="row">

        <div class="col col-xxl-8">

            <x-painel.instrutores.insert :instrutor="$instrutor" :cursos="$cursos" :cursoshabilitados="$cursoshabilitados"/>
        </div>

    </div>
@endsection