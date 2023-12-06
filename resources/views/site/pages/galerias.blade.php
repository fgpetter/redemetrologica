@extends('site.layouts.layout-site')
@section('content')
    <x-site.component-postlist :posts="$posts" />

    {{-- main --}}
@endsection
