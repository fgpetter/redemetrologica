<div>
    @if ($errors->any())
        <div class="alert alert-warning">
            <strong>Erro ao salvar os dados!</strong> <br><br>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="table-responsive" style="min-height: 180px">
        <table class="table table-responsive table-striped align-middle table-nowrap mb-0">
            <thead>
                <tr>
                    <th scope="col" style="width: 8%; white-space: nowrap;">
                        <a href="" wire:click.prevent="setSortBy('data_inscricao')">
                            Data Inscrição
                            @if($sortBy === 'data_inscricao')
                                @if($sortDirection === 'ASC')
                                    <i class="ri-arrow-up-s-line"></i>
                                @else
                                    <i class="ri-arrow-down-s-line"></i>
                                @endif
                            @endif
                        </a>
                    </th>
                    <th scope="col">
                        <a href="" wire:click.prevent="setSortBy('empresa')">
                            Empresa
                            @if($sortBy === 'empresa')
                                @if($sortDirection === 'ASC')
                                    <i class="ri-arrow-up-s-line"></i>
                                @else
                                    <i class="ri-arrow-down-s-line"></i>
                                @endif
                            @endif
                        </a>
                    </th>
                    <th scope="col">
                        <a href="" wire:click.prevent="setSortBy('nome')">
                            Nome
                            @if($sortBy === 'nome')
                                @if($sortDirection === 'ASC')
                                    <i class="ri-arrow-up-s-line"></i>
                                @else
                                    <i class="ri-arrow-down-s-line"></i>
                                @endif
                            @endif
                        </a>
                    </th>
                    <th scope="col" style="width: 5%; white-space: nowrap;">Valor</th>
                    <th scope="col" style="width: 5%; white-space: nowrap;"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($inscritos as $inscrito)
                    @if($inscrito->pessoa->tipo_pessoa === 'PF')
                        <tr wire:key="inscrito-{{ $inscrito->uid }}">
                            <td>{{ Carbon\Carbon::parse($inscrito->data_inscricao)->format('d/m/Y') }}</td>
                            <td class="text-truncate" style="max-width: 250px;">{{ $inscrito->empresa?->nome_razao ?? 'Individual' }}</td>
                            <td>{{ $inscrito->pessoa->nome_razao }}</td>
                            <td> {{ $inscrito->valor }} </td>
                            <td>
                                <div class="dropdown">
                                    <a href="#" role="button" id="dropdownMenuLink2" data-bs-toggle="dropdown"
                                        aria-expanded="false">
                                        <i class="ph-dots-three-outline-vertical" style="font-size: 1.5rem"
                                            data-bs-toggle="tooltip" data-bs-placement="top" title="Detalhes e edição"></i>
                                    </a>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink2">
                                        <li>
                                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="{{ '#inscritoModal' . $inscrito->uid }}">Editar</a>
                                        </li>
                                        @if(auth()->user()->email == 'fgpetter@gmail.com')
                                        <li>
                                            <a class="dropdown-item" href="{{ route('curso-visualizar-certificado', $inscrito->uid) }}">Certificado</a>
                                        </li>
                                        @endif
                                        <li>
                                            <x-painel.form-delete.delete route='cancela-inscricao' id="{{ $inscrito->uid }}" />
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        <x-painel.agendamento-cursos.modal-edita-participante :inscrito="$inscrito"/>
                    @endif
                @empty
                    <tr>
                        <td colspan="6" class="text-center">Este agendamento não possui inscritos.</td>
                    </tr>
                @endforelse
                @if($inscritos->sum('valor') > 0)
                <tfoot>
                    <tr>
                        <td colspan="3"></td>
                        <td><strong>Total:</strong> {{ $inscritos->sum('valor') }} </td>
                        <td></td>
                    </tr>
                </tfoot>
                @endif
            </tbody>
        </table>
    </div>
</div>
