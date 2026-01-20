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
                <div class="col-9">
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
                <div class="col-3">
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

      @if (session('curso'))
        <livewire:painel-cliente.confirm-inscricao-curso />
      @endif

      @if ( session('interlab') )
         @include('painel.painel-cliente.nova-inscricao-pd')
      @elseif( auth()->user()->pessoa->interlabs()->count() > 0 )
        <x-painel.painel-cliente.inscritos-interlab :interlabs="auth()->user()->pessoa->interlabs()->get()" />
      @endif

      {{-- todo - melhorar lista de cursos inscritos e convites --}}
            @if (auth()->user()->pessoa->cursos->isNotEmpty())
                <div class="col-12 col-xxl-6 col-xl-8">
                    <div class="card">
                        <div class="card-body lh-lg">
                            <h5 class="h5 mb-3">Você está inscrito nos seguintes cursos:</h5>
                            <ul class="list-group">
                                @foreach (auth()->user()->pessoa->cursos as $curso)
                                    <li class="list-group-item">
                                        <strong>Nome:</strong>
                                        {{ $curso->agendaCurso->curso->descricao }} <br>

                                        <strong>Período:</strong>
                                        de
                                        @if ($curso->agendaCurso->data_inicio)
                                            {{ \Carbon\Carbon::parse($curso->agendaCurso->data_inicio)->format('d/m/Y') }}
                                        @endif
                                        até
                                        @if ($curso->agendaCurso->data_fim)
                                            {{ \Carbon\Carbon::parse($curso->agendaCurso->data_fim)->format('d/m/Y') }}
                                        @endif
                                        {{ $curso->agendaCurso->horario }} <br>

                                        <strong>Status do agendamento:</strong>
                                        {{ $curso->agendaCurso->status }} <br>

                                        <strong>Local:</strong>
                                        {{ $curso->agendaCurso->endereco_local }} <br>

                                        @if ($curso->empresa)
                                            <strong>Empresa vinculada:</strong>
                                            {{ $curso->empresa->nome_razao }} <br>
                                        @endif

                                        @if ($curso->agendaCurso->cursoMateriais->count() > 0 && $curso->agendaCurso->status === 'CONFIRMADO')
                                            <strong>Materiais do curso:</strong>
                                            <ul class="list-unstyled ms-3 mt-2">
                                                @foreach ($curso->agendaCurso->cursoMateriais as $material)
                                                    <li class="mb-1">
                                                        <i class="bx bx-file me-1"></i>
                                                        <a href="{{ asset('storage/' . $material->arquivo) }}"
                                                            target="_blank" class="text-primary">
                                                            {{ $material->descricao ?: 'Material ' . $loop->iteration }}
                                                        </a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif

                                        @if ($curso->agendaCurso->convites->where('pessoa_id', auth()->user()->pessoa->id)->isNotEmpty())
                                            <strong>Convidados por você:</strong>
                                            <ul class="list-unstyled ms-3 mt-2">
                                                @foreach ($curso->agendaCurso->convites->where('pessoa_id', auth()->user()->pessoa->id) as $convite)
                                                    <li>
                                                        <i class="bx bx-user me-1"></i>
                                                        {{ $convite->nome }} ({{ $convite->email }})
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif

                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            @php
                $convitesSemInscricao = \App\Models\Convite::where('pessoa_id', auth()->user()->pessoa->id)
                    ->whereHas('agendaCurso', function ($query) {
                        $query->whereIn('status', ['AGENDADO', 'CONFIRMADO']);
                    })
                    ->whereDoesntHave('agendaCurso.inscritos', function ($query) {
                        $query->where('pessoa_id', auth()->user()->pessoa->id);
                    })
                    ->get();

                $convitesAgrupadosPorCurso = $convitesSemInscricao->groupBy('agendaCurso.curso.descricao');
            @endphp
            {{-- lista de convites enviados sem inscrição do usuario --}}
            @if ($convitesAgrupadosPorCurso->isNotEmpty())
                <div class="col-12 col-xxl-6 col-xl-8 mt-4">
                    <div class="card">
                        <div class="card-body lh-lg">
                            <h5 class="h5 mb-3">Convites realizados por você:</h5>
                            <ul class="list-group">
                                @foreach ($convitesAgrupadosPorCurso as $cursoDescricao => $convites)
                                    <li class="list-group-item">
                                        <strong>Nome:</strong> {{ $cursoDescricao }} <br>

                                        <strong>Período:</strong>
                                        de
                                        @if ($convites->first()->agendaCurso->data_inicio)
                                            {{ \Carbon\Carbon::parse($convites->first()->agendaCurso->data_inicio)->format('d/m/Y') }}
                                        @endif
                                        até
                                        @if ($convites->first()->agendaCurso->data_fim)
                                            {{ \Carbon\Carbon::parse($convites->first()->agendaCurso->data_fim)->format('d/m/Y') }}
                                        @endif
                                        {{ $convites->first()->agendaCurso->horario }} <br>

                                        <strong>Status do agendamento:</strong>
                                        {{ $convites->first()->agendaCurso->status }} <br>

                                        <strong>Local:</strong>
                                        {{ $convites->first()->agendaCurso->endereco_local }} <br>

                                        @if ($convites->first()->empresa)
                                            <strong>Empresa vinculada:</strong>
                                            {{ $convites->first()->empresa->nome_razao }} <br>
                                        @endif

                                        <strong>Convidados:</strong>
                                        <ul class="list-unstyled ms-3 mt-2">
                                            @foreach ($convites as $convite)
                                                <li>
                                                    <i class="bx bx-user me-1"></i>
                                                    {{ $convite->nome }} ({{ $convite->email }})
                                                </li>
                                            @endforeach
                                        </ul>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif
            {{-- todo - melhorar lista de cursos inscritos e convites --}}



        @endif

  @endif

@endsection
