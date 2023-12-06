@extends('site.layouts.layout-site')
@section('content')
    {{-- banner --}}
    <x-site.component-title :post="$post" />
    {{-- banner --}}


    {{-- main --}}
    <x-site.component-post :post="$post" />


    {{-- menu lateral --}}

    {{-- menu lateral --}}

    {{-- main --}}
@endsection
