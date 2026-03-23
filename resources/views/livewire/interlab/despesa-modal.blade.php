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
                                <div
                                    class="col-12 border rounded p-3 mb-2"
                                    wire:key="produto-{{ $index }}"
                                    x-data="{
                                        quantidade: '{{ $produto['quantidade'] }}',
                                        valor: '{{ $produto['valor'] }}',
                                        get total() {
                                            const qtd = parseFloat(this.quantidade.replace(',', '.')) || 0;
                                            const val = parseFloat(this.valor.replace(',', '.')) || 0;
                                            if (qtd === 0 || val === 0) return '';
                                            return (qtd * val).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                                        }
                                    }"
                                >
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="fw-semibold">Produto {{ $index + 1 }}</span>
                                        @if (count($produtos) > 1)
                                            <button type="button" class="btn btn-sm btn-outline-danger" wire:click="removerProduto({{ $index }})">
                                                Remover
                                            </button>
                                        @endif
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-12">
                                            <label class="form-label mb-0">Material/Serviço <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" wire:model="produtos.{{ $index }}.material_servico" required />
                                            @error("produtos.{$index}.material_servico")
                                                <span class="text-danger small">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="col-6 col-md-4">
                                            <label class="form-label mb-0">Fabricante</label>
                                            <input type="text" class="form-control" wire:model="produtos.{{ $index }}.fabricante" />
                                        </div>
                                        <div class="col-6 col-md-2">
                                            <label class="form-label mb-0">Cod Fabricante</label>
                                            <input type="text" class="form-control" wire:model="produtos.{{ $index }}.cod_fabricante" />
                                        </div>
                                        <div class="col-4">
                                            <label class="form-label mb-0">Quantidade</label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                wire:model="produtos.{{ $index }}.quantidade"
                                                x-model="quantidade"
                                                placeholder="0,00"
                                            />
                                        </div>
                                        <div class="col-4">
                                            <label class="form-label mb-0">Valor</label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                wire:model="produtos.{{ $index }}.valor"
                                                x-model="valor"
                                                placeholder="0,00"
                                            />
                                        </div>
                                        <div class="col-4">
                                            <label class="form-label mb-0">Total</label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                readonly
                                                :value="total"
                                                :placeholder="total ? '' : 'Calculado automaticamente'"
                                            />
                                        </div>
                                        <div class="col-4">
                                            <label class="form-label mb-0">Lote</label>
                                            <input type="text" class="form-control" wire:model="produtos.{{ $index }}.lote" />
                                        </div>
                                        <div class="col-4">
                                            <label class="form-label mb-0">Validade</label>
                                            <input type="date" class="form-control" wire:model="produtos.{{ $index }}.validade" />
                                        </div>
                                        <div class="col-4">
                                            <label class="form-label mb-0">Data da compra</label>
                                            <input type="date" class="form-control" wire:model="produtos.{{ $index }}.data_compra" />
                                        </div>
                                    </div>
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
