<div class="card">
  <div class="card-body">
    <div class="row">
      <div class="col-12 d-flex justify-content-end mb-3">
        <a href="#" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#bancoModal">
          <i class="ri-add-line align-bottom me-1"></i> Adicionar Banco
        </a>
      </div>
    </div>

    @if (session('banco-success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('banco-success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif
    @if (session('banco-error'))
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('banco-error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    <div class="table-responsive" style="min-height: 25vh">
      <table class="table table-responsive table-striped align-middle table-nowrap mb-0">
        <thead>
          <tr>
            <th scope="col" style="width: 5%">ID
            </th>
            <th scope="col">Numero do Banco</th>
            <th scope="col" style="width:40%">Nome do banco</th>
            <th scope="col" style="width: 5%"></th>
          </tr>
        </thead>
        <tbody>
          @forelse ($bancos as $banco)
            <tr>
              <th><a href="#" class="fw-medium" data-bs-toggle="modal"
                data-bs-target="{{ '#bancoModal'.$banco->uid }}">
                #{{substr($banco->uid, 7)}}
              </a></th>
              <td>{{ $banco->numero_banco }}</td>
              <td>{{ $banco->nome_banco }}</td>
              <td>
                <div class="dropdown">
                  <a href="#" role="button" id="dropdownMenuLink2"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ph-dots-three-outline-vertical" style="font-size: 1.5rem"
                      data-bs-toggle="tooltip" data-bs-placement="top"
                      title="Detalhes e edição"></i>
                  </a>
                  <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink2">
                    <li><a class="dropdown-item" href="#" data-bs-toggle="modal"
                        data-bs-target="{{ '#bancoModal'.$banco->uid }}">Editar</a>
                    </li>
                    <li>
                      <form method="POST"
                        action="{{ route('banco-delete', $banco['uid']) }}">
                        @csrf
                        <button class="dropdown-item" type="submit">Excluir</button>
                      </form>
                    </li>
                  </ul>
                </div>
              </td>
            </tr>
            <x-painel.bancos.modal-bancos :banco="$banco"/>
            
          @empty
            <tr>
              <td colspan="6" class="text-center">Não há bancos cadastrados.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
      {{-- <div class="row mt-3">
        {!! $bancos->withQueryString()->links('pagination::bootstrap-5') !!}
      </div> --}}
    </div>
    <x-painel.bancos.modal-bancos />
  
  </div>
</div>
