<div>
    @if($isVisible)
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5 class="mb-3">
                        <i class="ri-search-line me-2 text-primary"></i>Informe o CNPJ da Empresa
                    </h5>
                    <p class="text-muted mb-3">Para prosseguir com a inscrição, é necessário informar um CNPJ para envio de Nota Fiscal e Cobrança.</p>
                    
                    <form wire:submit.prevent="ProcuraCnpj">
                        <div class="input-group input-group">
                            <span class="input-group-text bg-primary text-white">
                                <i class="ri-building-line"></i>
                            </span>
                            <input type="text" id="cnpj" wire:model="BuscaCnpj" 
                                class="form-control @error('BuscaCnpj') is-invalid @enderror" 
                                placeholder="00.000.000/0000-00" 
                                x-mask="99.999.999/9999-99">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="ri-search-line me-2"></i>Buscar
                            </button>
                        </div>
                        @error('BuscaCnpj')
                            <div class="text-danger mt-2">
                                <i class="ri-error-warning-line me-1"></i>{{ $message }}
                            </div>
                        @enderror
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
