<div>
    @if ($errors->any())
        <x-alerts.warning :errors="$errors->all()" />
    @endif

    <div class="row">
        <div class="col-12 d-flex justify-content-between my-3">
            <h6>Despesas do interlab</h6>
            <button type="button" class="btn btn-sm btn-success" wire:click="$dispatch('abrir-despesa-modal')">
                <i class="ri-add-line align-bottom me-1"></i> Adicionar despesa
            </button>
        </div>
        <div class="table-responsive" style="min-height: 25vh">
            <table class="table table-responsive table-striped align-middle table-nowrap mb-0">
                <thead>
                    <tr>
                        <th scope="col">Fornecedor</th>
                        <th scope="col" style="width: 10%; white-space: nowrap;">Data</th>
                        <th scope="col" style="width: 8%; white-space: nowrap;">Avaliação</th>
                        <th scope="col" style="width: 12%; white-space: nowrap;">Val total</th>
                        <th scope="col" style="width: 5%; white-space: nowrap;"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($lancamentos as $lancamento)
                        <tr wire:key="despesa-lancamento-{{ $lancamento['id'] }}">
                            <td>{{ $lancamento['fornecedor_nome'] }}</td>
                            <td>{{ $lancamento['ultima_data_compra'] ? $lancamento['ultima_data_compra']->format('d/m/Y') : '—' }}</td>
                            <td>{{ $lancamento['media_avaliacao'] !== null ? number_format($lancamento['media_avaliacao'], 1, '.', '') : '—' }}</td>
                            <td>{{ 'R$ ' . number_format($lancamento['total'], 2, ',', '.') }}</td>
                            <td>
                                <div class="dropdown">
                                    <a href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="ph-dots-three-outline-vertical" style="font-size: 1.5rem"
                                            data-bs-toggle="tooltip" data-bs-placement="top" title="Detalhes e edição"></i>
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a class="dropdown-item" href="#"
                                                wire:click="editarLancamento({{ $lancamento['id'] }})">
                                                Editar
                                            </a>
                                        </li>
                                        <li>
                                            <button type="button" class="dropdown-item text-danger"
                                                @click.prevent="Swal.fire({
                                                    title: 'Tem certeza?',
                                                    text: 'Remover este lançamento de despesa?',
                                                    icon: 'warning',
                                                    showCancelButton: true,
                                                    confirmButtonColor: '#3085d6',
                                                    cancelButtonColor: '#d33',
                                                    confirmButtonText: 'Sim, excluir!'
                                                }).then((result) => {
                                                    if (result.isConfirmed) {
                                                        $wire.deletarLancamento({{ $lancamento['id'] }});
                                                    }
                                                })">
                                                Deletar
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">Não há despesas cadastradas</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-end fw-bold">Total:</td>
                        <td class="fw-bold">{{ 'R$ ' . number_format($totalGeral, 2, ',', '.') }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
