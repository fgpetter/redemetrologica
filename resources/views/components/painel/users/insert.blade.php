<div class="card">
    @if (!isset($user))
        <div class="card-header">
            <h4 class="card-title mb-0">Inserir usuário</h4>
        </div>
    @endif
    <div class="card-body">
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

                <h6 class="mb-0 mt-4"> Alterar senha</h6>
                <div class="col-12">
                    <label for="password" class="form-label">Nova Senha</label>
                    <input type="password" class="form-control" name="password" id="password" placeholder="Senha">
                    @error('password') <div class="text-warning">{{ $message }}</div> @enderror
                </div>

                <div class="col-12">
                    <label for="password_confirmation" class="form-label">Confirmar Senha</label>
                    <input type="password" class="form-control" name="password_confirmation" id="password_confirmation" placeholder="Confirmar Senha">
                    @error('password_confirmation') <div class="text-warning">{{ $message }}</div> @enderror
                </div>

                @if($user->pessoa)
                <div class="col my-4 mx-2 bg-light py-3 rounded">
                    <strong>Usuário associado a pessoa: </strong>
                    <a href="{{"/painel/pessoa/insert/".$user->pessoa->uid}}">{{ $user->pessoa->nome_razao }}</a>
                </div>
                @endif

                <div class="col-12">
                    <button type="submit" class="btn btn-primary px-4">Salvar</button>
                </div>
            </div>
        </form>
        @isset($user)
            <x-painel.form-delete.delete route='user-delete' id="{{ $user->id }}" label="Usuário" />
        @endisset

    </div>

</div>
