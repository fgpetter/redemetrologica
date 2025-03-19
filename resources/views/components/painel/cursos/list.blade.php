@php
    $ordem = request('ordem', 'desc') == 'desc' ? 'asc' : 'desc';
    $busca = request('busca', '');
@endphp
<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-12 d-flex justify-content-end mb-3">
                <a href="{{ route('curso-insert') }}" class="btn btn-sm btn-success">
                    <i class="ri-add-line align-bottom me-1"></i> Adicionar
                </a>
            </div>
        </div>



        <div class="table-responsive" style="min-height: 25vh">
            <!-- Cabeçalho de Busca -->
          <div>    
            
               <tr>
                   <th scope="col"></th>
                   <th scope="col">
                       <input type="text" class="form-control form-control-sm" 
                           placeholder="Buscar por descrição"
                           value="{{ $busca }}"
                           onkeypress="search(event, '{{ route('curso-index') }}', 'busca')">
                   </th>
                   <th scope="col"></th>
                   <th scope="col"></th>
                   <th scope="col"></th>
               </tr>
           
       </div>
            <table class="table table-responsive table-striped align-middle mb-0"
                style="table-layout: fixed">
                
                <!-- Cabeçalho da Tabela -->
                <thead>
                    <tr>
                        <th scope="col" class="d-none d-sm-table-cell" style="width: 10%; white-space: nowrap;">ID</th>
                        <th>
                            <a href="{{ route('curso-index', ['ordem' => $ordem, 'busca' => $busca]) }}">
                                {!! $ordem == 'asc' ? '<i class="ri-arrow-up-s-line"></i>' : '<i class="ri-arrow-down-s-line"></i>' !!}
                                Descrição
                            </a>
                        </th>
                        <th scope="col" style="width: 10%; white-space: nowrap;">Tipo</th>
                        <th scope="col" style="width: 10%; white-space: nowrap;">Carga Horária</th>
                        <th scope="col" style="width: 5%; white-space: nowrap;"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($cursos as $curso)
                        <tr>
                            <th scope="row" class="d-none d-sm-table-cell">
                                <a href="{{ route('curso-insert', ['curso' => $curso->uid]) }}" class="fw-medium">
                                    #{{ substr($curso->uid, 7) }}
                                </a>
                            </th>
                            <td>{{ $curso->descricao }}</td>
                            <td >{{ $curso->tipo_curso }}</td>
                            <td >{{ $curso->carga_horaria }}</td>
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
                                                href="{{ route('curso-insert', ['curso' => $curso->uid]) }}">Editar</a>
                                        </li>
                                        <li>
                                            <x-painel.form-delete.delete route="curso-delete"
                                                id="{{ $curso->uid }}" />
                                        </li>
                                    </ul>
                                </div>

                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center"> Não há cursos na base. </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="row mt-3">
                {!! $cursos->withQueryString()->links('pagination::bootstrap-5') !!}
            </div>
        </div>

    </div>
</div>
