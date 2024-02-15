@extends('layouts.master')
@section('title') Editar Instrutor @endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1') Pessoas @endslot
        @slot('title') @if ($instrutor->id) Editar Instrutor @else Cadastrar Instrutor @endif @endslot
    @endcomponent
    <div class="row">

        <div class="col col-xxl-8">
            <x-painel.instrutores.insert />
        </div>
        @if ($instrutor->id)
        <div class="col-4">
            <x-painel.funcionarios.dados-bancarios :instrutor="$instrutor"/>
        </div>
        @endif


    </div>
@endsection

@section('script')
    <script src="{{ URL::asset('build/js/pages/imask.js') }}"></script>
    <script src="{{ URL::asset('build/js/custom.js') }}"></script>
@endsection
