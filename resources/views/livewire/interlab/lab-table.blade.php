<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-12 d-flex justify-content-end mb-3">
                <button wire:click="create" class="btn btn-sm btn-success">
                    <i class="ri-add-line align-bottom me-1"></i> Adicionar Laboratório
                </button>
            </div>
        </div>

        {{-- Filtros --}}
        <div class="card border shadow-sm mb-3">
            <div class="card-body p-2">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="card-title mb-0">Filtros</h6>
                </div>

                <div class="row gx-3 align-items-center">
                    <!-- Filtro por Empresa -->
                    <div class="col-4" wire:ignore>
                        <label class="form-label mb-0">Empresa</label>
                        <select wire:model.live="empresaSelecionada" id="empresa-select-filter" class="form-select form-select-sm">
                            <option value="">Selecione...</option>
                            @foreach ($this->empresas as $empresa)
                                <option value="{{ $empresa->id }}">{{ $empresa->cpf_cnpj }} - {{ $empresa->nome_razao }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Pesquisa Global -->
                    <div class="col-3">
                        <label class="form-label mb-0">Laboratório</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="ri-search-line"></i>
                            </span>
                            <input wire:model.live.debounce.300ms="search" class="form-control"
                                type="text" placeholder="Pesquisar por nome...">
                        </div>
                    </div>

                    <!-- Pesquisa por PEP -->
                    <div class="col-3">
                        <label class="form-label mb-0">PEP</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="ri-search-line"></i>
                            </span>
                            <input wire:model.live.debounce.300ms="searchPep" class="form-control"
                                type="text" placeholder="Pesquisar por PEP...">
                        </div>
                    </div>

                    <!-- Botão Limpar Filtros -->
                    <div class="col text-end">
                        <div class="d-flex flex-column align-items-end">
                            <label class="form-label invisible mb-0">Limpar</label>
                            <button wire:click="resetFilters" type="button" class="btn btn-sm btn-light text-danger">
                                <i class="ri-close-line"></i> Limpar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabela --}}
        <div class="table-responsive" style="min-height: 25vh">
            <table class="table table-striped align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col" style="width: 5%;">ID</th>
                        <th scope="col" style="width: 20%;">
                            <a href="#" wire:click.prevent="setSortBy('empresa')">
                                Empresa
                                @if ($sortBy == 'empresa')
                                    <i class="ri-arrow-{{ $sortDirection == 'ASC' ? 'up' : 'down' }}-s-line"></i>
                                @endif
                            </a>
                        </th>
                        <th scope="col" style="width: 20%;">
                            <a href="#" wire:click.prevent="setSortBy('nome')">
                                Nome
                                @if ($sortBy == 'nome')
                                    <i class="ri-arrow-{{ $sortDirection == 'ASC' ? 'up' : 'down' }}-s-line"></i>
                                @endif
                            </a>
                        </th>
                        <th scope="col" style="width: 30%;">PEP Inscrito</th>
                        <th scope="col" style="width: 10%;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($laboratorios as $lab)
                        <tr wire:key="lab-{{ $lab->id }}">
                            <td>
                                <a href="#" wire:click.prevent="edit('{{ $lab->uid }}')">
                                    {{ $lab->id }}
                                </a>
                            </td>
                            <td>{{ $lab->empresa->nome_razao ?? '-' }}</td>
                            <td>{{ $lab->nome }}</td>
                            <td>
                                @php
                                    $peps = $lab->inscritos
                                        ->pluck('agendaInterlab.interlab.nome')
                                        ->filter()
                                        ->unique()
                                        ->values();
                                @endphp
                                @if($peps->isNotEmpty())
                                    {{ $peps->implode(', ') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                <div class="dropdown">
                                    <a href="#" role="button" id="dropdownMenuLink{{ $lab->id }}"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="ph-dots-three-outline-vertical fs-5"></i>
                                    </a>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink{{ $lab->id }}">
                                        <li>
                                            <a class="dropdown-item" href="#" wire:click.prevent="edit('{{ $lab->uid }}')">
                                                Editar
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item text-danger" href="#" onclick="confirmDelete('{{ $lab->uid }}')">
                                                Excluir
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">Nenhum laboratório encontrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="row mt-3 w-100">
                {{ $laboratorios->links() }}
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="labModal" tabindex="-1" aria-labelledby="labModalLabel" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="labModalLabel">{{ $isEdit ? 'Editar' : 'Novo' }} Laboratório</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit="save">
                        
                        <h6 class="mb-3">Dados do Laboratório</h6>
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">Empresa <span class="text-danger">*</span></label>
                                <div wire:ignore>
                                    <select id="empresa-select-modal" class="form-select">
                                        <option value="">Selecione a empresa...</option>
                                        @foreach($allEmpresas as $emp)
                                            <option value="{{ $emp->id }}">{{ $emp->cpf_cnpj }} - {{ $emp->nome_razao }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('empresa_id') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Nome do Laboratório <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" wire:model="nome">
                                @error('nome') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <hr class="my-4">
                        <h6 class="mb-3">Endereço</h6>
                        
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">CEP <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" wire:model.blur="cep" x-mask="99999-999">
                                @error('cep') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-9">
                                <label class="form-label">Endereço <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" wire:model="endereco">
                                @error('endereco') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Bairro <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" wire:model="bairro">
                                @error('bairro') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Cidade <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" wire:model="cidade">
                                @error('cidade') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">UF <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" wire:model="uf" maxlength="2">
                                @error('uf') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Complemento</label>
                                <input type="text" class="form-control" wire:model="complemento">
                                @error('complemento') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="mt-4 text-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                            <button type="submit" class="btn btn-primary">Salvar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@section('script')
<script>
    document.addEventListener('livewire:initialized', () => {
        // Filter Choices
        const filterElement = document.getElementById('empresa-select-filter');
        const filterChoices = new Choices(filterElement, {
            searchFields: ['label'],
            allowHTML: true,
            itemSelectText: '',
            noResultsText: 'Nenhum resultado',
        });

        filterElement.addEventListener('change', (event) => {
            @this.set('empresaSelecionada', event.target.value);
        });

        Livewire.on('reset-empresa-filter', () => {
            filterChoices.setChoiceByValue('');
        });


        // Modal Choices
        const modalElement = document.getElementById('empresa-select-modal');
        const modalChoices = new Choices(modalElement, {
            searchFields: ['label'],
            allowHTML: true,
            itemSelectText: '',
            noResultsText: 'Nenhum resultado',
            shouldSort: false,
        });

        modalElement.addEventListener('change', (event) => {
            @this.set('empresa_id', event.target.value);
        });

        Livewire.on('reset-empresa-modal', () => {
            modalChoices.setChoiceByValue('');
        });

        Livewire.on('set-empresa-modal', (id) => {
             if(id) modalChoices.setChoiceByValue(String(id));
        });

        // Modal Control
        const modalEl = document.getElementById('labModal');
        const modal = new bootstrap.Modal(modalEl);

        Livewire.on('open-modal', () => {
            modal.show();
        });

        Livewire.on('close-modal', () => {
            modal.hide();
        });
        
        // Fetch Address via CEP (Optional JS helper or handled by backend)
        // Here we rely on user typing or use a separate API call if requested. All good for now.
    });
</script>

<script>
    function confirmDelete(uid) {
        Swal.fire({
            title: 'Tem certeza?',
            text: "Você não poderá reverter isso!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sim, excluir!',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                @this.call('delete', uid);
            }
        });
    }
</script>
@endsection
