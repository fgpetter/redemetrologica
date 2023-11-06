  <div class="card">
    <div class="card-header">
      <h4 class="card-title mb-0">Pessoas</h4>
    </div><!-- end card header -->
    <div class="card-body">
    @if (session('update-success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('update-success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    <div class="table-responsive">
      <table class="table table-striped align-middle table-nowrap mb-0">
      <thead>
        <tr>
          <th scope="col">ID</th>
          <th scope="col">Nome</th>
          <th scope="col">CPF/CNPJ</th>
          <th scope="col">Data de cadastro</th>
          <th scope="col">Ações</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($users as $user)
          <tr>
            <th scope="row"><a href="{{ route('pessoa-edit', ['pessoa' => $pessoa['id']]) }}" class="fw-medium"> #{{$pessoa['id']}} </a></th>
            <td>{{$pessoa['name']}}</td>
            <td>Usuário</td>
            <td class="text-wrap">Painel | Editar | Incluir | Área do cliente | Usuários</td>
            <td>
              <a href="{{ route('pessoa-edit', ['pessoa' => $pessoa['id']]) }}" class="link-success">
                Ver Detalhes <i class="ri-arrow-right-line align-middle"></i>
              </a>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="text-center" > Não há pessoas na base. </td>
          </tr>
        @endforelse
      </tbody>
      </table>
    </div>

    </div>
  </div>
