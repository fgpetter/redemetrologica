@props([
    'instrutor' => [],
    'pessoas' => [],
    'cursoshabilitados' => [],
    'cursos' => [],
])

<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-12">
                <div class="hstack gap-2 flex-wrap mb-3 justify-content-end">
                    <a href="#" class="btn btn-sm btn-success" data-bs-toggle="modal"
                        data-bs-target="#cursoshabilitadosModal">
                        <i class="ri-add-line align-bottom me-1"></i> Adicionar Curso Habilitado
                    </a>
                </div>


            </div>
        </div>

        @if (session('error'))
            <x-alerts.alert type="error" />
        @endif
        @if (session('success'))
            <x-alerts.alert type="success" />
        @endif
        <div class="table-responsive" style="min-height: 25vh">
            <table class="table table-responsive table-striped align-middle table-nowrap mb-0">
                <thead>
                    <tr>
                        <th scope="col" class=" d-sm-table-cell" style="width: 1%; white-space: nowrap;">Id
                        </th>
                        <th scope="col">Descrição</th>
                        <th scope="col">Habilitado</th>

                        <th scope="col"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($cursoshabilitados as $cursohabilitado)
                        <tr>
                            <th>
                                <a href="#" data-bs-toggle="modal"
                                    data-bs-target="{{ '#cursoshabilitadosModal' . $cursohabilitado->uid }}">
                                    #{{ substr($cursohabilitado->uid, 7) }}
                                </a>
                            </th>
                            <td>{{ \Illuminate\Support\Str::limit($cursohabilitado->curso->descricao, 100) }}</td>
                            <td>
                                @if ($cursohabilitado->habilitado)
                                    <i class="ri-checkbox-circle-fill label-icon text-success fs-xl ms-2"></i>
                                @endif
                            </td>

                            <td>
                                <div class="dropdown">
                                    <a href="#" role="button" id="dropdownMenuLink2" data-bs-toggle="dropdown"
                                        aria-expanded="false">
                                        <i class="ph-dots-three-outline-vertical" style="font-size: 1.5rem"
                                            data-bs-toggle="tooltip" data-bs-placement="top"
                                            title="Detalhes e edição"></i>
                                    </a>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink2">
                                        <li>
                                            <a class="dropdown-item" href="#" data-bs-toggle="modal"
                                                data-bs-target="{{ '#cursoshabilitadosModal' . $cursohabilitado->uid }}">Editar</a>
                                        </li>
                                        <li>
                                            <x-painel.form-delete.delete route='instrutor-delete-curso-habilitado'
                                                id="{{ $cursohabilitado->uid }}" />
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        <x-painel.instrutores.modal-cursos-habilitados :curso="$cursohabilitado->curso" :instrutor="$instrutor"
                            :cursohabilitado="$cursohabilitado" />
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">Não há cursos cadastrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <x-painel.instrutores.modal-cursos-habilitados :cursos="$cursos" :instrutor="$instrutor" />

        </div>
    </div>
</div>
