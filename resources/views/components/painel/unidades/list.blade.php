 @if (session('error'))
     <x-alerts.alert type="error" />
 @endif
 @if (session('success'))
     <x-alerts.alert type="success" />
 @endif

 <div class="card">
     <div class="card-header d-flex justify-content-between">
         <h4 class="card-title mb-0">Unidades</h4>
         <a class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#modal_cadastro"> <i
                 class="ri-add-line align-bottom me-1"></i> Adicionar </a>
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
