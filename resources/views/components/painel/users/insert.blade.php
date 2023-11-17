@if (session('error')) <div class="alert alert-danger"> {{ session('error') }} </div> @endif
<div class="card">
  @if (!isset($user))
    <div class="card-header">
     <h4 class="card-title mb-0">Inserir usuário</h4> 
    </div>
  @endif
  <div class="card-body">
    <form method="POST" action="{{ isset($user) ? route('user-update', $user->id) : route('user-create') }}" >
      @csrf
      <div class="row gy-3">
        <div class="col-12">
          <label for="nome" class="form-label">Nome</label>
          <input type="text" class="form-control" name="nome" id="nome" value="{{ old('nome') ?? $user->name ?? null }}" placeholder="Nome" >
          @error('nome') <div class="text-warning">{{ $message }}</div> @enderror 
        </div>
        <div class="col-12">
          <label for="email" class="form-label">E-mail</label>
          <input type="email" class="form-control" name="email" id="email" value="{{ old('email') ?? $user->email ?? null }}" placeholder="E-mail" >
          @error('email') <div class="text-warning">{{ $message }}</div> @enderror
        </div>
        <div class="col-12">
        @isset($user)
          <label for="choices-single-default mb-0" class="form-label" style="margin-bottom: -0.5rem">Pessoa Associada</label>
          <select class="form-control" data-choices name="choices-single-default" id="choices-single-default">
              <option value="">Selecione uma pessoa</option>
              <option value="Choice 1">Choice 1</option>
              <option value="Choice 2">Choice 2</option>
              <option value="Choice 3">Choice 3</option>
          </select>
        @endisset
        </div>
        <div class="col-12">
          <button type="submit" class="btn btn-primary px-4">Salvar</button>
        </div>
      </div>
    </form>
    @isset($user)
      <form class="d-flex justify-content-end" style="margin-top: -1.5rem" method="POST" action="{{ route('user-delete', $user->id) }}">
        @csrf
        <button type="submit" class="btn btn-sm btn-danger">Remover usuário</button>
      </form>
    @endisset

  </div>
  
</div>