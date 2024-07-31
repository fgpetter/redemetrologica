<div class="card">
  <div class="card-body">
    <form method="POST" action="{{ route('laboratorio-update', $laboratorio->uid)}}" enctype="multipart/form-data">
      @csrf

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
            Laboratorios Internos
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
          <div class="row">
            <div class="col-12">
              <x-painel.laboratorios.dados-principais :laboratorio="$laboratorio" />
            </div>

            @if ($laboratorio->uid)
              <div class="col-12 d-flex justify-content-end">
                <x-painel.laboratorios.form-delete route="laboratorio-delete" id="{{ $laboratorio->uid }}" />
              </div>
            @endif

            <div class="col-12 mt-4 px-0">
              <x-painel.enderecos.list class="shadow-none" :pessoa="$laboratorio->pessoa" />
            </div>

          </div>
        </div>
        <div class="tab-pane" id="laboratorios" role="tabpanel">
          <x-painel.laboratorios.lab-internos :laboratorio="$laboratorio" :areasatuacao="$areasatuacao" />
        </div>
        <div class="tab-pane" id="analises" role="tabpanel">
        </div>
      </div>



    </form>

  </div>

</div>
