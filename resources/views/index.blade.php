@extends('layouts.master')
@section('title') @lang('translation.starter') @endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Inicio @endslot
@slot('title') Painel @endslot
@endcomponent
@endsection
@section('script')
@endsection