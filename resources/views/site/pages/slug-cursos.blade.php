@extends('site.layouts.layout-site')
@section('title')
  {{ $agendacursos->curso->descricao }}
@endsection
@section('content')
  <x-site.component-curso :agendacursos="$agendacursos" />
@endsection
