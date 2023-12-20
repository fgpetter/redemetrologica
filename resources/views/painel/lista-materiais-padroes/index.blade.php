@extends('layouts.master')
@section('title')
    Listagem de materiais padrões
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            materiais padrões
        @endslot
        @slot('title')
            Listagem de materiais padrões
        @endslot
    @endcomponent

    <div class="row">
        <div class="col">
            <x-painel.lista-materiais-padroes.list />
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ URL::asset('build/js/pages/imask.js') }}"></script>
    <script src="{{ URL::asset('build/js/custom.js') }}"></script>
@endsection
