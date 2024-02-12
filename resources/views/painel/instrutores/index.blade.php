@extends('layouts.master')
@section('title')
    Listagem de Instrutores
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Pessoas
        @endslot
        @slot('title')
            Listagem de Instrutores
        @endslot
    @endcomponent

    <div class="row">
        <div class="col">
            <x-painel.instrutores.list />
        </div>
    </div>
@endsection

@section('script')
@endsection
