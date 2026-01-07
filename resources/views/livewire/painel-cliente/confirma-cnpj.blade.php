<div>
    @if($isVisible)
    <div class="accordion-item shadow-sm mb-3">
        <h2 class="accordion-header" id="headingConfirmaCNPJ">
            <button class="accordion-button {{ $isOpen ? '' : 'collapsed' }} fw-semibold" type="button" 
                data-bs-toggle="collapse" data-bs-target="#collapseConfirmaCNPJ" 
                aria-expanded="{{ $isOpen ? 'true' : 'false' }}" aria-controls="collapseConfirmaCNPJ"
                wire:click="toggleAccordion">
                
                <span class="me-2">
                    <i class="ri-building-2-line fs-5 text-primary"></i>
                </span>
                
                @if(!empty($empresa['nome_razao']))
                    <div class="d-flex flex-column flex-md-row align-items-md-center gap-2 w-100">
                        <strong class="fs-5">{{ $empresa['nome_razao'] }}</strong>
                        <span class="badge bg-primary-subtle text-primary fs-6">{{ $empresa['cpf_cnpj'] }}</span>
                    </div>
                @elseif(!empty($empresa['cpf_cnpj']))
                    <div>
                        <span class="fs-5">Cadastrar Nova Empresa</span>
                        <span class="badge bg-success-subtle text-success ms-2 fs-6">{{ $empresa['cpf_cnpj'] }}</span>
                    </div>
                @else
                    <span class="text-muted">Dados da Empresa</span>
                @endif
            </button>
        </h2>
        <div id="collapseConfirmaCNPJ" class="accordion-collapse collapse {{ $isOpen ? 'show' : '' }}" 
            aria-labelledby="headingConfirmaCNPJ" data-bs-parent="#mainInscricaoAccordion">
            <div class="accordion-body bg-light">
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <strong> Atenção! </strong> Revise os dados da empresa e clique em "Continuar" para prosseguir com a inscrição.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <form wire:submit.prevent="salvar">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <x-forms.input-field wire:model="empresa.nome_razao" name="empresa_nome_razao"
                                label="Razão Social" :required="true" />
                            @error('empresa.nome_razao') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-6">
                            <x-forms.input-field wire:model="empresa.cpf_cnpj" name="empresa_cpf_cnpj" label="CNPJ"
                                :readonly="true" />
                            @error('empresa.cpf_cnpj') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-6">
                            <x-forms.input-field wire:model="empresa.telefone" name="empresa_telefone" label="Telefone"
                                class="telefone" maxlength="15"
                                x-mask:dynamic="$input.replace(/\D/g, '').length === 11 ? '(99) 99999-9999' : '(99) 9999-9999'" />
                            @error('empresa.telefone') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-6">
                            <x-forms.input-field wire:model="empresa.endereco_cobranca.email" name="empresa_cobranca_email"
                                label="E-mail de Cobrança" type="email" :required="true" />
                            @error('empresa.endereco_cobranca.email') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="col-12"><hr class="my-2"><h6 class="text-muted">Endereço de Cobrança</h6></div>

                        <div class="col-md-4">
                            <x-forms.input-field wire:model="empresa.endereco_cobranca.cep" name="empresa_cobranca_cep"
                                label="CEP" wire:blur="buscaCep" maxlength="9" x-mask="99999-999"
                                :required="true" />
                            @error('empresa.endereco_cobranca.cep') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-8">
                            <x-forms.input-field wire:model="empresa.endereco_cobranca.endereco"
                                name="empresa_cobranca_endereco" label="Endereço" :required="true" />
                            @error('empresa.endereco_cobranca.endereco') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-6">
                            <x-forms.input-field wire:model="empresa.endereco_cobranca.complemento"
                                name="empresa_cobranca_complemento" label="Complemento" />
                        </div>
                        <div class="col-md-6">
                            <x-forms.input-field wire:model="empresa.endereco_cobranca.bairro" name="empresa_cobranca_bairro"
                                label="Bairro" :required="true" />
                            @error('empresa.endereco_cobranca.bairro') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-6">
                            <x-forms.input-field wire:model="empresa.endereco_cobranca.cidade" name="empresa_cobranca_cidade"
                                label="Cidade" :required="true" />
                            @error('empresa.endereco_cobranca.cidade') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-2">
                            <x-forms.input-field wire:model="empresa.endereco_cobranca.uf" name="empresa_cobranca_uf"
                                label="UF" maxlength="2" style="text-transform: uppercase;"
                                :required="true" />
                            @error('empresa.endereco_cobranca.uf') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="mt-4 d-flex justify-content-end">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="ri-arrow-right-line me-2"></i>Continuar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
