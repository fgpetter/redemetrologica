<div class="alert alert-warning alert-top-border alert-dismissible fade show" role="alert">
  <i class="ri-alert-line me-3 align-middle fs-lg text-warning"></i>
  <strong>Erro ao salvar</strong>
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  
    <ul>
      @foreach ($errors as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>

</div>
