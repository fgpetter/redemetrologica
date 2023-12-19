<div class="card">
  @if (!isset($user))
    <div class="card-header">
     <h4 class="card-title mb-0">Inserir usuário</h4> 
    </div>
  @endif
  <div class="card-body">
    <form method="POST" action="{{ route('user-create') }}" >
      @csrf
      <div class="row d-flex align-items-end">
        <div class="col-sm-5">
          <label for="nome" class="form-label">Nome</label>
          <input type="text" class="form-control" name="nome" id="nome" value="{{ old('nome') ?? null }}" >
          @error('nome') <div class="text-warning">{{ $message }}</div> @enderror 
        </div>
        <div class="col-sm-5">
          <label for="email" class="form-label">E-mail</label>
          <input type="email" class="form-control" name="email" id="email" value="{{ old('email') ?? null }}" >
          @error('email') <div class="text-warning">{{ $message }}</div> @enderror
        </div>
        <div class="col-sm-2">
          <button type="submit" class="btn btn-primary px-4">Salvar</button>
        </div>
      </div>
    </form>
  </div>
  
</div>