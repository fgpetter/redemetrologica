<form class="d-flex justify-content-end pb-3" style="margin-top: -2rem" method="POST" 
  action="{{ route($route, $id) }}">
  @csrf
  <button type="submit" class="btn btn-sm btn-danger">Remover curso</button>
</form>