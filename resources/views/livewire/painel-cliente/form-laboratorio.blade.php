<form wire:submit.prevent="salvar">
    <div class="row g-3">
        <div class="col-12 col-xl-6">
            <x-forms.input-field wire:model="laboratorio.nome" name="lab_nome" label="Laboratório" required />
            @error('laboratorio.nome') <span class="text-danger small">{{ $message }}</span> @enderror
        </div>
        <div class="col-12 col-xl-6">
            <x-forms.input-field wire:model="laboratorio.responsavel_tecnico" name="lab_resp" label="Responsável Técnico" required />
            @error('laboratorio.responsavel_tecnico') <span class="text-danger small">{{ $message }}</span> @enderror
        </div>
        <div class="col-12 col-sm-6">
            <x-forms.input-field wire:model="laboratorio.telefone" name="lab_tel" label="Telefone" 
                class="telefone" maxlength="15"
                x-mask:dynamic="$input.replace(/\D/g, '').length === 11 ? '(99) 99999-9999' : '(99) 9999-9999'" />
            @error('laboratorio.telefone') <span class="text-danger small">{{ $message }}</span> @enderror
        </div>
        <div class="col-12 col-sm-6">
            <x-forms.input-field wire:model="laboratorio.email" name="lab_email" label="E-mail" type="email" required />
            @error('laboratorio.email') <span class="text-danger small">{{ $message }}</span> @enderror
        </div>

        <div class="col-12"><hr class="my-2"><h6 class="text-muted">Endereço do Laboratório</h6></div>
        
        <div class="col-md-4">
             <x-forms.input-field wire:model="laboratorio.endereco.cep" name="lab_cep" label="CEP" 
                wire:blur="buscaCep" maxlength="9" x-mask="99999-999" required />
             @error('laboratorio.endereco.cep') <span class="text-danger small">{{ $message }}</span> @enderror
        </div>
        <div class="col-md-8">
             <x-forms.input-field wire:model="laboratorio.endereco.endereco" name="lab_end" label="Endereço" required />
             @error('laboratorio.endereco.endereco') <span class="text-danger small">{{ $message }}</span> @enderror
        </div>
         <div class="col-md-6">
             <x-forms.input-field wire:model="laboratorio.endereco.complemento" name="lab_comp" label="Complemento" />
        </div>
        <div class="col-md-6">
             <x-forms.input-field wire:model="laboratorio.endereco.bairro" name="lab_bairro" label="Bairro" required />
             @error('laboratorio.endereco.bairro') <span class="text-danger small">{{ $message }}</span> @enderror
        </div>
        <div class="col-md-6">
             <x-forms.input-field wire:model="laboratorio.endereco.cidade" name="lab_cid" label="Cidade" required />
             @error('laboratorio.endereco.cidade') <span class="text-danger small">{{ $message }}</span> @enderror
        </div>
        <div class="col-md-2">
             <x-forms.input-field wire:model="laboratorio.endereco.uf" name="lab_uf" label="UF" maxlength="2" style="text-transform: uppercase;" required />
             @error('laboratorio.endereco.uf') <span class="text-danger small">{{ $message }}</span> @enderror
        </div>

        <div class="col-12">
            <x-forms.input-textarea wire:model="informacoes_inscricao" name="lab_obs" label="Observações adicionais">
            </x-forms.input-textarea>
        </div>

        <!-- Blocos de inscrição -->
        @if ($valores_inscricao && $valores_inscricao->isNotEmpty())
            <div class="col-12"><hr class="my-3"></div>
            <div class="col-12">
                <div class="card border">
                    <div class="card-header bg-primary-subtle">
                        <h6 class=" text-primary">
                            <i class="ri-checkbox-multiple-line me-2"></i>Selecione os Blocos de Inscrição
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @foreach ($valores_inscricao as $valorItem)
                                <div class="col-12 col-md-6 col-lg-4">
                                    <div class="border rounded p-3 h-100 shadow-sm d-flex align-items-start">
                                        <div class="form-check mb-0">
                                            <input class="form-check-input fs-5 mt-0" type="checkbox"
                                                wire:model.live="blocos_selecionados"
                                                value="{{ $valorItem->id }}"
                                                id="bloco_{{ $valorItem->id }}">
                                        </div>
                                        <label class="form-check-label ms-3 flex-grow-1" for="bloco_{{ $valorItem->id }}" style="cursor: pointer;">
                                            <strong class="d-block mb-2">{{ $valorItem->descricao }}</strong>
                                            <span class="badge bg-primary fs-6">
                                                R$ {{ number_format($valorItem->valor, 2, ',', '.') }}
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @error('blocos_selecionados') <span class="text-danger d-block mt-3">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>
        @endif

        <!-- Solicita certificado -->
        <div class="col-12">
            <div class="card border-warning">
                <div class="card-body">
                    <div class="form-check form-switch form-switch-lg">
                        <input class="form-check-input" type="checkbox" wire:model.live="solicitar_certificado" id="certificado_switch">
                        <label class="form-check-label" for="certificado_switch">
                            <strong><i class="ri-award-line me-2"></i>Solicitar Certificado de Desempenho</strong> 
                            <span class="badge bg-warning text-dark ms-2">+ R$ 300,00</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    
</form>
