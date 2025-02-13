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
        {{-- carrega componente em app\View\Components\Painel\PainelCliente\ConfirmaInscricao --}}
        <x-painel.painel-cliente.confirma-inscricao />
      @endif

      @if ( session('interlab') )
        {{-- carrega componente em app\View\Components\Painel\PainelCliente\ConfirmaInscricao --}}
        <x-painel.painel-cliente.confirma-inscricao-interlab />
      @endif

      @if ( auth()->user()->pessoa->cursos->count() > 0 )
        <div class="col-12 col-xxl-6 col-xl-8">
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

      @if ( $empresas = auth()->user()->pessoa->empresas->first() )
        @if($empresas->empresaInterlabs->count() > 0)
          {{-- carrega componente em app\View\Components\Painel\PainelCliente\LaboratoriosInscritosInterlab --}}
          <x-painel.painel-cliente.laboratorios-inscritos-interlab />
        @endif
      @endif

    @endif

  @endif

@endsection

@section('script')
@endsection