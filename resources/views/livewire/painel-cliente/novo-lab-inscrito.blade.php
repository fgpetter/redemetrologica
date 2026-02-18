<div x-data
     x-on:scroll-to-errors.window="
        $nextTick(() => {
            const anchor = document.getElementById('laboratorios-disponiveis-line');
            if (anchor) {
                anchor.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        })
     ">
    @if($isVisible)
        @if($laboratorios_disponiveis->count() > 0)
            <div class="mb-3" id="laboratorios-disponiveis-line">
                <h5 class="text-primary">
                    <i class="ri-flask-line me-2"></i>Laboratórios Disponíveis para Inscrição
                </h5>
                <p class="text-muted small">Selecione um laboratório já cadastrado ou cadastre um novo.</p>
            </div>
            
            @foreach ($laboratorios_disponiveis as $lab)
                <div class="accordion-item shadow-sm mb-3 border" wire:key="lab-disponivel-{{ $lab->id }}">
                    <h2 class="accordion-header" id="headingLabDisp{{ $lab->id }}">
                        <button class="accordion-button {{ $selecionadoId === $lab->id ? '' : 'collapsed' }}" 
                            type="button" 
                            wire:click="selectLab({{ $lab->id }})"
                            data-bs-toggle="collapse" 
                            data-bs-target="#collapse-labdisp-{{ $lab->id }}"
                            aria-expanded="{{ $selecionadoId === $lab->id ? 'true' : 'false' }}"
                            aria-controls="collapse-labdisp-{{ $lab->id }}">
                            <div class="w-100 d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center gap-3">
                                    <i class="ri-flask-line fs-5 text-primary"></i>
                                    <div>
                                        <div class="fs-5 py-1">{{ $lab->nome }}</div>
                                        <div class="py-1">{{ $lab->endereco->endereco ?? '' }}, {{ $lab->endereco->complemento ?? '' }} - {{ $lab->endereco->cidade ?? '' }} - {{ $lab->endereco->uf ?? '' }}</div>
                                        <small class="text-muted">
                                            @if($selecionadoId === $lab->id)
                                                Revise os dados do laboratório e clique em salvar
                                            @else
                                                Clique para inscrever este laboratório
                                            @endif
                                        </small>
                                    </div>
                                </div>
                                @if($selecionadoId !== $lab->id)
                                    <span class="badge bg-primary d-flex align-items-center py-2 fs-6 fw-normal">
                                        Inscrever
                                    </span>
                                @endif
                            </div>
                        </button>
                    </h2>
                    
                    <div id="collapse-labdisp-{{ $lab->id }}" class="accordion-collapse collapse {{ $selecionadoId === $lab->id ? 'show' : '' }}" 
                        aria-labelledby="headingLabDisp{{ $lab->id }}" data-bs-parent="#mainInscricaoAccordion">
                        <div class="accordion-body bg-light">
                            @if($selecionadoId === $lab->id)
                                @include('livewire.painel-cliente.form-laboratorio')
                                <div class="mt-4 d-flex justify-content-end gap-2">
                                    <button type="submit" class="btn btn-success btn-sm" wire:click="salvar">
                                        <i class="ri-save-line me-2"></i>Salvar
                                    </button>
                                    <button type="button" class="btn btn-warning btn-sm" wire:click="$set('selecionadoId', null)">
                                        <i class="ri-close-line me-2"></i>Cancelar
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
        
        <div class="accordion-item shadow-sm mb-3 border border-success" wire:key="lab-novo">
            <h2 class="accordion-header" id="headingLabNovo">
                <button class="accordion-button {{ $selecionadoId === 'new' ? '' : 'collapsed' }} fw-semibold" 
                    type="button" 
                    wire:click="selectLab('new')"
                    data-bs-toggle="collapse" 
                    data-bs-target="#collapse-labnovo"
                    aria-expanded="{{ $selecionadoId === 'new' ? 'true' : 'false' }}"
                    aria-controls="collapse-labnovo">
                    <div class="w-100 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-3">
                            <i class="ri-add-circle-fill fs-5 text-success"></i>
                            <div>
                                <strong class="d-block text-success">Cadastrar Novo Laboratório</strong>
                                <small class="text-muted">Criar e inscrever um novo laboratório</small>
                            </div>
                        </div>
                        @if($selecionadoId !== 'new')
                            <span class="badge bg-success d-flex align-items-center justify-content-center py-2 px-3 fs-6 fw-normal">
                                <i class="ri-add-line me-1"></i>Novo
                            </span>
                        @endif
                    </div>
                </button>
            </h2>
            
            <div id="collapse-labnovo" class="accordion-collapse collapse {{ $selecionadoId === 'new' ? 'show' : '' }}" 
                aria-labelledby="headingLabNovo" data-bs-parent="#mainInscricaoAccordion">
                <div class="accordion-body bg-light">
                    @if($selecionadoId === 'new')
                        @include('livewire.painel-cliente.form-laboratorio')
                        <div class="mt-4 d-flex justify-content-end gap-2">
                            <button type="submit" class="btn btn-success btn-sm" wire:click="salvar">
                                <i class="ri-save-line me-2"></i>Salvar
                            </button>
                            <button type="button" class="btn btn-warning btn-sm" wire:click="$set('selecionadoId', null)">
                                <i class="ri-close-line me-2"></i>Cancelar
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>