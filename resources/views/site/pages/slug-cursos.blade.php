@extends('site.layouts.layout-site')
@section('title') Curso @endsection
@section('content')
    <x-site.component-curso :agendacursos="$agendacursos" />
@endsection
