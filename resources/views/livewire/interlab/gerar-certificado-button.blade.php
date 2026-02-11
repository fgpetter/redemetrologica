
<div>
    <button wire:click="confirmarEnvio" class="dropdown-item">
        Gerar Certificado
    </button>

    <!-- Modal de Confirmação de Email -->
    @if($showModal)
        @teleport('body')
            <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5); z-index: 1060;">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content text-start">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="ri-mail-send-line me-2"></i>Confirmar Email para Envio do Certificado
                            </h5>
                            <button type="button" class="btn-close" wire:click="fecharModal"></button>
                        </div>
                        <div class="modal-body">
                            <p class="text-muted mb-3">
                                O certificado será enviado para o email abaixo. Você pode confirmar ou alterar o endereço antes de enviar.
                            </p>
                            <div class="mb-3 text-start">
                                <label for="email" class="form-label fw-semibold">Email de Destino</label>
                                <input type="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       id="email" 
                                       wire:model="email"
                                       placeholder="exemplo@email.com">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="fecharModal">
                                <i class="ri-close-line me-1"></i>Cancelar
                            </button>
                            <button type="button" class="btn btn-primary" wire:click="enviarCertificado">
                                <i class="ri-send-plane-fill me-1"></i>Enviar Certificado
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endteleport
    @endif
</div>

@once
<script>
document.addEventListener('livewire:init', () => {
    Livewire.on('show-success-alert', (event) => {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-right',
            iconColor: 'white',
            customClass: {
                popup: 'colored-toast',
            },
            showConfirmButton: false,
            timer: 6000,
            timerProgressBar: true,
            showCloseButton: true
        });
        
        Toast.fire({
            icon: 'success',
            title: event.message,
        });
    });

    Livewire.on('show-error-alert', (event) => {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-right',
            iconColor: 'white',
            customClass: {
                popup: 'colored-toast',
            },
            showConfirmButton: false,
            timer: 6000,
            timerProgressBar: true,
            showCloseButton: true
        });
        
        Toast.fire({
            icon: 'error',
            title: event.message,
        });
    });
});
</script>
@endonce