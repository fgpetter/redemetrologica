<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-12 d-flex justify-content-end mb-3">
                <a href="#" class="btn btn-sm btn-success" data-bs-toggle="modal"
                    data-bs-target="#cursoshabilitadosModal">
                    <i class="ri-add-line align-bottom me-1"></i> Adicionar Curso Habilitado
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
                        <th scope="col" class=" d-sm-table-cell" style="width: 1%; white-space: nowrap;">Codigo
                        </th>
                        <th scope="col">Matricula</th>
                        <th scope="col">Nome</th>
                        <th scope="col">CPF/CNPJ</th>
                        <th scope="col">Data Cadastro</th>
                        <th scope="col"></th>
                    </tr>
                </thead>
                <tbody>
                    {{-- array de teste --}}
                    @php
                        $instrutores = [
                            [
                                'codigo' => '1001',
                                'matricula' => 'MAT123',
                                'nome' => 'João Silva',
                                'cpf_cnpj' => '123.456.789-00',
                                'data_cadastro' => '2024-02-12',
                            ],
                            [
                                'codigo' => '1002',
                                'matricula' => 'MAT456',
                                'nome' => 'Maria Santos',
                                'cpf_cnpj' => '987.654.321-00',
                                'data_cadastro' => '2024-02-12',
                            ],
                            [
                                'codigo' => '1003',
                                'matricula' => 'MAT789',
                                'nome' => 'Pedro Oliveira',
                                'cpf_cnpj' => '654.321.987-00',
                                'data_cadastro' => '2024-02-12',
                            ],
                        ];
                    @endphp

                    {{-- array de teste --}}
                    @forelse ($instrutores as $instrutor)
                        <tr>
                            <th>{{ $instrutor['codigo'] }}</th>
                            <td class="text-truncate" style="max-width: 50vw">{{ $instrutor['matricula'] }}</td>
                            <td class="text-truncate" style="max-width: 50vw">{{ $instrutor['nome'] }}</td>
                            <td class="text-truncate" style="max-width: 50vw">{{ $instrutor['cpf_cnpj'] }}</td>
                            <td class="text-truncate" style="max-width: 50vw">{{ $instrutor['data_cadastro'] }}</td>
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
                                                data-bs-target="#materialModal">Editar</a>
                                        </li>
                                        <li>
                                            <form method="POST"
                                                action="{{ route('materiais-padroes-delete', $instrutor['codigo']) }}">
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
                            <td colspan="4" class="text-center">Não há Instrutores cadastrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

        </div>
    </div>
</div>
