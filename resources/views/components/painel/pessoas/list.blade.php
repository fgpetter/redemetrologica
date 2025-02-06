@php
  $order_name = 'ASC';
  if (isset($_GET['name']) && $_GET['name'] == 'ASC') {
    $order_name = 'DESC';
  }
  $order_doc = 'ASC';
  if (isset($_GET['doc']) && $_GET['doc'] == 'ASC') {
    $order_doc = 'DESC';
  }
  $order_data = 'ASC';
  if (isset($_GET['data']) && $_GET['data'] == 'ASC') {
    $order_data = 'DESC';
  }
  if (isset($_GET['buscanome'])) {
    $busca_nome = $_GET['buscanome'];
  }
  if (isset($_GET['buscadoc'])) {
    $busca_doc = $_GET['buscadoc'];
  }
@endphp

<div class="card">
  <div class="card-body">
    <div class="row">
      <div class="col-12 d-flex justify-content-end mb-3">
        <a href="{{ route('pessoa-insert') }}" class="btn btn-sm btn-success">
          <i class="ri-add-line align-bottom me-1"></i> Adicionar
        </a>
      </div>
    </div>

    <div class="table-responsive" style="min-height: 25vh">
      <table class="table table-responsive table-striped align-middle table-nowrap mb-0">
        <thead>
          <tr>
            <th scope="col"></th>
            <th scope="col">
              <input type="text" class="form-control form-control-sm"
                onkeypress="search(event, window.location.href, 'buscanome')"
                placeholder="Buscar por nome" value="{{ $busca_nome ?? null }}">
            </th>
            <th scope="col">
              <input type="text" class="form-control form-control-sm"
                onkeypress="search(event, window.location.href, 'buscadoc')"
                placeholder="Buscar por documento" value="{{ $busca_doc ?? null }}">

            </th>
            <th scope="col"></th>
            <th scope="col"></th>
          </tr>
        </thead>

        <thead>
          <tr>
            <th scope="col" class="d-none d-sm-table-cell" style="width: 5%; white-space: nowrap;">ID</th>
            <th scope="col">
              <a href="{{ route('pessoa-index', ['name' => $order_name]) }}">
                {!! $order_name == 'ASC' ? '<i class="ri-arrow-up-s-line"></i>' : '<i class="ri-arrow-down-s-line"></i>' !!}
                &nbsp Nome
              </a>
            </th>
            <th scope="col">
              <a href="{{ route('pessoa-index', ['doc' => $order_doc]) }}">
                {!! $order_doc == 'ASC' ? '<i class="ri-arrow-up-s-line"></i>' : '<i class="ri-arrow-down-s-line"></i>' !!}
                &nbsp CPF/CNPJ
              </a>
            </th>
            <th scope="col" class="text-wrap">
              <a href="{{ route('pessoa-index', ['data' => $order_data]) }}">
                {!! $order_data == 'ASC' ? '<i class="ri-arrow-up-s-line"></i>' : '<i class="ri-arrow-down-s-line"></i>' !!}
                &nbsp Data de cadastro
              </a>
            </th>
            <th scope="col" style="width: 5%; white-space: nowrap;"></th>
          </tr>
        </thead>
        <tbody>
          @forelse ($pessoas as $pessoa)
            <tr>
              <th scope="row" class="d-none d-sm-table-cell">
                <a href="{{ route('pessoa-insert', ['pessoa' => $pessoa->uid]) }}" class="fw-medium">
                  #{{ substr($pessoa->uid, 7) }}
                </a>
              </th>
              <td class="text-truncate"> {{ $pessoa->nome_razao }} </td>
              <td><input type="text" class="form-control-plaintext table-cpf-cnpj"
                  style="min-width: 135px" value="{{ $pessoa->cpf_cnpj }}" readonly></td>
              <td>{{ ($pessoa->created_at) ? $pessoa->created_at->format('d/m/Y') : '-' }}</td>
              <td>
                <div class="dropdown">
                  <a href="#" role="button" id="dropdownMenuLink1" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    <i class="ph-dots-three-outline-vertical" style="font-size: 1.5rem"
                      data-bs-toggle="tooltip" data-bs-placement="top"
                      title="Detalhes e edição"></i>
                  </a>
                  <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink1">
                    <li><a class="dropdown-item"
                        href="{{ route('pessoa-insert', ['pessoa' => $pessoa->uid]) }}">Editar</a>
                    </li>
                    <li>
                      <x-painel.form-delete.delete route='pessoa-delete'
                        id="{{ $pessoa->uid }}" />
                    </li>
                  </ul>
                </div>

              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="text-center"> Não há pessoas na base. </td>
            </tr>
          @endforelse
        </tbody>
      </table>
      <div class="row mt-3 w-100">
        {!! $pessoas->withQueryString()->links('pagination::bootstrap-5') !!}
      </div>
    </div>

  </div>
</div>
