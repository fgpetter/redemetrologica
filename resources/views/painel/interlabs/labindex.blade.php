@extends('layouts.master')
@section('title')
    Listagem de Laboratórios do Interlab
@endsection
@section('content')
    <x-breadcrumb title="Listagem de Laboratórios do Interlab" />

    <div class="row">

        <div class="col">
          
            <livewire:interlab.lab-table />
        </div>
    </div>
@endsection
