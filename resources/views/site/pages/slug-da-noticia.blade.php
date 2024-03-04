@extends('site.layouts.layout-site')
@section('content')
    {{-- banner --}}
    <x-site.component-title :post="$post" />
    {{-- banner --}}


    {{-- main --}}
    @if ($post->tipo == 'noticia')
        <x-site.component-post :post="$post" />
    @else
        <x-site.component-postGaleria :post="$post" :postMedia="$postMedia" />
    @endif

@endsection