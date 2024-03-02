<form class=" form-delete " method="POST" action="{{ route($route, $id) }}">
    @csrf

    <button class=" botao-delete dropdown-item" type="submit">Deletar</button>


</form>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('.form-delete');
        const deleteButton = form.querySelector('.botao-delete');

        deleteButton.addEventListener('click', function(e) {
            e.preventDefault();

            Swal.fire({
                title: 'Tem certeza?',
                text: "Você não poderá reverter isso!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sim, excluir!'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
</script>
