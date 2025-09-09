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
        <div class="col-6">
          <label for="nome" class="form-label">Nome</label>
          <input type="text" class="form-control" name="nome" id="nome"
            value="{{ old('nome') ?? ($user->name ?? null) }}" placeholder="Nome">
          @error('nome')
            <div class="text-warning">{{ $message }}</div>
          @enderror
        </div>
        <div class="col-6">
          <label for="email" class="form-label">E-mail</label>
          <input type="email" class="form-control" name="email" id="email"
            value="{{ old('email') ?? ($user->email ?? null) }}" placeholder="E-mail">
          @error('email')
            <div class="text-warning">{{ $message }}</div>
          @enderror
        </div>

        @can('cliente')
          <input type="hidden" name="update_pessoa" value="1">
          <div class="col-6">
            <x-forms.input-field label="CPF" :value="old('cpf_cnpj') ?? ($user->pessoa->cpf_cnpj ?? null)" 
              name="cpf_cnpj" required='required' id="input-cpf"/>
            @error('cpf_cnpj') <div class="text-warning">{{ $message }}</div> @enderror
          </div>

          <div class="col-6">
            <x-forms.input-field :value="old('telefone') ?? ($user->pessoa->telefone ?? null)" name="telefone" class="telefone"
              label="Telefone" /> 
            @error('telefone') <div class="text-warning">{{ $message }}</div> @enderror
          </div>

          <div class="col-6">
            <x-forms.input-field :value="old('telefone_alt') ?? ($user->pessoa->telefone_alt ?? null)" name="telefone_alt" class="telefone"
              label="Telefone Alternativo" />
            @error('telefone_alt') <div class="text-warning">{{ $message }}</div> @enderror
          </div>

          <div class="col-6">
            <x-forms.input-field :value="old('celular') ?? ($user->pessoa->celular ?? null)" name="celular" class="telefone"
              label="Celular" />
            @error('celular') <div class="text-warning">{{ $message }}</div> @enderror
          </div>

          <h6 class="mb-0 mt-4">ENDEREÇO</h6>

          <div class="row gy-2">
            <x-painel.enderecos.form-endereco :endereco="$endereco ?? null" :nome="false" :padrao="false" :required="false" />
          </div>

        @endcan {{-- cliente --}}


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
        <div class="alert alert-light text-body bg-light alert-label-icon d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between" role="alert">
          <div>
        <i class="ri-user-line label-icon"></i>
        Usuário associado a pessoa: &nbsp; <strong>{{ $user->pessoa->nome_razao }}</strong>
          </div>
          <a href="{{"/painel/pessoa/insert/".$user->pessoa->uid}}" class="btn btn-sm btn-info mt-2 mt-md-0 ms-0 ms-md-3">EDITAR PESSOA</a>
        </div>
      </div>
      @endif
    @endcanany

  </div>
</div>
