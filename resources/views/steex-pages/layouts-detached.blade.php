@extends('layouts.layouts-detached')
@section('title') @lang('translation.detached') @endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Layouts @endslot
@slot('title') Detached @endslot
@endcomponent
@endsection