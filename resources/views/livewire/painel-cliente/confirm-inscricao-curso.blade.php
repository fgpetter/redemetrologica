<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-9 col-xxl-9 border-bottom mb-3">
                <h4 class="mb-4">Confirme sua inscrição:</h4>
                {{-- Informações do curso --}}
                <x-painel.painel-cliente.dados-curso :curso="$curso" />
            </div>
        </div>
        @if ($showTipoInscricao) {{-- Exibe opções de inscrição --}}
            <div class="row mt-4">
                <div class="col-6">
                    <h5 class="fw-bold mb-3 text-start">Escolha uma das opções de inscrição para continuar:</h5>
                    <button wire:click="inscreverCNPJ"
                        class="btn btn-primary w-50 mb-3 d-flex align-items-center justify-content-center">
                        Inscrição pelo CNPJ <i class="bi bi-buildings"
                            style="font-size: 1.5rem; margin-left: 0.5rem;"></i>
                    </button>

                    <button wire:click="inscreverCPF"
                        class="btn btn-primary w-50 d-flex align-items-center justify-content-center">
                        Inscrição pelo CPF <i class="bi bi-person-lines-fill"
                            style="font-size: 1.5rem; margin-left: 0.5rem;"></i>
                    </button>
                </div>
            </div>
        @elseif ($tipoInscricao === 'CNPJ') {{-- Exibe informações para inscrição pelo CNPJ --}}
            @if ($showBuscaCnpj) {{-- Procurar por CNPJ --}}
                <div class="card px-3 py-3 mt-3 border">
                    <div class="row">
                        <div class="col-md-6 border-end pe-3">
                            <h5>Informe o CNPJ da empresa:</h5>
                            <p>Para prosseguir com a inscrição, é necessário informar um CNPJ para envio de nota Fiscal
                                e
                                Cobrança</p>
                            <div class="input-group">
                                <input type="text" id="cnpj" wire:model="BuscaCnpj" class="form-control"
                                    placeholder="CNPJ" x-mask="99.999.999/9999-99">
                                <button type="button" wire:click="ProcuraCnpj" class="btn btn-primary">Buscar</button>
                            </div>
                            @error('BuscaCnpj')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6 d-flex justify-content-end align-items-end">
                            <button class="btn btn-warning" wire:click="cancelarInscricao">ENCERRAR INSCRIÇÕES</button>
                        </div>
                    </div>
                </div>
            @endif
            @if ($showSalvarEmpresa) {{-- Formulário para salvar empresa --}}
                <form wire:submit.prevent="salvarEmpresa" class="mt-4">
                    <div class="card border overflow-hidden card-border-dark shadow-none">

                        <div class="card-header">
                            <h6 class="card-title mb-0">Complete os dados abaixo para emissão e envio de NF</h6>
                        </div>

                        <div class="card-body">
                            <div class="row g-3">

                                <div class="col-md-6">
                                    <x-forms.input-field wire:model="empresa.nome_razao" name="nome_razao"
                                        label="Razão Social" :required="true" />
                                    @error('empresa.nome_razao')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <x-forms.input-field wire:model="empresa.cpf_cnpj" name="cpf_cnpj" label="CNPJ"
                                        :readonly="true" />
                                    @error('empresa.cpf_cnpj')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <x-forms.input-field wire:model="empresa.telefone" name="telefone" label="Telefone"
                                        class="telefone" maxlength="15"
                                        x-mask:dynamic="$input.replace(/\D/g, '').length === 11 
                                                                    ? '(99) 99999-9999' 
                                                                    : '(99) 9999-9999'" />
                                    @error('empresa.telefone')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <x-forms.input-field wire:model="empresa.endereco_cobranca.email"
                                        name="cobranca_email" label="E-mail de Cobrança" type="email"
                                        :required="true" />
                                    @error('empresa.endereco_cobranca.email')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <x-forms.input-field wire:model="empresa.endereco_cobranca.cep" name="cobranca_cep"
                                        label="CEP" wire:blur="buscaCep" maxlength="9" x-mask="99999-999"
                                        :required="true" />
                                    @error('empresa.endereco_cobranca.cep')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="col-md-8">
                                    <x-forms.input-field wire:model="empresa.endereco_cobranca.endereco"
                                        name="cobranca_endereco" label="Endereço" :required="true" />
                                    @error('empresa.endereco_cobranca.endereco')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <x-forms.input-field wire:model="empresa.endereco_cobranca.complemento"
                                        name="cobranca_complemento" label="Complemento" />
                                    @error('empresa.endereco_cobranca.complemento')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <x-forms.input-field wire:model="empresa.endereco_cobranca.bairro"
                                        name="cobranca_bairro" label="Bairro" :required="true" />
                                    @error('empresa.endereco_cobranca.bairro')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <x-forms.input-field wire:model="empresa.endereco_cobranca.cidade"
                                        name="cobranca_cidade" label="Cidade" :required="true" />
                                    @error('empresa.endereco_cobranca.cidade')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="col-md-2">
                                    <x-forms.input-field wire:model="empresa.endereco_cobranca.uf" name="cobranca_uf"
                                        label="UF" maxlength="2" style="text-transform: uppercase;"
                                        :required="true" />
                                    @error('empresa.endereco_cobranca.uf')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="mt-4 d-flex justify-content-end gap-2">
                                <button type="submit" class="btn btn-success">
                                    Continuar
                                </button>
                                @if ($editandoEmpresa === false)
                                    <button type="button" class="btn btn-warning" wire:click="cancelarInscricao">
                                        Cancelar
                                    </button>
                                @endif
                            </div>

                        </div>
                    </div>
                </form>
            @endif
            <div class="row"> {{-- Exibe informações para inscrição --}}
                <div class="col-9">
                    @if ($showSalvarEmpresa === false && !empty($empresa) && isset($empresa['nome_razao'], $empresa['cpf_cnpj']))
                        <div class="card-header bg-light" style="min-height: 60px;">
                            <div class="d-flex justify-content-between align-items-center h-100">
                                <div>
                                    <strong>{{ $empresa['nome_razao'] }}</strong>
                                    <small class="text-muted ms-2">CNPJ: {{ $empresa['cpf_cnpj'] }}</small>
                                </div>
                                <div>
                                    <button class="btn btn-sm btn-outline-primary me-2" wire:click="editarEmpresa">
                                        <i class="ri-edit-line"></i>
                                        Editar
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="alert alert-secondary alert-dismissible bg-body-secondary fade show mt-3"
                            role="alert">
                            <strong>IMPORTANTE: </strong> <br>
                            <p>
                                Para <strong>inscrever-se</strong> e/ou convidar <strong> outras pessoas</strong> neste
                                curso, adicione os dados de e-mail e nome nos campos abaixo. <br>
                                As demais pessoas adicionadas nessa lista receberão um email com link para confirmarem
                                suas
                                inscrições. <br>
                            </p>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"
                                aria-label="Close"></button>
                        </div>
                        <form wire:submit.prevent="salvarInscricoes" id="form-convite">
                            @csrf
                            <input type="hidden" name="id_pessoa" value="{{ auth()->user()->pessoa->id }}">
                            <input type="hidden" name="id_curso" value="{{ $curso->id }}">
                            @foreach ($inscricoes as $index => $inscricao)
                                @if ($inscricao['email'] === auth()->user()->email && $inscricao['responsavel'] == 1)
                                    <div class ="mt-2">
                                        <strong class="card-title ">Confirme os dados para a sua inscrição:</strong>
                                    </div>
                                    <div class=" mt-2">
                                        <div class="row gx-0">
                                            <div class="col-10">
                                                <div class="card-body bg-light px-3 py-2">
                                                    <div class="row  mt-1 gx-1 align-items-center">
                                                        <div class="col-6">
                                                            <x-forms.input-field
                                                                wire:model.lazy="inscricoes.{{ $index }}.email"
                                                                name="email" label="Email" type="email" required
                                                                style="text-transform: lowercase;" />
                                                            @error('inscricoes.' . $index . '.email')
                                                                <span class="text-danger small">{{ $message }}</span>
                                                            @enderror
                                                        </div>
                                                        <div class="col-6">
                                                            <x-forms.input-field
                                                                wire:model.live="inscricoes.{{ $index }}.nome"
                                                                name="nome" label="Nome" required />
                                                            @error('inscricoes.' . $index . '.nome')
                                                                <span class="text-danger small">{{ $message }}</span>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                    <div class="row  mt-1 gx-1">
                                                        <div class="col-6">
                                                            <x-forms.input-field
                                                                wire:model.live="inscricoes.{{ $index }}.telefone"
                                                                name="telefone" label="Telefone" class="telefone"
                                                                required maxlength="15"
                                                                x-mask:dynamic="$input.replace(/\D/g, '').length === 11 
                                                                ? '(99) 99999-9999' 
                                                                : '(99) 9999-9999'" />
                                                            @error('inscricoes.' . $index . '.telefone')
                                                                <span class="text-danger small">{{ $message }}</span>
                                                            @enderror
                                                        </div>
                                                        <div class="col-6">
                                                            <x-forms.input-field
                                                                wire:model.live="inscricoes.{{ $index }}.cpf_cnpj"
                                                                name="cpf_cnpj" label="CPF" id="input-cpf"
                                                                x-mask="999.999.999-99" required />
                                                            @error('inscricoes.' . $index . '.cpf_cnpj')
                                                                <span class="text-danger small">{{ $message }}</span>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-2 d-flex gap-1 align-items-end p-1">
                                                @if ($loop->last)
                                                    <button type="button" class="btn btn-primary"
                                                        wire:click="adicionarInscricao">+</button>
                                                @endif

                                                @if (count($inscricoes) > 1)
                                                    <button type="button" class="btn btn-danger"
                                                        wire:click="removerInscricao({{ $index }})">-</button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    @if (
                                        $loop->first ||
                                            ($loop->index > 0 &&
                                                isset($inscricoes[$loop->index - 1]['responsavel']) &&
                                                $inscricoes[$loop->index - 1]['responsavel'] == 1))
                                        <h6 class="card-subtitle my-2 text-primary-emphasis">
                                            Adicionar inscrições:
                                        </h6>
                                    @endif
                                    <div class="row row-invite mt-1 gx-1">
                                        <div class="col-5">
                                            <input type="email" class="form-control"
                                                name="inscricoes[{{ $index }}][email]" placeholder="Email"
                                                wire:model.lazy="inscricoes.{{ $index }}.email" required
                                                style="text-transform: lowercase;">
                                            @error('inscricoes.' . $index . '.email')
                                                <span class="text-danger small">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="col-5">
                                            <input type="text" class="form-control"
                                                name="inscricoes[{{ $index }}][nome]" placeholder="Nome"
                                                wire:model.live="inscricoes.{{ $index }}.nome" required>
                                            @error('inscricoes.' . $index . '.nome')
                                                <span class="text-danger small">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="col-2">
                                            @if ($loop->last)
                                                <button type="button" class="btn btn-primary"
                                                    wire:click="adicionarInscricao">+</button>
                                            @endif
                                            @if (count($inscricoes) > 1)
                                                <button type="button" class="btn btn-danger"
                                                    wire:click="removerInscricao({{ $index }})">-</button>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                            <div class="mt-4 d-flex justify-content-end gap-2">
                                <button type="submit" class="btn btn-success">
                                    Concluir Inscrição
                                </button>
                                <button type="button" class="btn btn-warning" wire:click="cancelarInscricao">
                                    Cancelar
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        @elseif ($tipoInscricao === 'CPF'){{-- Exibe informações para inscrição pelo CPF --}}
            <div class="row">
                <div class="col-9">
                    {{-- <h4 class="m-4">Confirmação de Inscrição pelo CPF:</h4> --}}
                    <div class="alert alert-secondary alert-dismissible bg-body-secondary fade show mt-3"
                        role="alert">
                        <strong>IMPORTANTE: </strong> <br>
                        <p>
                            Confirme os dados abaixo para prosseguir com a inscrição. <br>
                            Caso necessário, você pode editar as informações antes de enviar.
                        </p>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"
                            aria-label="Close"></button>
                    </div>

                    <form wire:submit.prevent="salvarInscricoes" id="form-inscricao-cpf">
                        @csrf
                        <div class ="mt-2">
                            <strong class="card-title ">Confirme os dados para a sua inscrição:</strong>
                        </div>
                        <div class=" mt-2">
                            <div class="row gx-0">
                                <div class="col-10">
                                    <div class="card-body bg-light px-3 py-2">
                                        <div class="row  mt-1 gx-1 align-items-center">
                                            <div class="col-6">
                                                <x-forms.input-field wire:model.lazy="inscricoes.0.email"
                                                    name="email" label="Email" type="email" required
                                                    style="text-transform: lowercase;" />
                                                @error('inscricoes.0.email')
                                                    <span class="text-danger small">{{ $message }}</span>
                                                @enderror
                                            </div>
                                            <div class="col-6">
                                                <x-forms.input-field wire:model.live="inscricoes.0.nome"
                                                    name="nome" label="Nome" required />
                                                @error('inscricoes.0.nome')
                                                    <span class="text-danger small">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="row  mt-1 gx-1">
                                            <div class="col-6">
                                                <x-forms.input-field wire:model.live="inscricoes.0.telefone"
                                                    name="telefone" label="Telefone" class="telefone" required
                                                    maxlength="15"
                                                    x-mask:dynamic="$input.replace(/\D/g, '').length === 11 
                                                                ? '(99) 99999-9999' 
                                                                : '(99) 9999-9999'" />
                                                @error('inscricoes.0.telefone')
                                                    <span class="text-danger small">{{ $message }}</span>
                                                @enderror
                                            </div>
                                            <div class="col-6">
                                                <x-forms.input-field wire:model.live="inscricoes.0.cpf_cnpj"
                                                    name="cpf_cnpj" label="CPF" id="input-cpf"
                                                    x-mask="999.999.999-99" required />
                                                @error('inscricoes.0.cpf_cnpj')
                                                    <span class="text-danger small">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 d-flex justify-content-end gap-2">
                            <button type="submit" class="btn btn-success">
                                Concluir Inscrição
                            </button>
                            <button type="button" class="btn btn-warning" wire:click="cancelarInscricao">
                                Cancelar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>
</div>
