
<div>
    <button wire:click="gerarCertificado" class="dropdown-item">
        Gerar Certificado
    </button>
</div>

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
});
</script>