 @if (isset($label))
     <form class=" form-delete d-flex justify-content-end pb-3" style="margin-top: -2rem" method="POST"
         action="{{ route($route, $id) }}">
         @csrf
         <button type="button" onclick="requestConfirmDelete(event)" class="btn btn-sm btn-danger">Remover {{ $label }}</button>
     </form>
 @else
     <form class=" form-delete " method="POST" action="{{ route($route, $id) }}">
         @csrf
         <button class="dropdown-item" type="button" onclick="requestConfirmDelete(event)">Deletar</button>
     </form>
 @endif
 <script>
    function requestConfirmDelete(event) {
        event.preventDefault();

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
                const form = document.querySelector('.form-delete');
                form.submit();
            }
        });
    }
 </script>
