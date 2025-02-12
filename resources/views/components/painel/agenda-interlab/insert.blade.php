<div class="card">
  <div class="card-body">

    <!-- Nav tabs -->
    <ul class="nav nav-pills arrow-navtabs nav-info bg-light mb-3" role="tablist">
      <li class="nav-item">
        <a class="nav-link active" data-bs-toggle="tab" href="#principal" role="tab" aria-selected="true">
        Principal
        </a>
      </li>

      @if($agendainterlab->id)
        <li class="nav-item">
          <a class="nav-link" data-bs-toggle="tab" href="#participantes" role="tab" aria-selected="false">
          Participantes
          </a>
        </li>

        <li class="nav-item">
          <a class="nav-link" data-bs-toggle="tab" href="#despesas" role="tab" aria-selected="false">
          Despesas e Parametros
          </a>
        </li>

        <li class="nav-item">
          <a class="nav-link" data-bs-toggle="tab" href="#rodadas" role="tab" aria-selected="false">
          Rodadas
          </a>
        </li>
      @endif
    </ul>

    <!-- Tab panes -->
    <div class="tab-content">

      <div class="tab-pane active" id="principal" role="tabpanel"> <!-- Principal -->
       <div class="col-sm-8">
        <x-painel.agenda-interlab.form-principal 
        :agendainterlab="$agendainterlab" 
        :interlabs="$interlabs"/>
       </div>
      </div>

      <div class="tab-pane" id="participantes" role="tabpanel"> <!-- participantes -->
        <div class="col-12">
          <div class="row px-1 align-items-between">
            <div class="col">
              <h5 class="h5 mt-3">Inscritos</h5>
            </div>
            <div class="col">
              <a href="#" class="btn btn-sm btn-success float-end" data-bs-toggle="modal"
                data-bs-target="#adicionaParticipanteModal">
                <i class="ri-add-line align-bottom me-1"></i> Adicionar Laboratório
              </a>
            </div>
          </div>
  
          <x-painel.agenda-interlab.list-participantes 
            :pessoas="$pessoas"
            :agendainterlab="$agendainterlab"
            :intelabinscritos="$intelabinscritos"
            :interlabempresasinscritas="$interlabempresasinscritas" />
        </div>
      </div>

      <div class="tab-pane" id="despesas" role="tabpanel"> <!-- despesas -->
        <div class="col-12">
          <x-painel.agenda-interlab.despesas 
            :agendainterlab="$agendainterlab" 
            :materiaisPadrao="$materiaisPadrao"
            :interlabDespesa="$interlabDespesa"
            :interlabParametros="$interlabParametros"
            :parametros="$parametros"
            :fornecedores="$fornecedores"
            :fabricantes="$fabricantes" />
        </div>
      </div>

      <div class="tab-pane" id="rodadas" role="tabpanel"> <!-- despesas -->
        <x-painel.agenda-interlab.rodadas 
          :agendainterlab="$agendainterlab"
          :interlabParametros="$interlabParametros" 
          :rodadas="$rodadas"/>
      </div>

    </div>

  </div>

</div>
