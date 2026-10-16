@extends('layouts.master')
@section('title', $demanda ? 'Suporte — '.$demanda->titulo : 'Nova demanda')
@section('css')
    @include('painel.suporte.styles')
@endsection
@section('content')
    <x-breadcrumb li1="Suporte" li1link="suporte-index" :title="$demanda ? 'Demanda #'.$demanda->id : 'Nova demanda'" />
    <livewire:suporte.demanda-detalhe :demanda="$demanda" />
@endsection
@section('script')
    @include('painel.suporte.scripts')
@endsection
