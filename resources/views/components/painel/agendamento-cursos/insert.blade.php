 <div class="card">
     <div class="card-body">

         <!-- Nav tabs -->
         <ul class="nav nav-tabs nav-justified mb-3" role="tablist">
             <li class="nav-item">
                 <a class="nav-link active" data-bs-toggle="tab" href="#principal" role="tab" aria-selected="true">
                     Principal
                 </a>
             </li>

             <li class="nav-item">
                 <a class="nav-link" data-bs-toggle="tab" href="#participantes" role="tab" aria-selected="false">
                     Participantes
                 </a>
             </li>

             <li class="nav-item">
                 <a class="nav-link" data-bs-toggle="tab" href="#NF" role="tab" aria-selected="false">
                     Notas Fiscais
                 </a>
             </li>

             <li class="nav-item">
                 <a class="nav-link" data-bs-toggle="tab" href="#despesas" role="tab" aria-selected="false">
                     Despesas
                 </a>
             </li>


         </ul>

         <!-- Tab panes -->
         <div class="tab-content">

             <div class="tab-pane active" id="principal" role="tabpanel"> <!-- Principal -->
                 <x-painel.agendamento-cursos.form-principal :instrutores="$instrutores" :cursos="$cursos" :empresas="$empresas"
                     :agendacurso="$agendacurso" :cursoatual="$cursoatual" />
             </div>

             <div class="tab-pane" id="participantes" role="tabpanel"> <!-- participantes -->
                 <h5 class="h5 mt-3">Inscritos</h5>
                 <x-painel.agendamento-cursos.list-participantes :inscritos="$inscritos" />
                 <x-painel.agendamento-cursos.modal-participante />

                 <h5 class="h5 mt-5">Empresas participantes</h5>
                 <x-painel.agendamento-cursos.list-empresas-participantes :empresas="$inscritosempresas" />
             </div>

             <div class="tab-pane" id="NF" role="tabpanel"> <!-- notas fiscais -->
                 {{-- <x-painel.agendamento-cursos.list-nf /> --}}
             </div>

             <div class="tab-pane" id="despesas" role="tabpanel"> <!-- despesas -->
             </div>

         </div>

     </div>

 </div>
