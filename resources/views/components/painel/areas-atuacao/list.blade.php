<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-12 d-flex justify-content-end mb-3">
                <a href="#" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#areaAtuacaoModal">
                    <i class="ri-add-line align-bottom me-1"></i> Adicionar Área de Atuação
                </a>
            </div>
        </div>
        {{-- modal --}}
        <div class="modal fade" id="areaAtuacaoModal" tabindex="-1" aria-labelledby="areaAtuacaoModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="areaAtuacaoModalLabel">Adicionar Área de Atuação</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form method="POST" action="{{ route('area-atuacao-insert') }}">
                            @csrf
                            <div class="mb-3">
                                <label for="descricao" class="form-label">Descrição</label>
                                <input type="text" class="form-control" id="descricao" name="descricao">
                            </div>
                            <div class="mb-3">
                                <label for="observacoes" class="form-label">Observações</label>
                                <textarea class="form-control" id="observacoes" name="observacoes"></textarea>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                                <button type="submit" class="btn btn-primary">Salvar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        {{-- modal --}}
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
                        <th scope="col" class=" d-sm-table-cell" style="width: 1%; white-space: nowrap;">ID
                        </th>
                        <th scope="col">Descrição</th>
                        <th scope="col">Observações</th>
                        <th scope="col"></th>
                    </tr>
                </thead>
                <tbody>
                    {{-- array de teste --}}
                    @php
                        $areasAtuacao = [
                            [
                                'uid' => '1234567890abcdef',
                                'descricao' => 'Marketing Digital',
                                'observacoes' => 'Área responsável por estratégias de marketing online.',
                            ],
                            [
                                'uid' => '9876543210fedcba',
                                'descricao' => 'Design Gráfico',
                                'observacoes' => 'Criação de materiais gráficos para web e impressão.',
                            ],
                            [
                                'uid' => '0987654321abcdef',
                                'descricao' => 'Desenvolvimento Web',
                                'observacoes' => 'Desenvolvimento de websites e aplicações web.',
                            ],
                        ];
                    @endphp
                    {{-- array de teste --}}
                    @forelse ($areasAtuacao as $areaAtuacao)
                        <tr>
                            <th>{{ $areaAtuacao['uid'] }}</th>
                            <td class="text-truncate" style="max-width: 50vw">{{ $areaAtuacao['descricao'] }}</td>
                            <td>{{ Str::of($areaAtuacao['observacoes'])->limit(75) }}</td>
                            <td>
                                <div class="dropdown">
                                    <a href="#" role="button" id="dropdownMenuLink2" data-bs-toggle="dropdown"
                                        aria-expanded="false">
                                        <i class="ph-dots-three-outline-vertical" style="font-size: 1.5rem"
                                            data-bs-toggle="tooltip" data-bs-placement="top"
                                            title="Detalhes e edição"></i>
                                    </a>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink2">
                                        <li><a class="dropdown-item" href="#" data-bs-toggle="modal"
                                                data-bs-target="#areaAtuacaoModal">Editar</a>
                                        </li>
                                        <li>
                                            <form method="POST"
                                                action="{{ route('area-atuacao-delete', $areaAtuacao['uid']) }}">
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
                    @endforelse
                </tbody>
            </table>
            {{-- <div class="row mt-3">
                {!! $areasAtuacao->withQueryString()->links('pagination::bootstrap-5') !!}
            </div> --}}
        </div>
    </div>
</div>
