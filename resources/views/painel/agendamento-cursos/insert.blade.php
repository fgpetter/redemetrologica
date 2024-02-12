@extends('layouts.master')
@section('title')
    Editar Agendamento de Cursos
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Pessoas
        @endslot
        @slot('title')
            {{-- @if ($Instrutor->id)
                Editar Instrutor
            @else
                Cadastrar Instrutor
            @endif --}}
            Cadastrar Agendamento de Cursos
        @endslot
    @endcomponent
    <div class="row">

        <div class="col col-xxl-8">
            <x-painel.agendamento-cursos.insert />
        </div>


    </div>
@endsection

@section('script')
    <script src="{{ URL::asset('build/js/pages/imask.js') }}"></script>
    <script src="{{ URL::asset('build/js/custom.js') }}"></script>
@endsection
