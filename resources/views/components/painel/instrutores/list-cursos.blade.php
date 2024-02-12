<div class="card">
    <div class="card-body">
        <div class="row">

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
                                'descricao' => 'Curso do instrutor 1',
                                'tipo' => 'Tipo 1',
                            ],
                            [
                                'uid' => '9876543210fedcba',
                                'descricao' => 'Curso do instrutor 2',
                                'tipo' => 'Tipo 2',
                            ],
                            [
                                'uid' => '0987654321abcdef',
                                'descricao' => 'Curso do instrutor 3',
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
                                                action="{{ route('materiais-padroes-delete', $material['uid']) }}">
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
                            <td colspan="4" class="text-center">Este Instrutor não possui cursos realizados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

        </div>
    </div>
</div>
