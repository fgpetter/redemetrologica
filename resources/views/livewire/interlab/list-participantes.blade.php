
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
                            {{ $empresa->nome_razao }} - CNPJ: {{ $empresa->cpf_cnpj }}
                            @if($empresa->associado) - <span class="text-primary">Associado</span> @endif
                        </th>
                    </tr>
                </thead>

                @foreach ($inscritosPorEmpresa[$empresa->id] ?? [] as $participante)
                    <tr wire:key="participante-{{ $participante->id }}-{{ $participante->valor }}">

                        <td style="width: 1%; white-space: nowrap;">
                            <a data-bs-toggle="collapse" href="{{ '#collapse' . $participante->uid }}" role="button"
                                aria-expanded="false" aria-controls="collapseExample">
                                    <i class="ri-file-text-line btn-ghost ps-2 pe-3 fs-5"
                                       style="color: {{ $participante->certificado_emitido !== null ? '#28a745' : '#0d6efd' }};"></i>
                            </a>
                            <span style="font-size: smaller;">
                                {{ $participante->data_inscricao->format('d/m/Y') }}
                            </span>
                        </td>
                        <td>
                            <div class="d-flex flex-wrap align-items-center">
                                <div class="me-3">
                                    <b>Laboratório: </b>{{ $participante->laboratorio?->nome ?? '—' }}
                                </div>
                                <div class="flex-grow-1 ">
                                    <b>Inscrito por:</b>
                                    {{ $participante->pessoa->nome_razao }} - {{ $participante->pessoa->email }}
                                </div>
                            </div>
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
                                    ">
                                    <button class="btn btn-success btn-sm ms-1"
                                        @click="
                                        $wire.call('atualizarValor', participanteId, valorLocal);
                                        editandoId = null;
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
                                        <a class="dropdown-item" href="#"
                                            wire:click.prevent="$dispatch('abrir-editar-inscrito', { id: {{ $participante->id }} })">
                                            Editar
                                        </a>
                                    </li>

                                    <!-- Botão para baixar carta-senha -->
                                    @if($agendainterlab->status <> 'AGENDADO' && isset($tagsSenhaDoc[$participante->id]))
                                        <li>
                                            <a class="dropdown-item" href="{{ route('dados-doc.download', ['link' => $tagsSenhaDoc[$participante->id]->link]) }}" target="_blank">
                                                Baixar Carta Senha
                                            </a>
                                        </li>
                                    @endif
                                    <!-- Botão para gerar certificado -->
                                    <li>
                                        <button type="button" class="dropdown-item"
                                            wire:click="confirmarEnvioCertificado({{ $participante->id }}, @js($participante->email))">
                                            Gerar Certificado
                                        </button>
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
                                        <b>Informações:</b> {{ $participante->informacoes_inscricao }}
                                        <br>
                                        @if($participante->analistas->isNotEmpty())
                                            <b>Analistas inscritos:</b> <br>
                                            @foreach($participante->analistas as $analista)
                                                {{ $analista->nome }} - {{ $analista->email }} - {{ $analista->telefone }} <br>
                                            @endforeach
                                        @endif


                                    </div>
                                    <div class="col-6 text-wrap">
                                        <b>Responsável técnico:</b>
                                        {{ $participante->responsavel_tecnico }}
                                        <br>
                                        <b>Telefone:</b> {{ $participante->telefone }} <b>Email:</b>
                                        {{ $participante->email }}<br>
                                        <b>Endereço:</b>
                                        {{ $participante->laboratorio?->endereco?->endereco ?? 'N/A' }},
                                        {{ $participante->laboratorio?->endereco?->complemento ?? 'N/A' }},
                                        Bairro: {{ $participante->laboratorio?->endereco?->bairro ?? 'N/A' }} <br>
                                        Cidade: {{ $participante->laboratorio?->endereco?->cidade ?? 'N/A' }}
                                        / {{ $participante->laboratorio?->endereco?->uf ?? 'N/A' }}, CEP:
                                        {{ $participante->laboratorio?->endereco?->cep ?? 'N/A' }}
                                        <br>
                                        <b>Certificado:</b>
                                        @if($participante->certificado_emitido)
                                            <span class="text-success">Enviado em {{ \Carbon\Carbon::parse($participante->certificado_emitido)->format('d/m/Y') }}</span>
                                        @else
                                            <span class="text-muted">Não enviado</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
           
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
        <livewire:interlab.editar-inscrito />

    </div>

    @if ($showCertificadoModal)
        @teleport('body')
            <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5); z-index: 1060;">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content text-start">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="ri-mail-send-line me-2"></i>Confirmar Email para Envio do Certificado
                            </h5>
                            <button type="button" class="btn-close" wire:click="fecharCertificadoModal"></button>
                        </div>
                        <div class="modal-body">
                            <p class="text-muted mb-3">
                                O certificado será enviado para o email abaixo. Você pode confirmar ou alterar o endereço antes de enviar.
                            </p>
                            <div class="mb-3 text-start">
                                <label for="certificadoEmail" class="form-label fw-semibold">Email de Destino</label>
                                <input type="email"
                                    class="form-control @error('certificadoEmail') is-invalid @enderror"
                                    id="certificadoEmail"
                                    wire:model="certificadoEmail"
                                    placeholder="exemplo@email.com">
                                @error('certificadoEmail')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="fecharCertificadoModal">
                                <i class="ri-close-line me-1"></i>Cancelar
                            </button>
                            <button type="button" class="btn btn-primary" wire:click="enviarCertificado">
                                <i class="ri-send-plane-fill me-1"></i>Enviar Certificado
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endteleport
    @endif
</div>

@once
<script>
document.addEventListener('livewire:init', () => {
    Livewire.on('show-success-alert', (event) => {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-right',
            iconColor: 'white',
            customClass: { popup: 'colored-toast' },
            showConfirmButton: false,
            timer: 6000,
            timerProgressBar: true,
            showCloseButton: true,
        });
        Toast.fire({ icon: 'success', title: event.message });
    });

    Livewire.on('show-error-alert', (event) => {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-right',
            iconColor: 'white',
            customClass: { popup: 'colored-toast' },
            showConfirmButton: false,
            timer: 6000,
            timerProgressBar: true,
            showCloseButton: true,
        });
        Toast.fire({ icon: 'error', title: event.message });
    });
});
</script>
@endonce

