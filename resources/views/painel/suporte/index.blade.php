@extends('layouts.master')
@section('title', 'Suporte')
@section('css')
    @include('painel.suporte.styles')
@endsection
@section('content')
    <x-breadcrumb li1="Suporte" li1link="suporte-index" title="Demandas de ajustes e melhorias" />
    <livewire:suporte.suporte-table />
@endsection
@section('script')
    @include('painel.suporte.scripts')
@endsection
