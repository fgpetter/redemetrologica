@extends('layouts.master')
@section('title')
    Listagem de pessoas
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Pessoas
        @endslot
        @slot('title')
            Listagem de pessoas
        @endslot
    @endcomponent

    <div class="row">
        <div class="col">
            <x-painel.pessoas.list :pessoas="$pessoas" />
        </div>
    </div>
@endsection