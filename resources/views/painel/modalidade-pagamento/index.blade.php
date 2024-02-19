@extends('layouts.master')
@section('title')
    Listagem de modalidade de pagamento
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            modalidade de pagamento
        @endslot
        @slot('title')
            Listagem de modalidade de pagamento
        @endslot
    @endcomponent

    <div class="row">
        <div class="col-xxl-8">
            <x-painel.modalidade-pagamento.list :modalidadepagamentos="$modalidadepagamentos"/>
        </div>
    </div>
@endsection

@section('script')
@endsection
