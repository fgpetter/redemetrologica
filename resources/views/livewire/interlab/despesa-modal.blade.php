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
                                        <div class="text-muted small">
                                            <strong>{{ $produto['material_servico'] ?: '—' }}</strong>
                                            @if ($produto['quantidade'] || $produto['valor'])
                                                — Qtd: {{ $produto['quantidade'] ?: '—' }} | Valor: R$ {{ $produto['valor'] ?: '—' }}
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            @endforeach

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
