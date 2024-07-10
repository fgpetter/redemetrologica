@extends('layouts.master')
@section('title') Editar Arquivo @endsection
@section('content')

    @component('components.breadcrumb')
        @slot('li_1') Post @endslot
        @slot('title')
            @isset($download->id) 
                Editar Arquivo
            @else
                Cadastrar Arquivo
            @endisset
        @endslot
    @endcomponent

    <div class="row">
        <div class="col-xl-6">
            <x-painel.downloads.insert :download="$download" />
        </div>

    </div>
@endsection