@extends('layouts.master')
@section('title')
    Editar Post
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Post
        @endslot
        @slot('title')
            @isset($posts->id)
                Editar Post
            @else
                Cadastrar Post
            @endisset
        @endslot
    @endcomponent
    <div class="row">
        <div class="col-xl-6">
            <x-painel.noticias.insert :post="$post" />

        </div>

    </div>
@endsection

@section('script')
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
    <script src="{{ URL::asset('build/js/pages/imask.js') }}"></script>
    <script src="{{ URL::asset('build/js/custom.js') }}"></script>
@endsection
