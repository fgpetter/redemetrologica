<div class="card">
  @if (!isset($user))
    <div class="card-header">
      <h4 class="card-title mb-0">Inserir usuário</h4>
    </div>
  @endif
  <div class="card-body">
    @if(Session::has('password-error'))
      <div class="alert alert-warning alert-top-border" role="alert">
        <i class="ri-alert-line me-3 align-middle fs-lg text-warning"></i>
        <strong>Atenção!</strong>
        - Você está tentando acessar o painel com uma senha temporária, 
        por favor, altere sua senha para continuar.
      </div>
    @endif
    <form method="POST" action="{{ isset($user) ? route('user-update', $user->id) : route('user-create') }}">
      @csrf
      <div class="row gy-3">
        <div class="col-12">
          <label for="nome" class="form-label">Nome</label>
          <input type="text" class="form-control" name="nome" id="nome"
            value="{{ old('nome') ?? ($user->name ?? null) }}" placeholder="Nome">
          @error('nome')
            <div class="text-warning">{{ $message }}</div>
          @enderror
        </div>
        <div class="col-12">
          <label for="email" class="form-label">E-mail</label>
          <input type="email" class="form-control" name="email" id="email"
            value="{{ old('email') ?? ($user->email ?? null) }}" placeholder="E-mail">
          @error('email')
            <div class="text-warning">{{ $message }}</div>
          @enderror
        </div>

        <h6 class="mb-0 mt-4">ALTERAR SENHA</h6>
        <div class="col-6">
          <label for="password" class="form-label">Nova Senha</label>
          <input type="password" class="form-control" name="password" id="password" placeholder="Senha">
          @error('password') <div class="text-warning">{{ $message }}</div> @enderror
        </div>

        <div class="col-6">
          <label for="password_confirmation" class="form-label">Confirmar Senha</label>
          <input type="password" class="form-control" name="password_confirmation" id="password_confirmation" placeholder="Confirmar Senha">
          @error('password_confirmation') <div class="text-warning">{{ $message }}</div> @enderror
        </div>

        <div class="col-12">
          <button type="submit" class="btn btn-primary px-4">Salvar</button>
        </div>
      </div>
    </form>
    @can('admin')
      @isset($user)
        <x-painel.form-delete.delete route='user-delete' id="{{ $user->id }}" label="Usuário" />
      @endisset
    @endcan

    @canany(['admin','funcionario'])
      @if($user->pessoa)
      <div class="col mt-5">
        <div class="alert alert-light text-body bg-light alert-label-icon" role="alert">
          <i class="ri-user-line label-icon"></i>Usuário associado a pessoa: &nbsp; <strong>{{ $user->pessoa->nome_razao }}</strong> 
          <a href="{{"/painel/pessoa/insert/".$user->pessoa->uid}}" class="btn btn-sm btn-info float-end" style="margin-top:-3px"> EDITAR PESSOA </a>
        </div>
      </div>
      @endif
    @endcanany

  </div>
</div>
