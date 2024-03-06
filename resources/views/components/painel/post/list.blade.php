  <div class="card h-100">
      <div class="card-body">
          <div class="row">
              <div class="col-12 d-flex justify-content-end mb-3">
                  <a href="{{ route($tipo . '-insert') }}" class="btn btn-sm btn-success">
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

          <div class="table-responsive" style="min-height: 25vh">
              <table class="table table-striped align-middle table-nowrap mb-0">
                  <thead>
                      <tr>
                          <th scope="col">ID</th>
                          <th scope="col">Titulo</th>
                          <th scope="col">Tipo</th>
                          <th scope="col">Data Publicação</th>
                          <th scope="col">Rascunho</th>
                          <th scope="col">Visualizar</th>

                          <th scope="col"></th>
                      </tr>
                  </thead>
                  <tbody>
                      @forelse ($posts as $post)
                          <tr>
                              <th scope="row"><a href="{{ route($tipo . '-insert', ['post' => $post->slug]) }}"
                                      class="fw-medium"> #{{ $post['id'] }} </a></th>
                              <td>{{ $post['titulo'] }}</td>
                              <td>{{ Str::ucfirst($post['tipo']) }}</td>
                              <td>{{ Carbon\Carbon::parse($post->data_publicacao)->format('d/m/Y') }}</td>
                              <td>
                                  <input type="checkbox" {{ $post['rascunho'] ? 'checked' : '' }} disabled>
                              </td>
                              <td>
                                  @if ($post->tipo == 'galeria')
                                      <a href="{{ route('galeria-show', ['slug' => $post->slug]) }}" target="_blank"
                                          rel="noopener noreferrer">Visualizar</a>
                                  @else
                                      <a href="{{ route('noticia-show', ['slug' => $post->slug]) }}" target="_blank"
                                          rel="noopener noreferrer">Visualizar</a>
                                  @endif
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
                                                  href="{{ route($tipo . '-insert', ['post' => $post->slug]) }}">Editar</a>
                                          </li>
                                          <li>
                                              <x-painel.form-delete.delete route="post-delete"
                                                  id="{{ $post->id }}" />
                                          </li>
                                      </ul>
                                  </div>

                              </td>
                          </tr>
                      @empty
                          <tr>
                              <td colspan="7" class="text-center"> Não há Posts na base. </td>
                          </tr>
                      @endforelse
                  </tbody>
              </table>
          </div>

      </div>
  </div>
