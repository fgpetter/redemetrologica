  <div class="card">
      <div class="card-body">
          {{-- <div class="row">
              <div class="col-12 d-flex justify-content-end mb-3">
                  <a href="{{ route('funcionario-insert') }}" class="btn btn-sm btn-success">
                      <i class="ri-add-line align-bottom me-1"></i> Adicionar
                  </a>
              </div>
          </div> --}}



          <div class="table-responsive" style="min-height: 25vh">
              <table class="table table-responsive table-striped align-middle table-nowrap mb-0">
                  <thead>
                      <tr>
                          <th scope="col" class="d-none d-sm-table-cell" style="width: 5%; white-space: nowrap;">ID
                          </th>
                          <th scope="col">Nome</th>
                          <th scope="col">Cargo</th>
                          <th scope="col">Setor</th>
                          <th scope="col"></th>
                      </tr>
                  </thead>
                  <tbody>
                      @forelse ($funcionarios->where('pessoa' , '!=', null) as $funcionario)
                          <tr>
                              <th scope="row" class="d-none d-sm-table-cell">
                                  <a href="{{ route('funcionario-insert', ['funcionario' => $funcionario->uid]) }}"
                                      class="fw-medium">
                                      #{{ substr($funcionario->uid, 7) }}
                                  </a>
                              </th>
                              <td>{{ $funcionario->pessoa->nome_razao }}</td>
                              <td>{{ Str::ucfirst($funcionario->cargo) }}</td>
                              <td>{{ Str::ucfirst($funcionario->setor) }}</td>
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
                                                  href="{{ route('funcionario-insert', ['funcionario' => $funcionario->uid]) }}">Editar</a>
                                          </li>
                                          <li>
                                              <x-painel.form-delete.delete route='funcionario-delete'
                                                  id="{{ $funcionario->uid }}" />
                                          </li>
                                      </ul>
                                  </div>

                              </td>
                          </tr>
                      @empty
                          <tr>
                              <td colspan="5" class="text-center"> Não há funcionarios na base. </td>
                          </tr>
                      @endforelse
                  </tbody>
              </table>
          </div>

      </div>
  </div>
