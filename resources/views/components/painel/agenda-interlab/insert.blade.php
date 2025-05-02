<div class="card">
    <div class="card-body">

        <!-- Nav tabs -->
        <ul class="nav nav-pills arrow-navtabs nav-info bg-light mb-3" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#principal" role="tab" aria-selected="true">
                    Principal
                </a>
            </li>

            @if ($agendainterlab->id)
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
              <div class="row">
                <div class="col-xl-7 col-xxl-7">
                  <x-painel.agenda-interlab.form-principal :agendainterlab="$agendainterlab" :interlabs="$interlabs" />
                </div>

                <div class="col-xl-5 col-xxl-5">
                  @if ($agendainterlab->id)
                    <x-painel.agenda-interlab.arquivos-interlab :agendainterlab="$agendainterlab" :interlabs="$interlabs"/>
                  @endif
                </div>
              </div>
            </div>

            <div class="tab-pane" id="participantes" role="tabpanel"> <!-- participantes -->
                <div class="col-12">
                    <div class="row px-1 py-2 align-items-center">
                        <div class="col">
                            <h5 class="h5 mt-3">Inscritos</h5>
                        </div>
                        <div class="col d-flex justify-content-end gap-2">
                            <a href="#" class="btn btn-sm btn-success" data-bs-toggle="modal"
                                data-bs-target="#adicionaParticipanteModal">
                                <i class="ri-add-line align-bottom"></i> Adicionar Laboratório
                            </a>
                            @if ($agendainterlab->inscritos->count() > 0)
                                <a href="{{ route('interlab-relatorio-inscritos', $agendainterlab->uid) }}"
                                    class="btn btn-sm btn-primary">
                                    <i class="ri-file-excel-line align-bottom"></i> Baixar XLS
                                </a>
                            @endif
                        </div>
                    </div>

                    <x-painel.agenda-interlab.list-participantes :pessoas="$pessoas" :agendainterlab="$agendainterlab" :intelabinscritos="$intelabinscritos"
                        :interlabempresasinscritas="$interlabempresasinscritas" />
                </div>
            </div>

            <div class="tab-pane" id="despesas" role="tabpanel"> <!-- despesas -->
                <div class="col-12">
                    <x-painel.agenda-interlab.despesas :agendainterlab="$agendainterlab" :materiaisPadrao="$materiaisPadrao" :interlabDespesa="$interlabDespesa"
                        :interlabParametros="$interlabParametros" :parametros="$parametros" :fornecedores="$fornecedores" :fabricantes="$fabricantes" />
                </div>
            </div>

            <div class="tab-pane" id="rodadas" role="tabpanel"> <!-- despesas -->
                <x-painel.agenda-interlab.rodadas :agendainterlab="$agendainterlab" :interlabParametros="$interlabParametros" :rodadas="$rodadas" />
            </div>

        </div>

    </div>

</div>
