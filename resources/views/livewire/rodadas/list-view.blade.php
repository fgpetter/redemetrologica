<div class="card shadow-none overflow-hidden mt-3">
    <div class="card-header d-flex justify-content-between align-items-start py-2">
        <div class="d-flex align-items-center gap-3">
            <h5 class="h5 mt-3">Rodadas</h5>
        </div>
        
        <button class="btn btn-sm btn-success mt-2" 
                data-bs-toggle="modal" 
                data-bs-target="#modalRodada"
                wire:click="abrirModal">
            <i class="ri-add-line align-bottom me-1"></i>
            Adicionar rodada
        </button>
    </div>
    
    <div class="card-body px-1">
        <div class="table-responsive" style="min-height: 25vh">
            <table class="table table-responsive table-striped align-middle table-nowrap mb-0">
                <thead>
                    <tr>
                        <th scope="col">Descrição</th>
                        <th scope="col">Cronograma</th>
                        <th scope="col">Parâmetros dessa rodada</th>
                        <th scope="col" style="width: 5%; white-space: nowrap;">Vias</th>
                        <th scope="col" style="width: 5%; white-space: nowrap;"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rodadas as $rodada)
                        <tr wire:key="{{ $rodada->uid }}">
                            <td>{{ $rodada->descricao }}</td>
                            <td style="width: 30%; white-space: normal;">{!! nl2br($rodada->cronograma) !!}</td>
                            <td style="width: 30%; white-space: normal;">
                                <ul>
                                    @forelse ($rodada->parametros as $parametro)
                                        <li>{{ $parametro->parametro->descricao }}</li>
                                    @empty
                                        Nenhum Parâmetro
                                    @endforelse
                                </ul>
                            </td>
                            <td class="text-center">{{ $rodada->vias }}</td>
                            <td>
                                <div class="dropdown">
                                    <a href="#" role="button" id="dropdownMenuLink2" data-bs-toggle="dropdown"
                                        aria-expanded="false">
                                        <i class="ph-dots-three-outline-vertical" style="font-size: 1.5rem"
                                            data-bs-toggle="tooltip" data-bs-placement="top" title="Detalhes e edição"></i>
                                    </a>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink2">
                                        <li>
                                            <a class="dropdown-item" 
                                               href="javascript:void(0)" 
                                               data-bs-toggle="modal" 
                                               data-bs-target="#modalRodada"
                                               wire:click="abrirModal('{{ $rodada->uid }}')">
                                                Editar
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" 
                                               href="javascript:void(0)" 
                                               wire:click="deletar('{{ $rodada->uid }}')"
                                               wire:confirm="Tem certeza que deseja excluir esta rodada?">
                                                Excluir
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">Não há rodadas cadastradas</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Único -->
    <div wire:ignore.self class="modal fade" id="modalRodada" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        {{ $rodadaAtiva ? 'Editar Rodada' : 'Adicionar Rodada' }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <livewire:rodadas.modalform 
                        :agendainterlab="$agendainterlab" 
                        :rodadaUid="$rodadaAtiva"
                        key="{{ $rodadaAtiva ?? 'new' }}" />
                </div>
            </div>
        </div>
    </div>
</div>

