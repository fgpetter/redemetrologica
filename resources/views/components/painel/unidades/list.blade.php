<div class="card">
  <div class="card-header d-flex justify-content-between align-items-start">
  <div class="d-flex align-items-center gap-3">
    <div class="avatar-sm">
      <span class="avatar-title bg-dark-subtle text-dark rounded-circle fs-3">
        <i class="ph-buildings-light"></i>
      </span>
    </div>
    <h4 class="card-title mb-0">Unidades</h4>
  </div>
  <a class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#modal_cadastro">
    <i class="ri-add-line align-bottom me-1"></i>
    Adicionar
  </a>
  </div><!-- end card header -->
  <div class="card-body">
    @forelse ($pessoa->unidades as $unidade)
      <x-painel.unidades.card-dados :unidade="$unidade" />
      <x-painel.unidades.modal :unidade="$unidade" :pessoa="$pessoa" />
    @empty
      <p>Não há unidade cadastrada</p>
    @endforelse
    <x-painel.unidades.modal :unidade="null" :pessoa="$pessoa" />
  </div>
</div>
