@section('script')
    <script src="{{ URL::asset('build/js/pages/imask.js') }}"></script>
    <script src="{{ URL::asset('build/js/custom.js') }}"></script>
@endsection

@extends('layouts.master')
@section('title')
    Listagem de áreas de atuação
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Áreas de atuação
        @endslot
        @slot('title')
            Listagem de áreas de atuação
        @endslot
    @endcomponent

    <div class="row">
        <div class="col">
            <x-painel.areas-atuacao.list " />
            </div>
        </div>
@endsection
@section('script')
    <script src="{{ URL::asset('build/js/pages/imask.js') }}"></script>
        <script src="{{ URL::asset('build/js/custom.js') }}"></script>
@endsection
