@extends('layouts.master')
@section('title')
    Listagem de Arquivos para Download
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1') Painel @endslot
        @slot('title') Listagem de Arquivos para Download @endslot
    @endcomponent

    <div class="row">
        <div class="col">
            <x-painel.downloads.list :downloads="$downloads" />
        </div>
    </div>
@endsection