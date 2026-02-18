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
      @elseif (session('interlab'))
          @include('painel.painel-cliente.nova-inscricao-pd')
      @else
          {{-- Lista de interlabs inscritos --}}
          <x-painel.painel-cliente.inscritos-interlab :interlabs="auth()->user()->pessoa->interlabs()->with('laboratorio.analistas')->get()" />

          {{-- Lista de cursos inscritos e convites --}}
          <x-painel.painel-cliente.inscritos-cursos :cursos="auth()->user()->pessoa->cursos" />
      @endif
    @endif

  @endif

@endsection
