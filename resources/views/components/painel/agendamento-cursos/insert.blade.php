<div class="card">
  <div class="card-body">

    <!-- Nav tabs -->
    <ul class="nav nav-pills arrow-navtabs nav-info bg-light mb-3" role="tablist">
    <li class="nav-item">
      <a class="nav-link active" data-bs-toggle="tab" href="#principal" role="tab" aria-selected="true">
        Principal
      </a>
    </li>
    
    @if($agendacurso->id)
      <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#participantes" role="tab" aria-selected="false">
          Participantes
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#despesas" role="tab" aria-selected="false">
          Despesas
        </a>
      </li>
    @endif

    </ul>

    <!-- Tab panes -->
    <div class="tab-content">

      <div class="tab-pane active" id="principal" role="tabpanel"> <!-- Principal -->
        <x-painel.agendamento-cursos.form-principal :instrutores="$instrutores" :cursos="$cursos" :empresas="$empresas"
          :agendacurso="$agendacurso" :cursoatual="$cursoatual" :instrutoratual="$instrutoratual"/>
      </div>

      <div class="tab-pane" id="participantes" role="tabpanel"> <!-- participantes -->
        <h5 class="h5 mt-3">Inscritos</h5>
        <x-painel.agendamento-cursos.list-participantes :inscritos="$inscritos" />
        <x-painel.agendamento-cursos.modal-participante />

        <h5 class="h5 mt-5">Empresas participantes</h5>
        <x-painel.agendamento-cursos.list-empresas-participantes :inscritos="$inscritos" />
      </div>

      <div class="tab-pane" id="despesas" role="tabpanel"> <!-- despesas -->
      <x-painel.agendamento-cursos.list-despesas :despesas="$despesas" :agendacurso="$agendacurso" :materiaispadrao="$materiaispadrao"/>
      </div>

    </div>

  </div>

</div>
