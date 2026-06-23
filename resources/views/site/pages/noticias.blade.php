@extends('site.layouts.layout-site')
@section('title') Notícias @endsection
@section('content')
    <x-site.component-post-list :posts="$posts" />

    {{-- main --}}
@endsection
