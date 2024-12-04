@extends('site.layouts.layout-site')
@section('title') Notícias @endsection
@section('content')
    <x-site.component-postlist :posts="$posts" />

    {{-- main --}}
@endsection
