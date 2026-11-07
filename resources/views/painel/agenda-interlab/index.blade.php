@extends('layouts.master')
@section('title')
    Listagem de Agendamento de Interlabs
@endsection
@section('content')
    <x-breadcrumb title="Listagem de Agendamento de Interlabs" />

    <form method="GET" action="{{ route('agenda-interlab-fr135-anual') }}" class="d-flex gap-2 mb-3">
        <label for="ano-fr135" class="visually-hidden">Ano do FR-135</label>
        <select id="ano-fr135" name="ano" class="form-select w-auto" required>
            <option value="">Selecione o ano</option>
            @foreach ($anosFr135 as $ano)
                <option value="{{ $ano }}">{{ $ano }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-outline-primary">Baixar FR-135 anual</button>
    </form>
    <div class="row">

        <div class="col">
            <livewire:interlab.agenda-interlab-table />
        </div>
    </div>
    {{-- <div class="row">
        <div class="col">
            <x-painel.agenda-interlab.list :agendainterlabs="$agenda_interlabs" />
        </div>
    </div> --}}
@endsection
