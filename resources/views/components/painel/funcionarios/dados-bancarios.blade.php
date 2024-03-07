     <x-alerts.alert  />

 <div class="card">
     <div class="card-header d-flex justify-content-between">
         <h4 class="card-title mb-0">Dados bancários</h4>
         <a data-bs-toggle="modal" data-bs-target="#modal_conta_cadastro" class="btn btn-sm btn-success"> <i
                 class="ri-add-line align-bottom me-1"></i> Adicionar </a>
     </div><!-- end card header -->
     <div class="card-body">

         <ul class="list-group list-group-flush">
             @forelse ($funcionario->pessoa->dadosBancarios as $conta)
                 <x-painel.funcionarios.modal-dados-bancarios :funcionario="$funcionario" :conta="$conta" />
                 <div class="list-group-item d-flex justify-content-between align-items-center">
                     <div>
                         <strong>{{ $conta->nome_conta }}</strong><br />
                         {{ $conta->nome_banco }}, COD: {{ $conta->cod_banco }} <br />
                         Agência: {{ $conta->agencia }} - CC: {{ $conta->conta }}
                     </div>
                     <div>
                         <a href="#" role="button" id="dropdownMenuLink1" data-bs-toggle="dropdown"
                             aria-expanded="false">
                             <i class="ph-dots-three-outline-vertical" style="font-size: 1.5rem"
                                 data-bs-toggle="tooltip" title="Detalhes e edição"></i>
                         </a>
                         <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink1">
                             <li>
                                 <a class="dropdown-item" data-bs-toggle="modal"
                                     data-bs-target="{{ "#modal_conta_$conta->uid" }}">Editar</a>
                             </li>

                             <x-painel.form-delete.delete route='conta-delete' id="{{ $conta->uid }}" />

                         </ul>
                     </div>
                 </div>
             @empty
                 <p>Não há conta bancária cadastrada</p>
         </ul>
         @endforelse
     </div>
 </div>
 <x-painel.funcionarios.modal-dados-bancarios :conta="null" :funcionario="$funcionario" />
