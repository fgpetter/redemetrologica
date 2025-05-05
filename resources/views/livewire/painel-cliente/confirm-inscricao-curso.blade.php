<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-12 col-xxl-12 border-bottom">
                <h4 class="mb-4">Confirme sua inscrição:</h4>
                {{-- Informações do curso --}}
                <x-painel.painel-cliente.dados-curso :curso="$curso" />
            </div>
        </div>

        @if ($showTipoInscricao)
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
        @elseif ($tipoInscricao === 'CNPJ')
            {{-- Exibe informações da empresa para inscrição pelo CNPJ --}}
            <div class="row">
                <div class="col-12">
                    <h4 class="m-4">Confirmação de Inscrição pelo CNPJ:</h4>
                    @if ($showSalvarEmpresa === false && !empty($empresa) && isset($empresa['nome_razao'], $empresa['cpf_cnpj']))
                        <div class="card-header bg-light" style="min-height: 60px;">
                            <div class="d-flex justify-content-between align-items-center h-100">
                                <div>
                                    <strong>{{ $empresa['nome_razao'] }}</strong>
                                    <small class="text-muted ms-2">CNPJ: {{ $empresa['cpf_cnpj'] }}</small>
                                </div>
                                <div>
                                    <button class="btn btn-sm btn-outline-primary me-2"
                                        wire:click.prevent="$set('showSalvarEmpresa', true)">
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
                                Para inscrever <strong>outras pessoas</strong> nesse curso, adicione os dados de nome e
                                e-mail nos campos abaixo. <br>
                                As pessoas adicionadas nessa lista receberão um email com link para confirmarem suas
                                inscrições. <br>
                            </p>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"
                                aria-label="Close"></button>
                        </div>

                        <h6 class="card-subtitle my-3 text-primary-emphasis">Adicionar outros participantes da minha
                            empresa:</h6>
                        <form wire:submit.prevent="submit" id="form-convite">
                            @csrf
                            <input type="hidden" name="id_pessoa" value="{{ auth()->user()->pessoa->id }}">
                            <input type="hidden" name="id_curso" value="{{ $curso->id }}">

                            @foreach ($inscricoes as $index => $inscricao)
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
                                @if ($inscricao['email'] === auth()->user()->email)
                                    <div class="row row-invite mt-1 gx-1">
                                        <div class="col-5">
                                            <input type="text" class="form-control"
                                                name="inscricoes[{{ $index }}][telefone]" placeholder="Telefone"
                                                wire:model.live="inscricoes.{{ $index }}.telefone">
                                        </div>
                                        <div class="col-5">
                                            <input type="text" class="form-control"
                                                name="inscricoes[{{ $index }}][cpf_cnpj]" placeholder="CPF/CNPJ"
                                                wire:model.live="inscricoes.{{ $index }}.cpf_cnpj">
                                        </div>
                                    </div>
                                @endif
                            @endforeach

                            <div class="mt-4 d-flex justify-content-end gap-2">
                                <button type="submit" class="btn btn-success">
                                    Enviar Convites
                                </button>
                                <button type="button" class="btn btn-warning"
                                    wire:click="$set('inscricoes', [['nome' => '', 'email' => '']])">
                                    Cancelar
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        @elseif ($tipoInscricao === 'CPF')
            {{-- Exibe informações para inscrição pelo CPF --}}
            <div class="row">
                <div class="col-12">
                    <h4 class="m-4">Confirmação de Inscrição pelo CPF:</h4>
                    <div class="alert alert-secondary alert-dismissible bg-body-secondary fade show mt-3"
                        role="alert">
                        <strong>IMPORTANTE: </strong> <br>
                        <p>
                            Confirme os dados abaixo para prosseguir com a inscrição. <br>
                            Caso necessário, você pode editar as informações antes de enviar.
                        </p>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>

                    <form wire:submit.prevent="submit" id="form-inscricao-cpf">
                        @csrf
                        <div class="row row-invite mt-1 gx-1">
                            <div class="col-5">
                                <input type="email" class="form-control" name="inscricoes[0][email]"
                                    placeholder="Email" wire:model.lazy="inscricoes.0.email" required
                                    style="text-transform: lowercase;">
                                @error('inscricoes.0.email')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-5">
                                <input type="text" class="form-control" name="inscricoes[0][nome]"
                                    placeholder="Nome" wire:model.live="inscricoes.0.nome" required>
                                @error('inscricoes.0.nome')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="row row-invite mt-1 gx-1">
                            <div class="col-5">
                                <input type="text" class="form-control" name="inscricoes[0][telefone]"
                                    placeholder="Telefone" wire:model.live="inscricoes.0.telefone">
                            </div>
                            <div class="col-5">
                                <input type="text" class="form-control" name="inscricoes[0][cpf_cnpj]"
                                    placeholder="CPF" wire:model.live="inscricoes.0.cpf_cnpj">
                            </div>
                        </div>
                        <div class="mt-4 d-flex justify-content-end gap-2">
                            <button type="submit" class="btn btn-success">
                                Confirmar Inscrição
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
        @if ($showBuscaCnpj)
            <div class="card px-3 py-3 mt-3 border">
                <div class="row">
                    <div class="col-md-6 border-end pe-3">
                        <h5>{{ $empresa_inscrita && $empresa_inscrita->isNotEmpty()
                            ? 'Informe outro CNPJ caso queira cadastrar outra empresa para cobrança'
                            : 'Informe o CNPJ para continuar' }}
                        </h5>
                        <p>Para prosseguir com a inscrição, é necessário informar um CNPJ para envio de nota Fiscal e
                            Cobrança</p>
                        <div class="input-group">
                            <input type="text" id="cnpj" wire:model="BuscaCnpj" class="form-control"
                                placeholder="CNPJ">
                            <button type="button" wire:click="ProcuraCnpj" class="btn btn-primary">Buscar</button>
                        </div>
                        @error('BuscaCnpj')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-6 d-flex justify-content-end align-items-end">
                        <button class="btn btn-warning" wire:click="encerrarInscricoes">ENCERRAR INSCRIÇÕES</button>
                    </div>
                </div>
            </div>
        @endif
        @if ($showSalvarEmpresa)
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
                            <button type="button" class="btn btn-warning"
                                wire:click="$set('showSalvarEmpresa', false)">
                                Cancelar
                            </button>
                        </div>

                    </div>
                </div>
            </form>
        @endif
    </div>
</div>
