<div>
    @if ($showModal)
        <div class="modal fade show" style="display: block;" aria-modal="true" role="dialog">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $despesaEditandoId ? 'Editar Despesa' : 'Adicionar Despesa' }}</h5>
                        <button type="button" class="btn-close" wire:click="fechar" aria-label="Fechar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row gy-3">
                            <div class="col-12">
                                <label for="fornecedor_id" class="form-label">Fornecedor <span class="text-danger">*</span></label>
                                <select id="fornecedor_id" class="form-select" wire:model="fornecedorId" required>
                                    <option value="">Selecione na lista</option>
                                    @foreach ($fornecedores as $fornecedor)
                                        <option value="{{ $fornecedor->id }}">{{ $fornecedor->pessoa->nome_razao }}</option>
                                    @endforeach
                                </select>
                                @error('fornecedorId')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>

                            @foreach ($produtos as $index => $produto)
                                <div class="col-12 border rounded p-3 mb-2" wire:key="produto-{{ $index }}">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="fw-semibold">Produto {{ $index + 1 }}</span>
                                        <div class="d-flex gap-1">
                                            @if ($editingIndex !== $index)
                                                <button type="button" class="btn btn-sm btn-dark" wire:click="editarProduto({{ $index }})">Editar</button>
                                            @endif
                                            @if (count($produtos) > 1)
                                                <button type="button" class="btn btn-sm btn-outline-danger" wire:click="removerProduto({{ $index }})">Remover</button>
                                            @endif
                                        </div>
                                    </div>

                                    @if ($editingIndex === $index)
                                        @include('livewire.interlab.partials.despesa-produto-form', ['index' => $index, 'produto' => $produto])
                                    @else
                                        @php
                                            $qtd = (float) str_replace(',', '.', $produto['quantidade'] ?: '0');
                                            $val = (float) str_replace(['.', ','], ['', '.'], $produto['valor'] ?: '0');
                                            $totalCalc = $qtd * $val;
                                        @endphp
                                        <div class="text-muted small">
                                            <strong>Material/Serviço:</strong> {{ $produto['material_servico'] ?: '—' }}<br>
                                            <strong>Fabricante:</strong> {{ $produto['fabricante'] ?: '—' }}
                                            &nbsp;&middot;&nbsp;<strong>Cód Fabricante:</strong> {{ $produto['cod_fabricante'] ?: '—' }}
                                            &nbsp;&middot;&nbsp;<strong>Lote:</strong> {{ $produto['lote'] ?: '—' }}<br>
                                            <strong>Quantidade:</strong> {{ $produto['quantidade'] ?: '—' }}
                                            &nbsp;&middot;&nbsp;<strong>Valor:</strong> {{ $produto['valor'] ? 'R$ ' . $produto['valor'] : '—' }}
                                            &nbsp;&middot;&nbsp;<strong>Total:</strong> {{ $totalCalc > 0 ? 'R$ ' . number_format($totalCalc, 2, ',', '.') : '—' }}<br>
                                            <strong>Validade:</strong> {{ $produto['validade'] ? \Carbon\Carbon::parse($produto['validade'])->format('d/m/Y') : '—' }}
                                            &nbsp;&middot;&nbsp;<strong>Data Compra:</strong> {{ $produto['data_compra'] ? \Carbon\Carbon::parse($produto['data_compra'])->format('d/m/Y') : '—' }}
                                        </div>
                                    @endif
                                </div>
                            @endforeach

                            @if (count($produtos) >= 1)
                                <div class="col-12 border rounded p-3">
                                    <h6 class="mb-3">Avaliar fornecedor</h6>
                                    @error('avaliacao')
                                        <div class="text-danger small mb-2">{{ $message }}</div>
                                    @enderror
                                    <div class="row gy-2">
                                        <div class="col-md-4">
                                            <label for="avaliacao_custo" class="form-label">Custo</label>
                                            <select id="avaliacao_custo" class="form-select" wire:model="avaliacaoCusto">
                                                <option value="">Não avaliar</option>
                                                @for ($i = 0; $i <= 5; $i++)
                                                    <option value="{{ $i }}">{{ $i }}</option>
                                                @endfor
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="avaliacao_tempo" class="form-label">Tempo</label>
                                            <select id="avaliacao_tempo" class="form-select" wire:model="avaliacaoTempo">
                                                <option value="">Não avaliar</option>
                                                @for ($i = 0; $i <= 5; $i++)
                                                    <option value="{{ $i }}">{{ $i }}</option>
                                                @endfor
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="avaliacao_qualidade" class="form-label">Qualidade</label>
                                            <select id="avaliacao_qualidade" class="form-select" wire:model="avaliacaoQualidade">
                                                <option value="">Não avaliar</option>
                                                @for ($i = 0; $i <= 5; $i++)
                                                    <option value="{{ $i }}">{{ $i }}</option>
                                                @endfor
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="col-12">
                                <button type="button" class="btn btn-sm btn-outline-primary" wire:click="adicionarProduto">
                                    <i class="ri-add-line align-bottom me-1"></i> Adicionar produto
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="fechar">Fechar</button>
                        <button type="button" class="btn btn-primary" wire:click="salvar" wire:loading.attr="disabled">
                            <span wire:loading.remove>Salvar</span>
                            <span wire:loading>Salvando...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif
</div>
