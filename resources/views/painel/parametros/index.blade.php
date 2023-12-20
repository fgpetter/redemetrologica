@extends('layouts.master')
@section('title')
    Listagem de parâmetros
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            parâmetros
        @endslot
        @slot('title')
            Listagem de parâmetros
        @endslot
    @endcomponent

    <div class="row">
        <div class="col">
            <x-painel.parametros.list />
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ URL::asset('build/js/pages/imask.js') }}"></script>
    <script src="{{ URL::asset('build/js/custom.js') }}"></script>
@endsection
