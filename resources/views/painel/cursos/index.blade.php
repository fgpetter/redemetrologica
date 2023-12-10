@extends('layouts.master')
@section('title')
    Listagem de cursos
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Pessoas
        @endslot
        @slot('title')
            Listagem de cursos
        @endslot
    @endcomponent

    <div class="row">
        <div class="col">
            <x-painel.cursos.list :cursos="$cursos"/>
        </div>
    </div>
@endsection

@section('script')
@endsection
