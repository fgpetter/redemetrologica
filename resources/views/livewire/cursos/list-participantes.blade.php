<div>
    @if ($errors->any())
        <div class="alert alert-warning">
            <strong>Erro ao salvar os dados!</strong> <br><br>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    @if (session()->has('success'))
        <div wire:key="success-{{ rand() }}" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 1500)" class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" @click="show = false"></button>
        </div>
    @endif
    @if ($agendacurso->tipo_agendamento == 'IN-COMPANY')
    <div class="card mb-3 border-0 shadow-none">
        <div class="card-body p-0">
            <form wire:submit.prevent="saveInscrito" class="row g-2 align-items-start">
                <div class="col-md-3">
                    <input type="text" class="form-control" placeholder="Nome" wire:model.defer="nome">
                    @error('nome') <span class="text-danger small d-block mt-1">{{ $message }}</span> @enderror
                </div>
                <div class="col-md-3">
                    <input type="email" class="form-control" placeholder="E-mail" wire:model.defer="email">
                    @error('email') <span class="text-danger small d-block mt-1">{{ $message }}</span> @enderror
                </div>
                <div class="col-md-3">
                    <input type="text" class="form-control" placeholder="Telefone" wire:model.defer="telefone"
                        x-mask="(99) 99999-9999">
                    @error('telefone') <span class="text-danger small d-block mt-1">{{ $message }}</span> @enderror
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100"><i class="ri-add-line"></i> Adicionar</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <div class="table-responsive" style="min-height: 180px">
        <table class="table table-responsive table-striped align-middle table-nowrap mb-0">
            <thead>
                <tr>
                    <th scope="col" style="width: 8%; white-space: nowrap;">
                        <a href="" wire:click.prevent="setSortBy('data_inscricao')">
                            Data Inscrição
                            @if($sortBy === 'data_inscricao')
                                @if($sortDirection === 'ASC')
                                    <i class="ri-arrow-up-s-line"></i>
                                @else
                                    <i class="ri-arrow-down-s-line"></i>
                                @endif
                            @endif
                        </a>
                    </th>
                    <th scope="col">
                        <a href="" wire:click.prevent="setSortBy('empresa')">
                            Empresa
                            @if($sortBy === 'empresa')
                                @if($sortDirection === 'ASC')
                                    <i class="ri-arrow-up-s-line"></i>
                                @else
                                    <i class="ri-arrow-down-s-line"></i>
                                @endif
                            @endif
                        </a>
                    </th>
                    <th scope="col">
                        <a href="" wire:click.prevent="setSortBy('nome')">
                            Nome
                            @if($sortBy === 'nome')
                                @if($sortDirection === 'ASC')
                                    <i class="ri-arrow-up-s-line"></i>
                                @else
                                    <i class="ri-arrow-down-s-line"></i>
                                @endif
                            @endif
                        </a>
                    </th>
                    <th scope="col" style="width: 5%; white-space: nowrap;">Valor</th>
                    <th scope="col" style="width: 5%; white-space: nowrap;"></th>
                    <th scope="col" style="width: 5%; white-space: nowrap;"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($inscritos as $inscrito)
                    <tr wire:key="inscrito-{{ $inscrito->uid }}">
                        <td>{{ Carbon\Carbon::parse($inscrito->data_inscricao)->format('d/m/Y') }}</td>
                        <td class="text-truncate" style="max-width: 250px;">{{ $inscrito->empresa?->nome_razao ?? 'Individual' }}</td>
                        <td>{{ $inscrito->nome }}</td>
                        <td> {{ $inscrito->valor }} </td>
                        <td>
                            <span data-bs-toggle="tooltip" title="{{ $inscrito->is_pago ? 'Pago' : 'Aguardando Pagamento' }}">
                                <i class="{{ $inscrito->is_pago ? 'ri-money-dollar-circle-fill text-success' : 'ri-money-dollar-circle-fill text-warning' }}" style="font-size: 1.2rem"></i>
                            </span>
                            <span data-bs-toggle="tooltip" title="{{ $inscrito->is_certificado_emitido ? 'Certificado Enviado' : 'Certificado não enviado' }}">
                                <i class="{{ $inscrito->is_certificado_emitido ? 'ri-award-fill text-primary' : 'ri-award-line text-muted' }}" style="font-size: 1.2rem"></i>
                            </span>
                        </td>
                        <td>
                            <div class="dropdown">
                                <a href="#" role="button" id="dropdownMenuLink2" data-bs-toggle="dropdown"
                                    aria-expanded="false">
                                    <i class="ph-dots-three-outline-vertical" style="font-size: 1.5rem"
                                        data-bs-toggle="tooltip" data-bs-placement="top" title="Detalhes e edição"></i>
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink2">
                                    <li>
                                        <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="{{ '#inscritoModal' . $inscrito->uid }}">Editar</a>
                                    </li>
                                    <li>
                                        <button class="dropdown-item" wire:click="enviarDocs({{ $inscrito->id }})" wire:loading.attr="disabled">
                                            Enviar Docs
                                        </button>
                                    </li>
                                    <li>
                                        <button class="dropdown-item" wire:click="enviarCertificado({{ $inscrito->id }})" wire:loading.attr="disabled">
                                            Enviar Certificado
                                        </button>
                                    </li>
                                    <li>
                                        <x-painel.form-delete.delete route='cancela-inscricao' id="{{ $inscrito->uid }}" />
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    <x-painel.agendamento-cursos.modal-edita-participante :inscrito="$inscrito"/>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">Este agendamento não possui inscritos.</td>
                    </tr>
                @endforelse
                @if($inscritos->sum('valor') > 0)
                <tfoot>
                    <tr>
                        <td colspan="3"></td>
                        <td><strong>Total:</strong> {{ $inscritos->sum('valor') }} </td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
                @endif
            </tbody>
        </table>
    </div>
</div>
