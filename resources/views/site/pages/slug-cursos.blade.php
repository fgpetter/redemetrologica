@extends('site.layouts.layout-site')
@section('content')
    <x-site.component-curso :agendacursos="$agendacursos" />
@endsection
