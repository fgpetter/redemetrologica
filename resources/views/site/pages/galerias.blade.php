@extends('site.layouts.layout-site')
@section('title') Galeria @endsection
@section('content')
    <x-site.component-postlist :posts="$posts" />

    {{-- main --}}
@endsection
