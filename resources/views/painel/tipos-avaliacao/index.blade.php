@extends('layouts.master')
@section('title')
    Listagem de tipos de avaliação
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            tipos de avaliação
        @endslot
        @slot('title')
            Listagem de tipos de avaliação
        @endslot
    @endcomponent

    <div class="row">
        <div class="col">
            <x-painel.tipos-avaliacao.list />
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ URL::asset('build/js/pages/imask.js') }}"></script>
    <script src="{{ URL::asset('build/js/custom.js') }}"></script>
@endsection
