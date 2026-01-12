<div>
    <div class="row">
        <div class="col-md-3">
            <label for="num_ensaios" class="form-label">Num Ensaios</label>
            <input type="text" class="form-control text-end" id="num_ensaios"
                value="{{ number_format($num_ensaios, 2, ',', '.') }}" readonly>
        </div>
        <div class="col-md-3">
            <label for="soma_avaliadores" class="form-label">Soma Avaliadores</label>
            <input type="text" class="form-control text-end" id="soma_avaliadores"
                value="{{ number_format($soma_avaliadores, 2, ',', '.') }}" readonly>
        </div>
        <div class="col-md-3">
            <label for="soma_despesas_estimadas" class="form-label">Soma Despesas Estimadas</label>
            <input type="text" class="form-control text-end" id="soma_despesas_estimadas"
                value="{{ number_format($soma_despesas_estimadas, 2, ',', '.') }}" readonly>
        </div>
        <div class="col-md-3">
            <label for="soma_despesas_reais" class="form-label">Soma Despesas Reais</label>
            <input type="text" class="form-control text-end" id="soma_despesas_reais"
                value="{{ number_format($soma_despesas_reais, 2, ',', '.') }}" readonly>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-3">
            <label for="perc_lucro" class="form-label">Perc Lucro (%)</label>
            <input type="number" step="0.01" class="form-control text-end" id="perc_lucro"
                wire:model.live="perc_lucro">
            @error('perc_lucro')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
        <div class="col-md-3">
            <label for="superavit" class="form-label">Superavit</label>
            <input type="text" class="form-control text-end" id="superavit"
                value="{{ number_format($superavit, 2, ',', '.') }}" readonly>
        </div>
        <div class="col-md-3">
            <label for="data_envio_proposta" class="form-label">Data Envio Proposta</label>
            <input type="date" class="form-control" id="data_envio_proposta" wire:model="data_envio_proposta">
            @error('data_envio_proposta')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
        <div class="col-md-3">
            <label for="num_aval_treinamento" class="form-label">Num Aval Treinamento</label>
            <input type="number" class="form-control text-end" id="num_aval_treinamento"
                wire:model="num_aval_treinamento">
            @error('num_aval_treinamento')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-12">
            <label for="observacoes_orcamento" class="form-label">Observações Orçamento</label>
            <textarea class="form-control" id="observacoes_orcamento" rows="3" wire:model="observacoes_orcamento"></textarea>
            @error('observacoes_orcamento')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-12">
            <button type="button" class="btn btn-primary" wire:click="gerarOrcamento">
                Gerar Orçamento
            </button>
        </div>
    </div>
</div>
