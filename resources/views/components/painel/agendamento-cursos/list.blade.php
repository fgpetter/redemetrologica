<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-12 d-flex justify-content-end mb-3">
                <a href="{{ route('agendamentoCurso-insert') }}" class="btn btn-sm btn-success">
                    <i class="ri-add-line align-bottom me-1"></i> Adicionar Agendamento de curso
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
                        <th scope="col" class=" d-sm-table-cell" style="width: 1%; white-space: nowrap;">None do
                            Curso
                        </th>
                        <th scope="col">Data de Inicio</th>
                        <th scope="col">Nome Local</th>
                        <th scope="col">Carga Horaria</th>
                        <th scope="col">Certificado</th>
                        <th scope="col"></th>
                    </tr>
                </thead>
                <tbody>
                    {{-- array de teste --}}
                    @php
                        $agendamento_cursos = [
                            [
                                'nome_curso' => 'Curso de Programação',
                                'data_inicio' => '2024-02-15',
                                'nome_local' => 'Centro de Treinamento ABC',
                                'carga_horaria' => '40 horas',
                                'certificado' => 'sim',
                            ],
                            [
                                'nome_curso' => 'Curso de Design Gráfico',
                                'data_inicio' => '2024-03-10',
                                'nome_local' => 'Escola de Arte XYZ',
                                'carga_horaria' => '30 horas',
                                'certificado' => 'sim',
                            ],
                            [
                                'nome_curso' => 'Curso de Marketing Digital',
                                'data_inicio' => '2024-04-05',
                                'nome_local' => 'Agência de Publicidade DEF',
                                'carga_horaria' => '20 horas',
                                'certificado' => 'não',
                            ],
                        ];
                    @endphp

                    {{-- array de teste --}}
                    @forelse ($agendamento_cursos
                        as $agendamento_curso)
                        <tr>
                            <th>{{ $agendamento_curso['nome_curso'] }}</th>
                            <td class="text-truncate" style="max-width: 50vw">{{ $agendamento_curso['data_inicio'] }}
                            </td>
                            <td class="text-truncate" style="max-width: 50vw">{{ $agendamento_curso['nome_local'] }}
                            </td>
                            <td class="text-truncate" style="max-width: 50vw">{{ $agendamento_curso['carga_horaria'] }}
                            </td>
                            <td class="text-truncate" style="max-width: 50vw">{{ $agendamento_curso['certificado'] }}
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
                                        <li><a class="dropdown-item" href="#" data-bs-toggle="modal"
                                                data-bs-target="#materialModal">Editar</a>
                                        </li>
                                        <li>
                                            <form method="POST"
                                                action="{{ route('lista-materiais-padroes-delete', $agendamento_curso['nome_curso']) }}">
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
                            <td colspan="4" class="text-center">Não há Agendamento de Cursos</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

        </div>
    </div>
</div>
