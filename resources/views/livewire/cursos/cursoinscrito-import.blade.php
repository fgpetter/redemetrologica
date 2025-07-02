<div>
    <div class="mb-3">
        <label for="arquivo" class="form-label">Selecione o arquivo (.xls, .xlsx, .csv)</label>
        <input type="file" id="arquivo" class="form-control" wire:model="arquivo" accept=".xls,.xlsx,.csv">
        @error('arquivo')
            <span class="text-danger">{{ $message }}</span>
        @enderror
    </div>
    <div wire:loading wire:target="arquivo" class="text-center my-3">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Carregando...</span>
        </div>
        <p>Analisando o arquivo, aguarde...</p>
    </div>
    @if (!empty($preview))
        @php
            $errorCount = count(array_filter($rowErrors));
            $totalRecords = count($preview);
        @endphp


        @if ($errorCount > 0)
            <h6 class="mt-4">Pré-visualização dos dados para correção</h6>
            <p class="text-muted">Corrija os campos com erro antes de importar.</p>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <div class="alert alert-info mb-0 py-2 text-center">
                        <strong>Total de registros:</strong> {{ $totalRecords }}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="alert alert-danger mb-0 py-2 text-center">
                        <strong>Registros com erros:</strong> <span wire:poll.500ms>{{ $errorCount }}</span>
                    </div>
                </div>
            </div>
        @else
            <div class="alert alert-success text-center mt-4 py-3">
                <i class="ri-check-double-line me-1 align-middle fs-4"></i>
                <strong class="fs-5">Tudo pronto!</strong><br>
                Existem <strong>{{ $totalRecords }}</strong> registros para importar.
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 20%;">Cpf_Cnpj</th>
                        <th class="text-center" style="width: 25%;">Nome_Razao</th>
                        <th class="text-center" style="width: 25%;">Email</th>
                        <th class="text-center" style="width: 20%;">Status</th>
                        <th class="text-center" style="width: 5%;"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($preview as $index => $row)
                        <tr>

                            <td>
                                <input type="text" class="form-control form-control-sm"
                                    wire:model.live.debounce.500ms="preview.{{ $index }}.cpf_cnpj"
                                    data-mask="cpf-cnpj">
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm"
                                    wire:model.live.debounce.500ms="preview.{{ $index }}.nome_razao">
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm"
                                    wire:model.live.debounce.500ms="preview.{{ $index }}.email">
                            </td>
                            <td class="text-center">
                                @if ($rowErrors[$index])
                                    <span class="text-danger" style="white-space: normal; word-wrap: break-word;">
                                        <i class="ri-error-warning-line me-1 align-middle"></i> {{ $rowErrors[$index] }}
                                    </span>
                                @else
                                    <span class="text-success d-flex align-items-center justify-content-center">
                                        <i class="ri-check-line me-1"></i> OK
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">

                                <button type="button" class="btn btn-danger btn-sm"
                                    wire:click="removeRow({{ $index }})">
                                    <i class="ri-delete-bin-line"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-between mt-3">

            <button type="button" class="btn btn-secondary" wire:click="addRow">
                <i class="ri-add-line align-bottom"></i> Adicionar Linha
            </button>
            @php
                $hasErrors = count(array_filter($rowErrors)) > 0;
            @endphp
            <button wire:click="importInscritos" class="btn btn-primary" wire:loading.attr="disabled"
                @if ($hasErrors) disabled @endif data-bs-dismiss="modal">
                <span wire:loading.remove wire:target="importInscritos">
                    <i class="ri-upload-2-line align-bottom"></i> Confirmar Importação
                </span>
                <span wire:loading wire:target="importInscritos" >
                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                    Importando...
                </span>
            </button>
        </div>
    @endif
</div>
