@extends('layouts.master')

@section('title') Painel @endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1') Inicio @endslot
        @slot('title') Painel @endslot
    @endcomponent

    @if( auth()->user()->pessoa )

        @if(auth()->user()->pessoa->funcionario)
            {{-- carega componentes referentes ao funcionário e área --}}
            Painel de funcionários
        @else

            @if ( session('curso') )
                {{-- carrega componente em app\view\ConfirmaInscricao --}}
                <x-painel.painel-cliente.confirma-inscricao />
            @endif

            @if ( session('interlab') )
                {{-- carrega componente em app\view\ConfirmaInscricao --}}
                <x-painel.painel-cliente.confirma-inscricao-interlab />
            @endif

            @if ( auth()->user()->pessoa->cursos->count() > 0 )
                <div class="col-6">
                    <div class="card">
                        <div class="card-body lh-lg">
                            <h5 class="h5 mb-3">Você está inscrito no seguinte curso:</h5>
            
                            @foreach ( auth()->user()->pessoa?->cursos as $curso )
                                <strong>Nome:</strong> {{ $curso->agendaCurso->curso->descricao }} <br>
                                <strong>Período:</strong>
                                de
                                @if($curso->agendaCurso->data_inicio)
                                {{ \Carbon\Carbon::parse($curso->agendaCurso->data_inicio)->format('d/m/Y') }} 
                                @endif
                                até 
                                @if($curso->agendaCurso->data_fim)
                                {{ \Carbon\Carbon::parse($curso->agendaCurso->data_fim)->format('d/m/Y') }} 
                                @endif
                                {{ $curso->agendaCurso->horario }} <br>
                                <strong>Status do agendamento:</strong> {{ $curso->agendaCurso->status }} <br>
                                <strong>Local: </strong> {{ $curso->agendaCurso->endereco_local }} <br>
                            @endforeach
            
                        </div>
                    </div>
                </div>
            @endif

            @if ( auth()->user()->pessoa->interlabs->count() > 0 )
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

    @endif

@endsection

@section('script')
@endsection