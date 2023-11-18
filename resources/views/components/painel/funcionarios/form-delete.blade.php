<form class="d-flex justify-content-end pb-3" style="margin-top: -2rem" method="POST" 
  action="{{ route($route, $id) }}">
  @csrf
  <button type="submit" class="btn px-3 btn-sm btn-danger">Remover</button>
</form>