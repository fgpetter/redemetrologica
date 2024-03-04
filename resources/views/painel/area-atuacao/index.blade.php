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
            <x-painel.areas-atuacao.list :areas_atuacao="$areas_atuacao"/>
        </div>
    </div>
@endsection