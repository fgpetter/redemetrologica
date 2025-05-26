<div>
    {{-- Área com 2 inputs de datas que servirão para filtrar os lançamentos financeiros --}}
    <div class="row mb-3">
        <div class="col-md-5">
            <label for="dataInicio" class="form-label">Data Início</label>
            <input type="date" id="dataInicio" class="form-control" wire:model="dataInicio">
        </div>
        <div class="col-md-5">
            <label for="dataFim" class="form-label">Data Fim</label>
            <input type="date" id="dataFim" class="form-control" wire:model="dataFim">
        </div>
        <div class="col-md-2">
            <label for="dataFim" class="form-label invisible">filtrar</label>
            <button class="btn btn-primary" wire:click="filtrarLancamentos">Filtrar Lançamentos</button>
        </div>
    </div>

    {{-- Área com uma lista com os lançamentos financeiros filtrados e seus totais --}}
    <div class="mb-3">
        <h5>Lançamentos Filtrados</h5>
        @if(is_countable($lancamentos) && count($lancamentos) > 0)
            <div style="max-height: 300px; overflow-y: auto;">
                <ul class="list-group">
                    @foreach($lancamentos as $lancamento)
                        <li class="list-group-item">
                            <strong>ID:</strong> {{ $lancamento->id }} |
                            <strong>Histórico:</strong> {{ $lancamento->historico ?? 'N/A' }} |
                            <strong>Valor:</strong> R$ {{ number_format($lancamento->valor, 2, ',', '.') }} |
                            <strong>Data de Vencimento:</strong> {{ $lancamento->data_vencimento ?? 'N/A' }} |
                            @if($lancamento->pessoa && $lancamento->pessoa->dadoBancario)
                                <strong>Banco:</strong> {{ $lancamento->pessoa->dadoBancario->cod_banco ?? 'N/A' }} |
                                <strong>Agência:</strong> {{ $lancamento->pessoa->dadoBancario->agencia ?? 'N/A' }} |
                                <strong>Conta:</strong> {{ $lancamento->pessoa->dadoBancario->conta ?? 'N/A' }}
                            @else
                                <strong>Dados Bancários:</strong> Não disponíveis
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
            <div class="mt-2">
                <strong>Total:</strong> R$ {{ number_format(collect($lancamentos)->sum('valor'), 2, ',', '.') }}
            </div>
        @else
            <p>Nenhum lançamento encontrado para os filtros aplicados.</p>
        @endif
    </div>

    {{-- Text box para visualizar o arquivo que será gerado --}}
    <div class="mb-3">
        <label for="arquivoGerado" class="form-label">Arquivo Gerado</label>
        <textarea id="arquivoGerado" class="form-control" rows="5" readonly>{{ $arquivoGerado }}</textarea>
    </div>

    {{-- Área com um botão para gerar o arquivo --}}
    <div class="mb-3">
        <button class="btn btn-primary" wire:click="gerarArquivo">Gerar Arquivo</button>
    </div>

    {{-- Área com um botão para baixar o arquivo --}}
    <div class="mb-3">
        @if($arquivoGerado)
            <button class="btn btn-success" wire:click="baixarArquivo">Baixar Arquivo</button>
        @endif
    </div>
</div>
