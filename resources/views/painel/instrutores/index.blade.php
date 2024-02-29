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
            <x-painel.instrutores.list :instrutores="$instrutores" :pessoas="$pessoas"/>
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ URL::asset('build/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>
    <script defer>
    const element = document.getElementById('choices-single-default')
    if(element){
        const choices = new Choices(element,{
        searchFields: ['label'],
        });
    }

    </script>
@endsection