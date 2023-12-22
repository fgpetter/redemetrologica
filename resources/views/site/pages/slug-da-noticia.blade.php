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



    {{-- menu lateral --}}

    {{-- menu lateral --}}

    {{-- main --}}
@endsection
{{-- @section('script')
    <script src="{{ URL::asset('build/js/pages/imask.js') }}"></script>
    <script src="{{ URL::asset('build/js/custom.js') }}"></script>
@endsection --}}
