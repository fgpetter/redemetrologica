@extends('layouts.master')

@section('title') Painel @endsection

@section('content')
  @component('components.breadcrumb')
    @slot('li_1') Inicio @endslot
    @slot('title') Painel @endslot
  @endcomponent

  @if( auth()->user()->pessoa )

    {{-- Habilita impersonamento --}}
    @canany(['admin','funcionario'])
      <div class="col-6">
        <div class="card mb-4">
          <div class="card-body">
            <strong>Selecione um usuário para assumir a visão de cliente:</strong>
            <form action="{{ route('impersonate') }}" method="POST">
              @csrf
              <div class="row">
                <div class="col">
                  <select class="form-control" data-choices name="user_id" id="user_id">
                    <option value="">Selecione um usuário</option>
                    @foreach(App\Models\User::whereHas('permissions', fn($q) => $q->where('permission', 'cliente'))
                      ->select('id', 'name', 'email')->orderBy('name')->get() as $user)
                      <option value="{{ $user->id }}">
                        {{ $user->name .' - '. $user->email }}
                      </option>
                    @endforeach
                  </select>
                </div>
                <div class="col-2">
                  <button type="submit" class="btn btn-primary">
                    Personificar
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    @endcanany

    @if(session('impersonator_id'))
      <div class="alert alert-warning">
        <form action="{{ route('impersonate-stop') }}" method="POST" class="d-inline">
          @csrf
          <span class="text-secondary"> Atuando como: {{ auth()->user()->name }} </span>
          <button type="submit" class="btn btn-warning btn-sm ms-4">
            Parar Personificação
          </button>
        </form>
      </div>
    @endif


    @if(auth()->user()->pessoa->funcionario)
      {{-- carega componentes referentes ao funcionário e área --}}
      Painel de funcionários
    @else

      @if ( session('curso') )
        {{-- carrega componente em app\View\Components\Painel\PainelCliente\ConfirmaInscricao --}}
        <x-painel.painel-cliente.confirma-inscricao />
        <livewire:painel-cliente.confirm-inscricao-curso />
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
                
                @if($curso->agendaCurso->cursoMateriais->count() > 0 && $curso->agendaCurso->status == 'CONFIRMADO')
                  <strong>Materiais do curso:</strong>
                  <ul class="list-unstyled ms-3 mt-2">
                    @foreach($curso->agendaCurso->cursoMateriais as $material)
                      <li class="mb-1">
                        <i class="bx bx-file me-1"></i>
                        <a href="{{ asset('storage/' . $material->arquivo) }}" target="_blank" class="text-primary">
                          {{ $material->descricao ?: 'Material ' . $loop->iteration }}
                        </a>
                      </li>
                    @endforeach
                  </ul>
                @endif
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
  <script defer>
    const element = document.getElementById('user_id')
    if(element){
      const choices = new Choices(element,{
        searchFields: ['label'],
        maxItemCount: -1,
        allowHTML: true
      });
    }
  </script>
@endsection