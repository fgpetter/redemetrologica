@extends('layouts.master')
@section('title')
    Editar {{ $tipo }}
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Post
        @endslot
        @slot('title')
            @isset($posts->id)
                Editar {{ $tipo }}
            @else
                Cadastrar {{ $tipo }}
            @endisset
        @endslot
    @endcomponent
    <div class="row">
        <div class="col-xl-6">
            <x-painel.post.insert :post="$post" :postMedia="$postMedia" :tipo="$tipo" />
        </div>

    </div>
@endsection