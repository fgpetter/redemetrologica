 
 @php
    $order_name = 'ASC';
    if (request('name') == 'ASC') $order_name = 'DESC';
    
    $order_doc = 'ASC';
    if (request('doc') == 'ASC') $order_doc = 'DESC';
    
    $busca_nome = request('buscanome', '');
    $busca_doc = request('buscadoc', '');
    
@endphp
 <div class="card">
      <div class="card-body">

        {{-- adiciona fornecedor --}}
        <div class="row">
          <div class="col-12">
            <div class="hstack gap-2 flex-wrap mb-3 justify-content-end">
              <button class="btn btn-sm btn-success" type="button" data-bs-toggle="collapse" 
                data-bs-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample">
                <i class="ri-add-line align-bottom me-1"></i> Adicionar Fornecedor
              </button>
            </div>
  
            <div class="collapse" id="collapseExample">
              <div class="card mb-3 shadow-none">
                  <div class="card-body">
                    <form action="{{route('fornecedor-create')}}" method="POST">
                      @csrf
                      <div class="row">
                        <div class="col-10">
                          <select class="form-control" data-choices name="pessoa_uid" id="choices-single-default">
                            <option value="">Selecione na lista</option>
                            @foreach($pessoas as $pessoa)
                              <option value="{{ $pessoa->uid }}">{{ $pessoa->cpf_cnpj }} | {{ $pessoa->nome_razao }}</option>
                            @endforeach
                          </select>
                          @error('pessoa_uid')<div class="text-warning">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-2">
                          <button class="btn btn-success" type="submit">Adicionar</button>
                        </div>
                      </div>
                    </form>
                    <p>Caso a pessoa não esteja cadastrada ainda, <a href="{{ route('pessoa-insert') }}">Clique Aqui</a></p>
                  </div>
              </div>
            </div>
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
                                placeholder="Buscar por nome" value="{{ $busca_nome }}">
                        </th>
                        <th scope="col">
                            <input type="text" class="form-control form-control-sm"
                                onkeypress="search(event, window.location.href, 'buscadoc')"
                                placeholder="Buscar por documento" value="{{ $busca_doc }}">
                        </th>
                        <th scope="col"></th>
                    </tr>
                </thead>  
               <thead>
                    <tr>
                        <th scope="col" class="d-none d-sm-table-cell" style="width: 5%; white-space: nowrap;">ID</th>
                        <th scope="col">
                            <a href="{{ route('fornecedor-index', [
                                'name' => $order_name,
                                'doc' => request('doc'),
                                'buscanome' => $busca_nome,
                                'buscadoc' => $busca_doc
                            ]) }}">
                                {!! $order_name == 'ASC' ? '<i class="ri-arrow-up-s-line"></i>' : '<i class="ri-arrow-down-s-line"></i>' !!}
                                Razão Social
                            </a>
                        </th>
                        <th scope="col">
                            <a href="{{ route('fornecedor-index', [
                                'doc' => $order_doc,
                                'name' => request('name'),
                                'buscanome' => $busca_nome,
                                'buscadoc' => $busca_doc
                            ]) }}">
                                {!! $order_doc == 'ASC' ? '<i class="ri-arrow-up-s-line"></i>' : '<i class="ri-arrow-down-s-line"></i>' !!}
                                CNPJ
                            </a>
                        </th>
                        <th scope="col"></th>
                    </tr>
                </thead>
                  <tbody>
                      @forelse ($fornecedores->where('pessoa' , '!=', null) as $fornecedor)
                          <tr>
                              <th scope="row" class="d-none d-sm-table-cell">
                                  <a href="{{ route('fornecedor-insert', ['fornecedor' => $fornecedor->uid]) }}"
                                      class="fw-medium">
                                      #{{ substr($fornecedor->uid, 7) }}
                                  </a>
                              </th>
                              <td>{{ $fornecedor->pessoa->nome_razao }}</td>
                              <td>{{ $fornecedor->pessoa->cpf_cnpj }}</td>
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
                                                  href="{{ route('fornecedor-insert', ['fornecedor' => $fornecedor->uid]) }}">Editar</a>
                                          </li>
                                          <li>
                                              <x-painel.form-delete.delete route='fornecedor-delete'
                                                  id="{{ $fornecedor->uid }}" />
                                          </li>
                                      </ul>
                                  </div>

                              </td>
                          </tr>
                      @empty
                          <tr>
                              <td colspan="5" class="text-center"> Não há fornecedores na base. </td>
                          </tr>
                      @endforelse
                  </tbody>
              </table>
          </div>

      </div>
  </div>
