<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-12 d-flex justify-content-end mb-3">
                <a href="#" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#materialModal">
                    <i class="ri-add-line align-bottom me-1"></i> Adicionar Material
                </a>
            </div>
        </div>
        {{-- modal --}}
        <div class="modal fade" id="materialModal" tabindex="-1" aria-labelledby="materialModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="materialModalLabel">Adicionar Material</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form method="POST" action="{{ route('lista-materiais-padroes-insert') }}">
                            @csrf
                            <div class="mb-3">
                                <label for="descricao" class="form-label">Descrição</label>
                                <input type="text" class="form-control" id="descricao" name="descricao">
                            </div>
                            <div class="mb-3">
                                <label for="cod_fabricante" class="form-label">Código de Fabricante</label>
                                <input type="text" class="form-control" id="cod_fabricante" name="cod_fabricante">
                            </div>
                            <div class="mb-3">
                                <label for="fabricante" class="form-label">Fabricante</label>
                                <input type="text" class="form-control" id="fabricante" name="fabricante">
                            </div>
                            <div class="mb-3">
                                <label for="marca" class="form-label">Marca</label>
                                <input type="text" class="form-control" id="marca" name="marca">
                            </div>
                            <div class="mb-3">
                                <label for="tipo_material" class="form-label">Tipo de Material</label>
                                <select class="form-control" id="tipo_material" name="tipo_material">
                                    <option value="cursos">Cursos</option>
                                    <option value="interlab">Interlab</option>
                                    <option value="ambos">Ambos</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="padrao" class="form-label">Padrão</label>
                                <input type="checkbox" class="form-check-input" id="padrao" name="padrao">
                            </div>
                            <div class="mb-3">
                                <label for="tipo_despesa" class="form-label">Tipo de Despesa</label>
                                <select class="form-control" id="tipo_despesa" name="tipo_despesa">
                                    <option value="fixo">Fixo</option>
                                    <option value="variavel">Variável</option>
                                    <option value="outros">Outros</option>
                                </select>
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
                        <th scope="col"></th>
                    </tr>
                </thead>
                <tbody>
                    {{-- array de teste --}}
                    @php
                        $materiais = [
                            [
                                'uid' => '1234567890abcdef',
                                'descricao' => 'Material 1',
                                'tipo' => 'Tipo 1',
                            ],
                            [
                                'uid' => '9876543210fedcba',
                                'descricao' => 'Material 2',
                                'tipo' => 'Tipo 2',
                            ],
                            [
                                'uid' => '0987654321abcdef',
                                'descricao' => 'Material 3',
                                'tipo' => 'Tipo 3',
                            ],
                        ];
                    @endphp
                    {{-- array de teste --}}
                    @forelse ($materiais as $material)
                        <tr>
                            <th>{{ $material['uid'] }}</th>
                            <td class="text-truncate" style="max-width: 50vw">{{ $material['descricao'] }}</td>
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
                                                data-bs-target="#materialModal">Editar</a>
                                        </li>
                                        <li>
                                            <form method="POST"
                                                action="{{ route('lista-materiais-padroes-delete', $material['uid']) }}">
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
                            <td colspan="4" class="text-center">Não há materiais cadastrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            {{-- <div class="row mt-3">
                {!! $materiais->withQueryString()->links('pagination::bootstrap-5') !!}
            </div> --}}
        </div>
    </div>
</div>
