@extends('layouts.master')

@section('title') @lang('translation.starter') @endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1') Inicio @endslot
        @slot('title') Painel @endslot
    @endcomponent
    @if(auth()->user()->pessoa?->funcionario)
        {{-- carega componentes referentes ao funcionário e área --}}
        é funcionário
    @else

        @if ( session('curso') )
            {{-- carrega componente em app\view\ConfirmaInscricao --}}
            <x-painel.painel-cliente.confirma-inscricao />
        @endif

        @if ( session('interlab') )
            {{-- carrega componente em app\view\ConfirmaInscricao --}}
            <x-painel.painel-cliente.confirma-inscricao-interlab />
        @endif

        @if ( auth()->user()->pessoa?->cursos?->count() > 0 )
            <div class="col-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="h5 mb-3">Você está inscrito no seguinte curso:</h5>
        
                        @foreach ( auth()->user()->pessoa?->cursos as $curso )
                            <strong>Nome:</strong> {{ $curso->agendaCurso->curso->descricao }} <br>
                            <strong>Data:</strong> {{ \Carbon\Carbon::parse($curso->agendaCurso->data_inicio)->format('d/m/Y') }} <br>
                            <strong>Hora:</strong> {{ $curso->agendaCurso->horario }} <br>
                            <strong>Status do agendamento:</strong> {{ $curso->agendaCurso->status }} <br>
                            <strong>Local: </strong> {{ $curso->agendaCurso->endereco_local }} <br>
                        @endforeach
        
                    </div>
                </div>
            </div>
        @endif

        @if ( auth()->user()->pessoa?->interlabs?->count() > 0 )
        <div class="col-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="h5 mb-3">Você está inscrito no seguinte Interlab:</h5>
    
                    @foreach ( auth()->user()->pessoa?->interlabs as $interlab )
                        <strong>Nome:</strong> {{ $interlab->agendaInterlab->interlab->nome }} <br>
                    @endforeach
    
                </div>
            </div>
        </div>
        @endif

    @endif

@endsection

@section('script')
@endsection