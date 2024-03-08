@foreach (['error', 'success', 'warning'] as $key)
    @if (session($key))
        <script>
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-right',
                iconColor: 'white',
                customClass: {
                    popup: 'colored-toast',
                },
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
            })
            Toast.fire({
                icon: '{{ $key }}',
                title: '{{ session($key) }}',
            })
        </script>
    @endif
@endforeach
