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
                        <th scope="col">Material/Serviço</th>
                        <th scope="col" style="width: 12%; white-space: nowrap;">Val total</th>
                        <th scope="col" style="width: 5%; white-space: nowrap;"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($agrupadas as $grupo)
                        <tr wire:key="despesa-{{ $grupo['fornecedor_id'] }}">
                            <td>{{ $grupo['fornecedor_nome'] }}</td>
                            <td>
                                @if (count($grupo['materiais']) > 1)
                                    <ul class="mb-0 ps-3">
                                        @foreach ($grupo['materiais'] as $material)
                                            <li>{{ $material }}</li>
                                        @endforeach
                                    </ul>
                                @elseif (count($grupo['materiais']) === 1)
                                    {{ $grupo['materiais'][0] }}
                                @else
                                    —
                                @endif
                            </td>
                            <td>{{ 'R$ ' . number_format($grupo['total'], 2, ',', '.') }}</td>
                            <td>
                                <div class="dropdown">
                                    <a href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="ph-dots-three-outline-vertical" style="font-size: 1.5rem"
                                            data-bs-toggle="tooltip" data-bs-placement="top" title="Detalhes e edição"></i>
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a class="dropdown-item" href="#"
                                                wire:click="editarFornecedor({{ $grupo['fornecedor_id'] }})">
                                                Editar
                                            </a>
                                        </li>
                                        <li>
                                            <button type="button" class="dropdown-item text-danger"
                                                @click.prevent="Swal.fire({
                                                    title: 'Tem certeza?',
                                                    text: 'Remover todas as despesas deste fornecedor?',
                                                    icon: 'warning',
                                                    showCancelButton: true,
                                                    confirmButtonColor: '#3085d6',
                                                    cancelButtonColor: '#d33',
                                                    confirmButtonText: 'Sim, excluir!'
                                                }).then((result) => {
                                                    if (result.isConfirmed) {
                                                        $wire.deletarPorFornecedor({{ $grupo['fornecedor_id'] }});
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
                            <td colspan="4" class="text-center">Não há despesas cadastradas</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2" class="text-end fw-bold">Total:</td>
                        <td class="fw-bold">{{ 'R$ ' . number_format($totalGeral, 2, ',', '.') }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
