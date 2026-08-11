<div>
    <div class="row">
        <div class="col-md-3">
            <label for="perc_lucro" class="form-label">Perc Lucro (%)</label>
            <input type="number" step="0.01" class="form-control text-end" id="perc_lucro"
                wire:model.blur.live="form.perc_lucro">
            @error('form.perc_lucro')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
        <div class="col-md-3">
            <label for="data_envio_proposta" class="form-label">Data Envio Proposta</label>
            <input type="date" class="form-control" id="data_envio_proposta"
                wire:model="form.data_envio_proposta">
            @error('form.data_envio_proposta')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-md-6 mt-4">
                
            <div class="row gy-3">
                <div class="col-md-6">
                    <span class="">Previsão Data Inicio:</span>
                    <span class="ms-1">{{ $dataInicio ?? '—' }}</span>
                </div>
                <div class="col-md-6">
                    <span class="">Previsão Data Fim:</span>
                    <span class="ms-1">{{ $dataFim ?? '—' }}</span>
                </div>
    
                <div class="col-md-6">
                    <span class="">Número de Avaliadores:</span>
                    <span class="ms-1">{{ $numAvaliadores }}</span>
                </div>
                <div class="col-md-6">
                    <span class="">Avaliadores em Treinamento:</span>
                    <span class="ms-1">{{ $numAvalTreinamento }}</span>
                </div>
    
                <div class="col-12">
                    <span class="">Avaliações:</span>
                    <span class="ms-1">{{ $avaliacoes !== '' ? $avaliacoes : '—' }}</span>
                </div>
    
                <div class="col-md-6">
                    <span class="">Ensaios:</span>
                    <span class="ms-1">{{ number_format($numEnsaios, 0, ',', '.') }}</span>
                </div>
                <div class="col-md-6">
                    <span class="">Total Dias de Trabalho:</span>
                    <span class="ms-1">{{ number_format($totalDiasTrabalho, 1, ',', '.') }}</span>
                </div>
    
                <div class="col-12">
                    <span class="">Remuneração total dos avaliadores:</span>
                    <span class="ms-1">{{ number_format($somaAvaliadores, 2, ',', '.') }}</span>
                </div>
    
                <div class="col-md-4">
                    <span class="">Despesas Estimadas:</span>
                    <span class="ms-1">{{ number_format($somaDespesasEstimadas, 2, ',', '.') }}</span>
                </div>
                <div class="col-md-4">
                    <span class="">Despesas Reais:</span>
                    <span class="ms-1">{{ number_format($somaDespesasReais, 2, ',', '.') }}</span>
                </div>
                <div class="col-md-4">
                    <span class="">NF (Tributos):</span>
                    <span class="ms-1">{{ number_format($nf, 2, ',', '.') }}</span>
                </div>
                <div class="col-md-4">
                    <span class="">Superavit:</span>
                    <span class="ms-1">{{ number_format($superavit, 2, ',', '.') }}</span>
                </div>
    
                <div class="col-12">
                    <span class="">Valor Total:</span>
                    <span class="ms-1 fw-semibold">{{ number_format($valorProposta, 2, ',', '.') }}</span>
                </div>
            </div>
    
        
            <div class="row mt-4">
                <div class="col-md-12">
                    <label for="observacoes_orcamento" class="form-label">Observações Orçamento</label>
                    <textarea class="form-control" id="observacoes_orcamento" rows="3"
                        wire:model="form.observacoes_orcamento"></textarea>
                    @error('form.observacoes_orcamento')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        
        </div>
    </div>


    <div class="row mt-3">
        <div class="col-md-12">
            <button type="button" class="btn btn-primary" wire:click="gerarOrcamento">
                Gerar Orçamento
            </button>
            @error('template')
                <div class="text-danger mt-2">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

@script
<script>
    $wire.on('show-orcamento-validation-alert', (event) => {
        Swal.fire({
            icon: 'warning',
            title: 'Atenção',
            text: event.message,
            confirmButtonText: 'OK',
        });
    });
</script>
@endscript
