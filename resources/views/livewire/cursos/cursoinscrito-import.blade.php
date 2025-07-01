<div>
    <div class="mb-3">
        <label for="arquivo" class="form-label">Selecione o arquivo (.xls, .xlsx, .csv)</label>
        <input type="file" id="arquivo" class="form-control" wire:model="arquivo" accept=".xls,.xlsx,.csv">
        @error('arquivo') <span class="text-danger">{{ $message }}</span> @enderror
    </div>

    <div wire:loading wire:target="arquivo" class="text-center my-3">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Carregando...</span>
        </div>
        <p>Analisando o arquivo, aguarde...</p>
    </div>

    @if(!empty($preview))
        <h6 class="mt-4">Pré-visualização dos dados para correção</h6>
        <p class="text-muted">Corrija os campos com erro antes de importar.</p>

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-nowrap">
                <thead>
                    <tr>
                        @foreach($headers as $header)
                            <th>{{ ucfirst(str_replace('_', ' ', $header)) }}</th>
                        @endforeach
                        <th style="width: 25%;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($preview as $index => $row)
                        <tr>
                            @foreach($headers as $header)
                                <td>
                                    <input type="text"
                                           class="form-control form-control-sm @if($rowErrors[$index] && str_contains(strtolower($rowErrors[$index]), strtolower($header))) is-invalid @endif"
                                           wire:model.live.debounce.500ms="preview.{{ $index }}.{{ $header }}">
                                </td>
                            @endforeach
                            <td>
                                @if($rowErrors[$index])
                                    <span class="text-danger d-flex align-items-center"><i class="ri-error-warning-line me-1"></i> {{ $rowErrors[$index] }}</span>
                                @else
                                    <span class="text-success d-flex align-items-center"><i class="ri-check-line me-1"></i> OK</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-end mt-3">
            <button wire:click="importar" class="btn btn-primary" wire:loading.attr="disabled" @if($this->hasErrors) disabled @endif>
                <span wire:loading.remove wire:target="importar">
                    <i class="ri-upload-2-line align-bottom"></i> Confirmar Importação
                </span>
                <span wire:loading wire:target="importar">
                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Importando...
                </span>
            </button>
        </div>
    @endif
</div>