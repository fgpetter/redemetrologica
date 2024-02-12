@extends('layouts.master')
@section('title')
    Editar Avaliador
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
            Cadastrar Instrutor
        @endslot
    @endcomponent
    <div class="row">

        <div class="col col-xxl-8">
            <x-painel.instrutores.insert />
        </div>


    </div>
@endsection

@section('script')
    <script src="{{ URL::asset('build/js/pages/imask.js') }}"></script>
    <script src="{{ URL::asset('build/js/custom.js') }}"></script>
@endsection
