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
            <x-painel.parametros.list :parametros="$parametros"/>
        </div>
    </div>
@endsection

@section('script')
@endsection
