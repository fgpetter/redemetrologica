  <div class="card h-100">
    <div class="card-body">
      <div class="row">
        <div class="col-12 d-flex justify-content-end mb-3">
          <a href="{{ route('download-insert') }}" class="btn btn-sm btn-success">
            <i class="ri-add-line align-bottom me-1"></i> Adicionar Arquivo
          </a>
        </div>
      </div>

      <div class="table-responsive" style="min-height: 25vh">
        <table class="table table-striped align-middle table-nowrap mb-0">
          <thead>
            <tr>
              <th scope="col" style="width: 5%; white-space: nowrap;">ID</th>
              <th scope="col">Titulo</th>
              <th scope="col">Descriçao</th>
              <th scope="col">Categoria</th>
              <th scope="col" style="width: 5%; white-space: nowrap;">Data</th>
              <th scope="col" style="width: 5%; white-space: nowrap;">Baixar</th>
              <th scope="col" style="width: 5%; white-space: nowrap;"></th>
            </tr>
          </thead>
          <tbody>
            @forelse ($downloads as $download)
              <tr>
                <th scope="row"><a href="{{ route('download-insert', ['download' => $download->uid]) }}"
                    class="fw-medium"> #{{ $download['id'] }} </a></th>
                <td>{{ $download['titulo'] }}</td>
                <td>{{ $download['descricao'] }}</td>
                <td>{{ $download['categoria'] }}</td>
                <td>{{ Carbon\Carbon::parse($download->updated_at)->format('d/m/Y') }}</td>
                <td><a href="{{ asset('downloads/' . $download->arquivo) }}">
                    <i class="ph-file-arrow-down align-middle" style="font-size: 1.4rem"></i>
                  </a>
                </td>

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
                          href="{{ route('download-insert', ['download' => $download->uid]) }}">Editar</a>
                      </li>
                      <li>
                        <x-painel.form-delete.delete route="download-delete" id="{{ $download->uid }}" />
                      </li>
                    </ul>
                  </div>

                </td>
              </tr>
            @empty
              <tr>
                <td colspan="7" class="text-center"> Não há arquivos cadastrados. </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

    </div>
  </div>
