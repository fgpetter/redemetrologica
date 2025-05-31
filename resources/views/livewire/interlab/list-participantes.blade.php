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
        @forelse ($interlabempresasinscritas as $empresa)

            <thead class="bg-light">
                <tr>
                    <th scope="col" colspan="5"><strong>Empresa: </strong> &nbsp;
                        {{ $empresa->empresa->nome_razao }} - CNPJ: {{ $empresa->empresa->cpf_cnpj }}</th>
                </tr>
            </thead>
            @foreach ($intelabinscritos->where('empresa_id', $empresa->empresa_id) as $participante)
                <tr wire:key="participante-{{ $participante->id }}">
                    <td style="width: 1%; white-space: nowrap;">
                        <a data-bs-toggle="collapse" href="{{ '#collapse' . $participante->uid }}" role="button"
                            aria-expanded="false" aria-controls="collapseExample">
                            <i class="ri-file-text-line btn-ghost ps-2 pe-3 fs-5"></i>
                        </a>
                        <span
                            style="font-size: smaller;">{{ Carbon\Carbon::parse($participante->data_inscricao)->format('d/m/Y') }}</span>
                    </td>
                    <td>
                        <b>Laboratório: </b>{{ $participante->laboratorio->nome }}
                        <b><span
                                style="font-size: smaller;">({{ $participante->laboratorio->endereco->uf ?? 'N/A' }})</span></b>
                        &nbsp;&nbsp;
                        <b>RT: </b>{{ $participante->laboratorio->responsavel_tecnico }}
                    </td>
                    <!-- ====== Célula de VALOR ====== -->
                    <td>
                        @if ($editandoValor === $participante->id)
                            {{-- Se estivermos editando este participante, exibimos o <input> e botões --}}
                            apareceu
                            {{-- <input type="text" wire:model.defer="novoValor" class="form-control"
                                placeholder="Digite o valor" />

                            <button class="btn btn-success btn-sm mt-1"
                                wire:click="atualizarValorParticipante({{ $participante->id }})">
                                Salvar
                            </button>
                            <button class="btn btn-secondary btn-sm mt-1" wire:click="cancelarEdicao()">
                                Cancelar
                            </button> --}}
                        @else
                            {{-- Se não estivermos editando, mostramos o texto e um clique ativa o modo edição --}}
                            <b>Valor: </b>
                            <span wire:click="editarValorParticipante({{ $participante->id }})"
                                style="cursor: pointer; color: #0d6efd;" title="Clique para editar">
                                {{ $participante->valor !== null ? 'R$ ' . number_format($participante->valor, 2, ',', '.') : 'R$ 0,00' }}
                            </span>
                        @endif
                    </td>
                    <!-- ====== FIM da Célula de VALOR ====== -->
                    <td style="width: 1%; white-space: nowrap;">
                        <div class="dropdown">
                            <a href="#" role="button" id="dropdownMenuLink2" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <i class="ph-dots-three-outline-vertical" style="font-size: 1.5rem"
                                    data-bs-toggle="tooltip" data-bs-placement="top" title="Detalhes e edição"></i>
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink2">
                                <li>
                                    <a class="dropdown-item" href="#" data-bs-toggle="modal"
                                        data-bs-target="{{ '#participanteModal' . $participante->uid }}">Editar</a>
                                </li>
                                <li>
                                    <x-painel.form-delete.delete route='cancela-inscricao-interlab'
                                        id="{{ $participante->uid }}" />
                                </li>
                            </ul>
                        </div>
                    </td>

                </tr>
                <tr>
                    <td colspan="5" class="p-0">
                        <div class="collapse" id="{{ 'collapse' . $participante->uid }}">
                            <div class="row m-3 pe-2">
                                <div class="col-6 text-wrap">
                                    <b>Inscrito por:</b>
                                    {{ $participante->pessoa->nome_razao }} <br>
                                    <b>Informacoes:</b> {{ $participante->informacoes_inscricao }}
                                </div>
                                <div class="col-6 text-wrap">
                                    <b>Responsável técnico:</b> {{ $participante->laboratorio->responsavel_tecnico }}
                                    <br>
                                    <b>Telefone:</b> {{ $participante->laboratorio->telefone }} <b>Email:</b>
                                    {{ $participante->laboratorio->email }}<br>
                                    <b>Endereço:</b> {{ $participante->laboratorio->endereco?->endereco ?? 'N/A' }},
                                    {{ $participante->laboratorio->endereco->complemento ?? 'N/A' }}, Bairro:
                                    {{ $participante->laboratorio->endereco->bairro ?? 'N/A' }} <br>
                                    Cidade: {{ $participante->laboratorio->endereco->cidade ?? 'N/A' }} /
                                    {{ $participante->laboratorio->endereco->uf ?? 'N/A' }},
                                    CEP: {{ $participante->laboratorio->endereco->cep ?? 'N/A' }}
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                <x-painel.agenda-interlab.modal-inscritos :participante="$participante" :agendainterlab="$agendainterlab" />
            @endforeach
        @empty
            <tr>
                <td colspan="6" class="text-center">Este agendamento não possui inscritos.</td>
            </tr>
        @endforelse
        @if ($intelabinscritos->count() > 0)
            <tfoot>
                <tr>
                    <td colspan="2"><strong>Qtd Inscritos:</strong> {{ $intelabinscritos->count() }} </td>
                    </td>
                    <td><strong>Valor total:</strong> {{ $intelabinscritos->sum('valor') }} </td>
                    <td></td>
                </tr>
            </tfoot>
        @endif
    </table>

    <x-painel.agenda-interlab.modal-adicionar-inscrito :agendainterlab="$agendainterlab" :pessoas="$pessoas" />

</div>
