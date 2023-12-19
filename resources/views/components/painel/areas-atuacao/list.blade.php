<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-12 d-flex justify-content-end mb-3">
                <a href="{{ route('area-atuacao-insert') }}" class="btn btn-sm btn-success">
                    <i class="ri-add-line align-bottom me-1"></i> Adicionar Área de Atuação
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
            <table class="table table-responsive table-striped align-middle table-nowrap mb-0">
                <thead>
                    <tr>
                        <th scope="col" class="d-none d-sm-table-cell" style="width: 1%; white-space: nowrap;">ID
                        </th>
                        <th scope="col">Descrição</th>
                        <th scope="col">Observações</th>
                        <th scope="col"></th>
                    </tr>
                </thead>
                <tbody>
                    {{-- @forelse ($areasAtuacao as $areaAtuacao) --}}
                    <tr>
                        <th scope="row" class="d-none d-sm-table-cell">
                            <a href="#" class="fw-medium">
                                uid
                            </a>
                        </th>
                        <td class="text-truncate" style="max-width: 50vw">descricao</td>
                        <td>observacoes</td>
                        <td>
                            <div class="dropdown">
                                <a href="#" role="button" id="dropdownMenuLink2" data-bs-toggle="dropdown"
                                    aria-expanded="false">
                                    <i class="ph-dots-three-outline-vertical" style="font-size: 1.5rem"
                                        data-bs-toggle="tooltip" data-bs-placement="top" title="Detalhes e edição"></i>
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink2">
                                    <li><a class="dropdown-item" href="#">Editar</a>
                                    </li>
                                    <li>
                                        <form method="POST" action="#">
                                            @csrf
                                            <button class="dropdown-item" type="submit">Excluir</button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">Não há áreas de atuação cadastradas.</td>
                        </tr>
                        {{-- @endforelse --}}
                    </tbody>
                </table>
                {{-- <div class="row mt-3">
                {!! $areasAtuacao->withQueryString()->links('pagination::bootstrap-5') !!}
            </div> --}}
            </div>
        </div>
    </div>
