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

    <div class="table-responsive" style="min-height: 180px" x-data="{ editandoId: null }">
        <table class="table table-responsive table-striped align-middle table-nowrap mb-0">
            @forelse ($interlabempresasinscritas as $empresa)

                <thead class="bg-light">
                    <tr>
                        <th scope="col" colspan="5"><strong>Empresa: </strong> &nbsp;
                            {{ $empresa->nome_razao }} - CNPJ: {{ $empresa->cpf_cnpj }}</th>
                    </tr>
                </thead>

                @foreach ($intelabinscritos->where('empresa_id', $empresa->id) as $participante)
                    <tr wire:key="participante-{{ $participante->id }}-{{ $participante->valor }}">

                        <td style="width: 1%; white-space: nowrap;">
                            <a data-bs-toggle="collapse" href="{{ '#collapse' . $participante->uid }}" role="button"
                                aria-expanded="false" aria-controls="collapseExample">
                                <i class="ri-file-text-line btn-ghost ps-2 pe-3 fs-5"></i>
                            </a>
                            <span style="font-size: smaller;">
                                {{ $participante->data_inscricao->format('d/m/Y') }}
                            </span>
                        </td>
                        <td>
                            <div class="d-flex flex-wrap align-items-center">
                                <div class="me-3">
                                    <b>Laboratório: </b>{{ $participante->laboratorio->nome }}
                                </div>
                                <div class="flex-grow-1 ">
                                    <b>Inscrito por:</b>
                                    {{ $participante->pessoa->nome_razao }} - {{ $participante->pessoa->email }}
                                </div>
                            </div>
                        </td>

                        {{-- ====== Célula de VALOR (otimizada p/ atualização instantânea) ====== --}}
                        <td class="text-start" style="width: 200px; white-space: nowrap;" 
                            x-data="{
                                valorLocal: {{ $participante->valor ?? 0 }},
                                participanteId: {{ $participante->id }},
                                formatarValor(valor) {
                                    if (valor === 0) {
                                        return 'Sem valor';
                                    } else {
                                        let numero = Number(valor).toFixed(2);
                                        return 'R$ ' + numero.replace('.', ',');
                                    }
                                },
                                atualizarModal() {
                                    const novoValorFormatado = this.formatarValor(this.valorLocal);
                                    const modalValorInput = document.getElementById('valor-{{ $participante->uid }}');
                                    if (modalValorInput) {
                                        modalValorInput.value = novoValorFormatado;
                                    }
                                }
                            }"
                        >
                            <template x-if="editandoId !== {{ $participante->id }}">
                                <span @click="editandoId = {{ $participante->id }}"
                                    style="cursor: pointer; color: #000;">
                                    <b>Valor:</b> <span x-text="formatarValor(valorLocal)"></span>
                                </span>
                            </template>
                            <template x-if="editandoId === {{ $participante->id }}">
                                <div class="d-flex align-items-center">
                                    <input type="number" x-model.number="valorLocal"
                                        class="form-control form-control-sm" style="width: 100px;"
                                        @keydown.enter.prevent="
                                        $wire.call('atualizarValor', participanteId, valorLocal);
                                        editandoId = null;
                                        atualizarModal();
                                    ">
                                    <button class="btn btn-success btn-sm ms-1"
                                        @click="
                                        $wire.call('atualizarValor', participanteId, valorLocal);
                                        editandoId = null;
                                        atualizarModal();
                                    ">
                                        ✔
                                    </button>
                                    <button class="btn btn-warning btn-sm ms-1"
                                        @click="
                                        valorLocal = {{ $participante->valor ?? 0 }};
                                        editandoId = null;
                                    ">
                                        ✖
                                    </button>
                                </div>
                            </template>
                        </td>
                        {{-- ====== FIM da Célula de VALOR ====== --}}


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
                                            data-bs-target="{{ '#participanteModal' . $participante->uid }}">
                                            Editar
                                        </a>
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
                                        <b>Informações:</b> {{ $participante->informacoes_inscricao }}
                                    </div>
                                    <div class="col-6 text-wrap">
                                        <b>Responsável técnico:</b>
                                        {{ $participante->laboratorio->responsavel_tecnico }}
                                        <br>
                                        <b>Telefone:</b> {{ $participante->laboratorio->telefone }} <b>Email:</b>
                                        {{ $participante->laboratorio->email }}<br>
                                        <b>Endereço:</b>
                                        {{ $participante->laboratorio->endereco?->endereco ?? 'N/A' }},
                                        {{ $participante->laboratorio->endereco->complemento ?? 'N/A' }},
                                        Bairro: {{ $participante->laboratorio->endereco->bairro ?? 'N/A' }} <br>
                                        Cidade: {{ $participante->laboratorio->endereco->cidade ?? 'N/A' }}
                                        / {{ $participante->laboratorio->endereco->uf ?? 'N/A' }}, CEP:
                                        {{ $participante->laboratorio->endereco->cep ?? 'N/A' }}
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
                        <td colspan="2"><strong>Qtd Inscritos:</strong> {{ $intelabinscritos->count() }}</td>
                        <td>
                            <strong>Valor total:</strong>
                            R$ {{ number_format($intelabinscritos->sum('valor'), 2, ',', '.') }}
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            @endif
        </table>

        {{-- Botão/modal de adicionar novo inscrito --}}
        <x-painel.agenda-interlab.modal-adicionar-inscrito :agendainterlab="$agendainterlab" :pessoas="$pessoas" />

    </div>
</div>
