<div @if ($loteAtivo) wire:poll.5s @endif>
    @if ($loteAtivo)
        <div class="alert alert-info mt-3 mb-0">
            O lote <strong>{{ $loteAtivo->nome }}</strong> está em processamento
            ({{ $loteAtivo->status->label() }}). Aguarde a conclusão para gerar outro.
        </div>
        @include('livewire.interlab.partials.lote-postagem-classificacao', ['lote' => $loteAtivo])
    @endif

    @if ($loteComErro)
        <div class="alert alert-danger mt-3 mb-0">
            O lote <strong>{{ $loteComErro->nome }}</strong> foi encerrado com erro
            @if ($loteComErro->etapa_erro)
                na etapa {{ $loteComErro->etapa_erro }}
            @endif
            @if ($loteComErro->erro_mensagem)
                : {{ $loteComErro->erro_mensagem }}
            @endif
            . É possível gerar outro lote.
        </div>
        @include('livewire.interlab.partials.lote-postagem-classificacao', ['lote' => $loteComErro])
        <div class="d-flex justify-content-end mt-2">
            <button type="button" class="btn btn-outline-secondary btn-sm" wire:click="dispensarErro">
                <i class="ri-refresh-line align-bottom"></i> Limpar status
            </button>
        </div>
    @endif

    @if (! $loteAtivo)
        <h5 class="h5 mt-3">Laboratórios inscritos</h5>

        @if ($errors->has('inscritosSelecionados'))
            <div class="alert alert-warning">
                <ul class="mb-0">
                    @foreach ($errors->get('inscritosSelecionados') as $erro)
                        <li>{{ $erro }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-striped align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th scope="col" style="width: 1%;">Seleção</th>
                        <th scope="col">Laboratório</th>
                        <th scope="col">Endereço</th>
                        <th scope="col">Informações da inscrição</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($inscritos as $inscrito)
                        @php
                            $endereco = $inscrito->laboratorio?->endereco;
                        @endphp
                        <tr wire:key="lote-postagem-inscrito-{{ $inscrito->id }}">
                            <td>
                                <div class="form-check mb-0">
                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        id="inscrito-postagem-{{ $inscrito->id }}"
                                        value="{{ $inscrito->id }}"
                                        wire:model="inscritosSelecionados"
                                    >
                                    <label class="form-check-label visually-hidden" for="inscrito-postagem-{{ $inscrito->id }}">
                                        Selecionar {{ $inscrito->laboratorio?->nome }}
                                    </label>
                                </div>
                            </td>
                            <td class="text-wrap">
                                <strong>{{ $inscrito->laboratorio?->nome ?? '—' }}</strong>
                                @if ($inscrito->laboratorio?->empresa?->nome_razao)
                                    <br>
                                    <span class="text-muted">{{ $inscrito->laboratorio->empresa->nome_razao }}</span>
                                @endif
                            </td>
                            <td class="text-wrap">
                                {{ $endereco?->endereco ?? 'N/A' }},
                                {{ $endereco?->complemento ?? 'N/A' }},
                                Bairro: {{ $endereco?->bairro ?? 'N/A' }}
                                <br>
                                Cidade: {{ $endereco?->cidade ?? 'N/A' }}
                                / {{ $endereco?->uf ?? 'N/A' }}, CEP:
                                {{ $endereco?->cep ?? 'N/A' }}
                            </td>
                            <td class="text-wrap">{{ $inscrito->informacoes_inscricao }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">Esta agenda não possui laboratórios inscritos.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($inscritos->isNotEmpty())
            <form
                class="mt-4"
                x-data="{
                    itens: @js($form->itensDeclaracao),
                    adicionar() {
                        this.itens.push({ conteudo: '', quantidade: '', valor_unitario: '' })
                    },
                    remover(indice) {
                        if (this.itens.length <= 1) {
                            return
                        }

                        if (! confirm('Remover este item da declaração?')) {
                            return
                        }

                        this.itens.splice(indice, 1)
                    },
                    enviar() {
                        $wire.gerarPostagem(this.itens)
                    },
                }"
                x-on:submit.prevent="enviar"
            >
                <h5 class="h5">Dados da postagem</h5>
                <p class="text-muted mb-0">Estes dados valem para todos os laboratórios selecionados.</p>
                <p class="text-muted">Formato: Caixa/pacote</p>

                <div class="row g-3">
                    <div class="col-md-6">
                        <x-forms.input-field wire:model="form.nome" name="form.nome" label="Nome do lote" required />
                        @error('form.nome')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <x-forms.input-field wire:model="form.pesoGramas" name="form.pesoGramas" label="Peso (gramas)" type="number" required />
                        @error('form.pesoGramas')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <x-forms.input-field wire:model="form.altura" name="form.altura" label="Altura (cm)" required />
                        @error('form.altura')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <x-forms.input-field wire:model="form.largura" name="form.largura" label="Largura (cm)" required />
                        @error('form.largura')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <x-forms.input-field wire:model="form.comprimento" name="form.comprimento" label="Comprimento (cm)" required />
                        @error('form.comprimento')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-4 mb-2">
                    <h6 class="mb-0">Itens da declaração de conteúdo</h6>
                    <button type="button" class="btn btn-sm btn-success" x-on:click="adicionar">
                        <i class="ri-add-line align-bottom"></i> Adicionar item
                    </button>
                </div>

                @foreach ($errors->getMessages() as $campo => $mensagens)
                    @continue(! str_starts_with($campo, 'form.itensDeclaracao'))
                    @foreach ($mensagens as $mensagem)
                        <span class="text-danger small d-block">{{ $mensagem }}</span>
                    @endforeach
                @endforeach

                <template x-for="(item, indice) in itens" :key="indice">
                    <div class="row g-3 align-items-end mb-2">
                        <div class="col-md-6">
                            <x-forms.input-field x-model="item.conteudo" label="Conteúdo" required />
                        </div>
                        <div class="col-md-2">
                            <x-forms.input-field x-model="item.quantidade" label="Quantidade" type="number" required />
                        </div>
                        <div class="col-md-3">
                            <x-forms.input-field x-model="item.valor_unitario" label="Valor unitário" mask="money" required />
                        </div>
                        <div class="col-md-1" x-show="itens.length > 1">
                            <button type="button" class="btn btn-sm btn-danger" x-on:click="remover(indice)">
                                <i class="ri-delete-bin-line"></i>
                            </button>
                        </div>
                    </div>
                </template>

                @if ($errors->any())
                    <div class="alert alert-warning mt-3 mb-0" id="erros-gerar-postagem">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $erro)
                                <li>{{ $erro }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="d-flex justify-content-end mt-3">
                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="gerarPostagem">
                        <span wire:loading.remove wire:target="gerarPostagem">
                            <i class="ri-truck-line align-bottom"></i> Gerar postagem
                        </span>
                        <span wire:loading wire:target="gerarPostagem">Gerando...</span>
                    </button>
                </div>
            </form>
        @endif

        @if ($lotesConcluidos->isNotEmpty())
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Lotes gerados</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th scope="col">Nome</th>
                                    <th scope="col">Data</th>
                                    <th scope="col">Itens</th>
                                    <th scope="col" style="width: 1%;">Documentos</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($lotesConcluidos as $loteConcluido)
                                    <tr wire:key="lote-concluido-{{ $loteConcluido->id }}">
                                        <td>{{ $loteConcluido->nome }}</td>
                                        <td>{{ $loteConcluido->created_at?->format('d/m/Y H:i') }}</td>
                                        <td>{{ $loteConcluido->itens->count() }}</td>
                                        <td class="text-nowrap">
                                            <a
                                                href="{{ route('agenda-interlab-lote-postagem-documentos', $loteConcluido) }}"
                                                class="btn btn-sm btn-outline-primary"
                                            >
                                                <i class="ri-download-2-line align-bottom"></i> Baixar documentos (.zip)
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>

@script
<script>
    $wire.on('show-success-alert', (event) => {
        toastPostagem('success', event.message);
    });

    $wire.on('show-error-alert', (event) => {
        toastPostagem('error', event.message);
        document.getElementById('erros-gerar-postagem')?.scrollIntoView({ block: 'center' });
    });

    function toastPostagem(icon, title) {
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

        Toast.fire({ icon, title });
    }
</script>
@endscript
