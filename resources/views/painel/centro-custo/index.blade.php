@extends('layouts.master')
@section('title')
    Listagem de centros de custo
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            centros de custo
        @endslot
        @slot('title')
            Listagem de centros de custo
        @endslot
    @endcomponent

    <div class="row">
        <div class="col-xxl-8">
            <x-painel.centro-custo.list :centrocustos="$centrocustos"/>
        </div>
    </div>
@endsection