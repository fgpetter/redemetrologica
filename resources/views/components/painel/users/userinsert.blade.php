<div class="col-xl-6">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title mb-0">Inserir usuários</h4>
        </div><!-- end card header -->
        <div class="card-body">
          <form method="POST" action="{{ route('user-create') }}">
            @csrf
            <div class="row gy-3">
              <div class="col-12">
                <input type="text" class="form-control" name="nome" id="nome" placeholder="Nome" >
                @error('nome') <div class="text-warning">{{ $message }}</div> @enderror
              </div>
              <div class="col-12">
                <input type="email" class="form-control" name="email" id="email" placeholder="e-mail" >
                @error('email') <div class="text-warning">{{ $message }}</div> @enderror
              </div>
              <div class="col-12">
                <button type="submit" class="btn btn-primary">Salvar</button>
              </div>
            </div>
          </form>

        </div>
    </div>
</div>