@extends('layouts.master')
@section('title')
    Listagem de posts
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Posts
        @endslot
        @slot('title')
            Listagem de posts
        @endslot
    @endcomponent

    <div class="row">
        <div class="col">
            <x-painel.noticias.list :posts="$posts" />
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
    <script src="{{ URL::asset('build/js/pages/imask.js') }}"></script>
    <script src="{{ URL::asset('build/js/custom.js') }}"></script>
@endsection
