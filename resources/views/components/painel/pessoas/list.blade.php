  <div class="card">
    <div class="card-body">
      <div class="row">
        <div class="col-12 d-flex justify-content-end mb-3">
          <a href="{{ route('pessoa-insert') }}" class="btn btn-sm btn-success" > 
            <i class="ri-add-line align-bottom me-1"></i> Adicionar 
          </a>
        </div>
      </div>

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
            <th scope="col"></th>
          </tr>
        </thead>
        <tbody>
          @forelse ($pessoas as $pessoa)
            <tr>
              <th scope="row"><a href="{{ route('pessoa-insert', ['pessoa' => $pessoa['id']]) }}" class="fw-medium"> #{{$pessoa['id']}} </a></th>
              <td>{{$pessoa['nome_razao']}}</td>
              <td><input type="text" class="form-control-plaintext table-cpf-cnpj" value="{{$pessoa['cpf_cnpj']}}" readonly></td>
              <td>{{$pessoa['created_at']}}</td>
              <td>
                <div class="dropdown">
                  <a href="#" role="button" id="dropdownMenuLink1" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ph-dots-three-outline-vertical" style="font-size: 1.5rem" 
                      data-bs-toggle="tooltip" data-bs-placement="top" title="Detalhes e edição"></i>
                  </a>
                  <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink1">
                    <li><a class="dropdown-item" href="{{route('pessoa-insert', ['pessoa' => $pessoa->id])}}">Editar</a></li>
                    <li>
                      <form method="POST" action="{{ route('pessoa-delete', $pessoa->id) }}">
                        @csrf
                        <button class="dropdown-item" type="submit">Deletar</button>
                      </form>
                    </li>
                  </ul>
                </div>
  
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