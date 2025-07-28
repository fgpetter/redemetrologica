<div class="card">
  <div class="card-body">

    <ul class="nav nav-pills arrow-navtabs nav-info bg-light mb-3" role="tablist">
      <li class="nav-item">
        <a class="nav-link active" data-bs-toggle="tab" href="#principal" role="tab"
          aria-selected="true">
          Dados Principais
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#laboratorios" role="tab"
          aria-selected="false">
          Áreas
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#orcamentos" role="tab"
          aria-selected="false">
          Orçamentos
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#analises" role="tab"
          aria-selected="false">
          Análises realizadas
        </a>
      </li>
    </ul>

    <div class="tab-content">
      <div class="tab-pane active" id="principal" role="tabpanel"> <!-- Dados principais -->
            <x-painel.avaliacoes.dados-principais :avaliacao="$avaliacao" :laboratorio="$laboratorio" :tipoavaliacao="$tipoavaliacao" />
      </div>

      <div class="tab-pane" id="laboratorios" role="tabpanel"> <!-- Áreas -->
        <x-painel.avaliacoes.areas :avaliacao="$avaliacao" :laboratorio="$laboratorio" :avaliadores="$avaliadores" />
      </div>
      
      <div class="tab-pane" id="orcamentos" role="tabpanel"> <!-- Orçamentos -->
        <livewire:avaliacoes.agenda-avaliacoes-orcamentos :avaliacao="$avaliacao" />
      </div>

      <div class="tab-pane" id="analises" role="tabpanel"> <!-- Análises realizadas -->
      </div>
    </div>

  </div>

</div>
