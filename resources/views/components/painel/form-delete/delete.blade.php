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