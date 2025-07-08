<div>
    @if ($showModal)
    <div class="modal fade show" style="display: block;" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form wire:submit.prevent="substituir">
                    <div class="modal-header">
                        <h5 class="modal-title">Substituir Responsável</h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        @if ($interlabInscrito)
                            <p>Selecione o novo responsável para a inscrição de <strong>{{ $interlabInscrito->laboratorio->nome }}</strong>.</p>
                            <div class="mb-3">
                                <label for="novo_responsavel_id" class="form-label">Novo Responsável</label>
                                <select class="form-control"  data-choices  wire:model="novo_responsavel_id" id="novo_responsavel_id"required>
                                    <option value="">Selecione...</option>
                                    @foreach($pessoas->where('tipo_pessoa', 'PF') as $pessoa)
                                        <option value="{{ $pessoa->id }}">{{ $pessoa->cpf_cnpj }} |{{ $pessoa->nome_razao }}</option>
                                    @endforeach
                                </select>
                                @error('novo_responsavel_id') <span class="text-danger">{{ $message }}</span> @enderror

                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">Fechar</button>
                        <button type="submit" class="btn btn-primary">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show"></div>
    @endif
</div>
