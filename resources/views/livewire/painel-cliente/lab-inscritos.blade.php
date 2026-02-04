<div>
    @if($isVisible)
        @if ($interlab->instrucoes_inscricao)
            <div class="alert alert-info alert-borderless shadow-sm mb-4" role="alert">
                <div class="d-flex">
                    <div class="flex-shrink-0">
                        <i class="ri-information-line fs-3"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="alert-heading">Informações Importantes:</h6>
                        <p class="mb-0">{!! nl2br(e($interlab->instrucoes_inscricao)) !!}</p>
                    </div>
                </div>
            </div>
        @endif

    @if(count($inscritos) > 0)
        <div class="mb-3">
            <h5 class="text-success">
                <i class="ri-list-check-2 me-2"></i>Laboratórios Inscritos
            </h5>
            <p class="text-muted small">Clique em um laboratório para editar as informações de inscrição.</p>
        </div>
        
        
        
        @foreach ($inscritos as $inscrito)
            <div class="accordion-item shadow-sm mb-3 border" wire:key="inscrito-{{ $inscrito->id }}">
                <h2 class="accordion-header" id="headingLab{{ $inscrito->id }}">
                    <button class="accordion-button collapsed fw-semibold" type="button" 
                        wire:click="edit({{ $inscrito->id }})"
                        @if($editingId && $editingId !== $inscrito->id) disabled @endif
                        data-bs-toggle="collapse" data-bs-target="#collapse-lab-{{ $inscrito->id }}"
                        aria-expanded="{{ $editingId === $inscrito->id ? 'true' : 'false' }}"
                    >
                        <div class="w-100 d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-3">
                                <i class="ri-flask-fill fs-5 text-success"></i>
                                <div>
                                    <strong class="d-block">{{ $inscrito->laboratorio->nome }}</strong>
                                    <small class="text-muted">{{ $inscrito->informacoes_inscricao }}</small>
                                </div>
                            </div>
                            @if(!$editingId)
                                <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle p-2 fs-5 lh-1 me-4">
                                    <i class="ri-check-line"></i>
                                </span>
                            @endif
                        </div>
                    </button>
                </h2>
                
                <div id="collapse-lab-{{ $inscrito->id }}" class="accordion-collapse collapse {{ $editingId === $inscrito->id ? 'show' : '' }}" 
                    aria-labelledby="headingLab{{ $inscrito->id }}" data-bs-parent="#mainInscricaoAccordion">
                    <div class="accordion-body bg-light">
                        @if($editingId === $inscrito->id)
                            <form wire:submit.prevent="salvar">
                                @include('livewire.painel-cliente.form-laboratorio')
                                <div class="mt-4 d-flex justify-content-end gap-2">
                                    <button type="button" class="btn btn-success btn-sm" wire:click="salvar">
                                        <i class="ri-save-line me-2"></i>Salvar
                                    </button>
                                    <button type="button" class="btn btn-warning btn-sm" wire:click="cancelEdit">
                                        <i class="ri-close-line me-2"></i>Cancelar
                                    </button>
                                </div>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    @endif
    @endif
</div>
