@extends('layouts.master')
@section('title')
    Listagem de {{ $tipo == 'noticia' ? 'notícias' : 'galerias'}}
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Posts
        @endslot
        @slot('title')
            Listagem de {{ $tipo == 'noticia' ? 'notícias' : 'galerias'}}
        @endslot
    @endcomponent

    <div class="row">
        <div class="col">
            <x-painel.post.list :posts="$posts" :tipo="$tipo"/>
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
