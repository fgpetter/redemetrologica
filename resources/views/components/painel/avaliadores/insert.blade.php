     <x-alerts.alert  />
 <div class="card">
     <div class="card-body">
         <form method="POST"
             action="{{ isset($avaliador->uid) ? route('avaliador-update', $avaliador->uid) : route('avaliador-create') }}"
             enctype="multipart/form-data">
             @csrf

             <ul class="nav nav-tabs nav-justified mb-3" role="tablist">
                 <li class="nav-item">
                     <a class="nav-link active" data-bs-toggle="tab" href="#principal" role="tab"
                         aria-selected="true">
                         Dados Principais
                     </a>
                 </li>
                 <li class="nav-item">
                     <a class="nav-link" data-bs-toggle="tab" href="#avaliacoes" role="tab" aria-selected="false">
                         Avaliações
                     </a>
                 </li>
                 <li class="nav-item">
                     <a class="nav-link" data-bs-toggle="tab" href="#qualificacoes" role="tab"
                         aria-selected="false">
                         Qualificações
                     </a>
                 </li>
             </ul>

             <div class="tab-content">
                 <div class="tab-pane active" id="principal" role="tabpanel"> <!-- Dados principais -->
                     <div class="row">
                         <div class="col-12">
                             <x-painel.avaliadores.dados-principais :avaliador="$avaliador" />
                         </div>

                         @if ($avaliador->uid)
                             <div class="col-12 d-flex justify-content-end">
                                 <x-painel.avaliadores.form-delete route="avaliador-delete"
                                     id="{{ $avaliador->uid }}" />
                             </div>
                         @endif

                         <div class="col-12 mt-4 px-0">
                             <x-painel.enderecos.list class="shadow-none" :pessoa="$avaliador->pessoa" />
                         </div>

                     </div>
                 </div>
                 <div class="tab-pane" id="avaliacoes" role="tabpanel">
                     <x-painel.avaliadores.avaliacoes :avaliacoes="$avaliacoes" :avaliador="$avaliador" />
                 </div>
                 <div class="tab-pane" id="qualificacoes" role="tabpanel">
                     {{-- <x-painel.avaliadores.qualificacoes :qualificacoes="$qualificacoes"/> --}}
                     {{-- <x-painel.avaliadores.certificados :certificados="$certificados"/> --}}
                     {{-- <x-painel.avaliadores.area-atucao :areaatuacao="$areaatuacao"/> --}}
                 </div>
             </div>



         </form>
         @if ($avaliador->id)
             <form method="POST" id="curriculo-delete"
                 action="{{ route('avaliador-curriculo-delete', $avaliador->uid) }}">
                 @csrf
             </form>
         @endif



     </div>

 </div>
