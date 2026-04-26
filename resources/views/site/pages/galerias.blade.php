@extends('site.layouts.layout-site')
@section('title') Galeria @endsection
@section('content')
    <x-site.component-post-list :posts="$posts" />

    {{-- main --}}
@endsection
