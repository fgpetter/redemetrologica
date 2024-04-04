@if (isset($label))
<form class=" form-delete d-flex justify-content-end pb-3" style="margin-top: -2rem" method="POST" action="{{ route($route, $id) }}">
  @csrf
  <button type="submit"  class="btn btn-sm btn-danger botao-delete">Remover {{ $label }}</button>
</form>
@else
<form class=" form-delete " method="POST" action="{{ route($route, $id) }}">
  @csrf
  <button class="dropdown-item botao-delete" type="submit">Deletar</button>
</form>
@endif

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