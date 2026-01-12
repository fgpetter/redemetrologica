<div class="card">
    <div class="card-body">

        <!-- Nav tabs -->
        <ul class="nav nav-pills arrow-navtabs nav-info bg-light mb-3" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#principal" role="tab" aria-selected="true">
                    Principal
                </a>
            </li>

            @if ($agendacurso->id)
                @if ($tipoagenda == 'ABERTO' || $agendacurso?->empresa_id)
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#participantes" role="tab"
                            aria-selected="false">
                            Participantes
                        </a>
                    </li>
                @endif

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
                @if ($tipoagenda == 'IN-COMPANY')
                    <x-painel.agendamento-cursos.form-principal-in-company :instrutores="$instrutores" :cursos="$cursos"
                        :empresas="$empresas" :agendacurso="$agendacurso" :cursoatual="$cursoatual" :instrutoratual="$instrutoratual" />
                @endif

                @if ($tipoagenda == 'ABERTO')
                    <x-painel.agendamento-cursos.form-principal :instrutores="$instrutores" :cursos="$cursos" :empresas="$empresas"
                        :agendacurso="$agendacurso" :cursoatual="$cursoatual" :instrutoratual="$instrutoratual" />
                @endif
            </div>

            <div class="tab-pane" id="participantes" role="tabpanel"> <!-- participantes -->
                <div class="row px-1 align-items-between">
                    <div class="col">
                        <h5 class="h5 mt-3">Inscritos</h5>
                    </div>
                    <div class="col d-flex justify-content-end align-items-center gap-1">
                        @if ($agendacurso?->tipo_agendamento != 'IN-COMPANY')
                            <a href="#" class="btn btn-sm btn-success" data-bs-toggle="modal"
                                data-bs-target="#adicionaInscritoModal">
                                <i class="ri-add-line align-bottom"></i> Adicionar inscrito
                            </a>
                        @endif
                        @if ($tipoagenda == 'ABERTO' && $agendacurso?->id && $agendacurso?->inscritos()->count() > 0)
                            <a href="{{ route('agendamento-curso.export-lista-presenca', $agendacurso) }}"
                                class="btn btn-sm btn-primary">
                                Baixar Lista de Presença
                            </a>
                        @endif



                    </div>
                </div>
                
                {{-- Lista de participantes livewire component --}}
                <livewire:cursos.list-participantes :agendacurso="$agendacurso" />

                <x-painel.agendamento-cursos.modal-insere-participante :agendacurso="$agendacurso" :empresas="$empresas"
                    :pessoas="$pessoas" />
                
            </div>

            <div class="tab-pane" id="despesas" role="tabpanel"> <!-- despesas -->
                <x-painel.agendamento-cursos.list-despesas :despesas="$despesas" :agendacurso="$agendacurso" :materiaispadrao="$materiaispadrao" />
            </div>

        </div>

    </div>

</div>
