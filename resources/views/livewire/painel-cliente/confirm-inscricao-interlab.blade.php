<div class="col-12">
    <!-- Cabeçalho do interlaboratorial -->
    <div class="card px-3 border border-primary">
        <h5 class="card-subtitle mt-3 mb-2 text-primary-emphasis">Dados do interlaboratorial:</h5>
        <p class="pb-3">
            <strong>Interlaboratorial:</strong> {{ $interlab->interlab->nome }} <br>
            <strong>Agenda:</strong> de {{ \Carbon\Carbon::parse($interlab->data_inicio)->format('d/m/Y') }} a
            {{ \Carbon\Carbon::parse($interlab->data_fim)->format('d/m/Y') }} <br>
        </p>
    </div>
    <!-- Listagem de empresas com inscrições existentes -->
    @if ($empresas_inscritas && $empresas_inscritas->isNotEmpty())
        @if ($interlab->instrucoes_inscricao)
            <blockquote class="blockquote custom-blockquote blockquote-outline blockquote-primary rounded mb-5">
                <i class="ri-information-fill text-primary fs-5"></i> Importante:
                <p class="mb-2 text-black fs-6 fs-sm-5">{!! nl2br($interlab->instrucoes_inscricao) !!}</p>
            </blockquote>
        @endif
        <div class="mb-5 border p-3 rounded" id="step-4">
            <h5 class="mb-3 text-primary">Laboratórios Inscritos:</h5>
            @foreach ($empresas_inscritas as $empresa_inscrita)
                <div class="card mb-3" wire:key="empresa-{{ $empresa_inscrita->id }}">
                    @if ($empresaEditadaId !== $empresa_inscrita->id)
                        <div class="card-header bg-light" style="min-height: 60px;">
                            <div class="d-flex justify-content-between align-items-center h-100">
                                <div>
                                    <strong>{{ $empresa_inscrita->nome_razao }}</strong>
                                    <small class="text-muted ms-2">CNPJ: {{ $empresa_inscrita->cpf_cnpj }}</small>
                                </div>
                                <!-- Botão de editar empresa -->
                                @if (
                                    !$showSalvarEmpresa &&
                                        !$showInscreveLab &&
                                        $empresaEditadaId === null &&
                                        $laboratorioEditadoId === null &&
                                        $novaInscricaoEmpresaId === null)
                                    <div>
                                        <button class="btn btn-sm btn-outline-primary me-2"
                                            wire:click.prevent="{{ $empresaEditadaId === $empresa_inscrita->id
                                                ? '$set(\'empresaEditadaId\', null)'
                                                : 'editEmpresa(' . $empresa_inscrita->id . ')' }}">
                                            <i class="ri-edit-line"></i>
                                            {{ $empresaEditadaId === $empresa_inscrita->id ? 'Cancelar' : 'Editar' }}
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                    {{-- editando empresa ja inscrita --}}
                    @if ($empresaEditadaId === $empresa_inscrita->id)
                        <div class="card-body bg-light">
                            <form wire:submit.prevent="salvarEmpresa">
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <x-forms.input-field wire:model="empresa.nome_razao" name="nome_razao"
                                            label="Razão Social" :required="true" />
                                        @error('empresa.nome_razao')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <x-forms.input-field wire:model="empresa.cpf_cnpj" name="cpf_cnpj"
                                            label="CNPJ" :readonly="true" />
                                        @error('empresa.cpf_cnpj')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <x-forms.input-field wire:model="empresa.telefone" name="telefone"
                                            label="Telefone" class="telefone" maxlength="15"
                                            x-mask:dynamic="$input.replace(/\D/g, '').length === 11 
                                                ? '(99) 99999-9999' 
                                                : '(99) 9999-9999'" />
                                        @error('empresa.telefone')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <x-forms.input-field wire:model="empresa.endereco_cobranca.email"
                                            name="cobranca_email" label="E-mail de Cobrança" type="email"
                                            :required="true" />
                                        @error('empresa.endereco_cobranca.email')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-4">
                                        <x-forms.input-field wire:model="empresa.endereco_cobranca.cep"
                                            name="cobranca_cep" label="CEP" wire:blur="buscaCep('cobranca')"
                                            maxlength="9" x-mask="99999-999" :required="true" />
                                        @error('empresa.endereco_cobranca.cep')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-8">
                                        <x-forms.input-field wire:model="empresa.endereco_cobranca.endereco"
                                            name="cobranca_endereco" label="Endereço" :required="true" />
                                        @error('empresa.endereco_cobranca.endereco')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <x-forms.input-field wire:model="empresa.endereco_cobranca.complemento"
                                            name="cobranca_complemento" label="Complemento" />
                                        @error('empresa.endereco_cobranca.complemento')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <x-forms.input-field wire:model="empresa.endereco_cobranca.bairro"
                                            name="cobranca_bairro" label="Bairro" :required="true" />
                                        @error('empresa.endereco_cobranca.bairro')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <x-forms.input-field wire:model="empresa.endereco_cobranca.cidade"
                                            name="cobranca_cidade" label="Cidade" :required="true" />
                                        @error('empresa.endereco_cobranca.cidade')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-2">
                                        <x-forms.input-field wire:model="empresa.endereco_cobranca.uf"
                                            name="cobranca_uf" label="UF" maxlength="2"
                                            style="text-transform: uppercase;" :required="true" />
                                        @error('empresa.endereco_cobranca.uf')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="mt-4 d-flex justify-content-end gap-2">
                                    <button type="submit" class="btn btn-success">
                                        Salvar Alterações
                                    </button>
                                    <button type="button" class="btn btn-warning"
                                        wire:click="$set('empresaEditadaId', null)">
                                        Cancelar
                                    </button>
                                </div>
                            </form>
                        </div>
                    @else
                        <div class="card-body">
                            {{-- laboratorios inscritos --}}
                            @foreach ($inscritos->where('empresa_id', $empresa_inscrita->id) as $inscrito)
                                @if ($laboratorioEditadoId !== $inscrito->laboratorio->id)
                                    <div class="mb-3 p-2 border rounded" wire:key="inscrito-{{ $inscrito->id }}">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <small class="text-muted">Laboratório:</small>
                                                <p class="mb-0">{{ $inscrito->laboratorio->nome }}</p>
                                            </div>
                                            <div class="col-md-3">
                                                <small class="text-muted">Responsável Técnico:</small>
                                                <p class="mb-0">{{ $inscrito->laboratorio->responsavel_tecnico }}
                                                </p>
                                            </div>
                                            <div class="col-md-3">
                                                <small class="text-muted">Endereço:</small>
                                                <p class="mb-0">
                                                    {{ $inscrito->laboratorio->endereco->endereco }},
                                                    {{ $inscrito->laboratorio->endereco->cidade }}/{{ $inscrito->laboratorio->endereco->uf }}
                                                </p>
                                            </div>
                                            <!-- Botão de editar laboratório -->
                                            @if (
                                                !$showSalvarEmpresa &&
                                                    !$showInscreveLab &&
                                                    $empresaEditadaId === null &&
                                                    $laboratorioEditadoId === null &&
                                                    $novaInscricaoEmpresaId === null)
                                                <div class="mt-2 text-end col-3">
                                                    <button class="btn btn-sm btn-outline-warning"
                                                        wire:click.prevent="{{ $laboratorioEditadoId === $inscrito->laboratorio->id
                                                            ? '$set(\'laboratorioEditadoId\', null)'
                                                            : 'editLaboratorio(' . $inscrito->id . ')' }}">
                                                        <i class="ri-edit-line"></i>
                                                        {{ $laboratorioEditadoId === $inscrito->laboratorio->id ? 'Cancelar' : 'Editar' }}
                                                    </button>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                                {{-- editando laboratorio --}}
                                @if ($laboratorioEditadoId === $inscrito->laboratorio->id)
                                    <div>
                                        <form wire:submit.prevent="InscreveLab">
                                            <div class="card border overflow-hidden">
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-12 col-xl-6">
                                                            <x-forms.input-field wire:model="laboratorio.nome"
                                                                name="laboratorio.nome" label="Laboratório"
                                                                required />
                                                            @error('laboratorio.nome')
                                                                <div class="text-danger">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                        <div class="col-12 col-xl-6">
                                                            <x-forms.input-field
                                                                wire:model="laboratorio.responsavel_tecnico"
                                                                name="laboratorio.responsavel_tecnico"
                                                                label="Responsável Técnico" required />
                                                            @error('laboratorio.responsavel_tecnico')
                                                                <div class="text-danger">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>

                                                    <div class="row mb-2">
                                                        <div class="col-12 col-sm-6">
                                                            <x-forms.input-field wire:model="laboratorio.telefone"
                                                                name="laboratorio.telefone" label="Telefone"
                                                                class="telefone" maxlength="15"
                                                                x-mask:dynamic="$input.replace(/\D/g, '').length === 11 
                                                                ? '(99) 99999-9999' 
                                                                : '(99) 9999-9999'"
                                                                wire:ignore />
                                                            @error('laboratorio.telefone')
                                                                <div class="text-danger">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                        <div class="col-12 col-sm-6">
                                                            <x-forms.input-field wire:model="laboratorio.email"
                                                                name="laboratorio.email" type="email"
                                                                label="E-mail" :required="true" />
                                                            @error('laboratorio.email')
                                                                <div class="text-danger">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>

                                                    <div class="row my-3 gy-2">
                                                        <div class="col-5 col-sm-4">
                                                            <x-forms.input-field wire:model="laboratorio.endereco.cep"
                                                                name="laboratorio.endereco.cep" label="CEP"
                                                                class="cep" wire:blur="buscaCep('laboratorio')"
                                                                maxlength="9" x-mask="99999-999" required />
                                                            @error('laboratorio.endereco.cep')
                                                                <div class="text-danger">{{ $message }}
                                                                </div>
                                                            @enderror
                                                        </div>
                                                        <div class="col-12 col-sm-8">
                                                            <x-forms.input-field
                                                                wire:model="laboratorio.endereco.endereco"
                                                                name="laboratorio.endereco.endereco" label="Endereço"
                                                                required />
                                                            @error('laboratorio.endereco.endereco')
                                                                <div class="text-danger">{{ $message }}
                                                                </div>
                                                            @enderror
                                                        </div>
                                                        <div class="col-12 col-sm-6">
                                                            <x-forms.input-field
                                                                wire:model="laboratorio.endereco.complemento"
                                                                name="laboratorio.endereco.complemento"
                                                                label="Complemento" />
                                                            @error('laboratorio.endereco.complemento')
                                                                <div class="text-danger">{{ $message }}
                                                                </div>
                                                            @enderror
                                                        </div>
                                                        <div class="col-12 col-sm-6">
                                                            <x-forms.input-field
                                                                wire:model="laboratorio.endereco.bairro"
                                                                name="laboratorio.endereco.bairro" label="Bairro"
                                                                required />
                                                            @error('laboratorio.endereco.bairro')
                                                                <div class="text-danger">{{ $message }}
                                                                </div>
                                                            @enderror
                                                        </div>
                                                        <div class="col-12 col-sm-6">
                                                            <x-forms.input-field
                                                                wire:model="laboratorio.endereco.cidade"
                                                                name="laboratorio.endereco.cidade" label="Cidade"
                                                                required />
                                                            @error('laboratorio.endereco.cidade')
                                                                <div class="text-danger">{{ $message }}
                                                                </div>
                                                            @enderror
                                                        </div>
                                                        <div class="col-md-2">
                                                            <x-forms.input-field wire:model="laboratorio.endereco.uf"
                                                                name="laboratorio.endereco.uf" label="UF"
                                                                maxlength="2" style="text-transform: uppercase;"
                                                                :required="true" />
                                                            @error('laboratorio.endereco.uf')
                                                                <div class="text-danger">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>

                                                    <x-forms.input-textarea wire:model="informacoes_inscricao"
                                                        name="informacoes_inscricao" label="Informações da inscrição"
                                                        sublabel="Informe aqui quais rodadas, blocos ou parâmetros esse laboratório irá participar."
                                                        required>
                                                        {{ old('informacoes_inscricao') ?? null }}
                                                    </x-forms.input-textarea>
                                                    @error('informacoes_inscricao')
                                                        <div class="text-danger">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="row  mt-4 mb-4 d-flex justify-content-end gap-2">
                                                <div class="col-md-auto">
                                                    <button type="submit" class="btn btn-success">
                                                        Salvar Alterações
                                                    </button>
                                                </div>
                                                <div class="col-md-auto">
                                                    <button type="button" class="btn btn-warning"
                                                        wire:click="cancelEdit">
                                                        Cancelar
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                @endif
                            @endforeach
                            <!-- Botão de adicionar novo laboratório -->
                            @if (
                                !$showSalvarEmpresa &&
                                    !$showInscreveLab &&
                                    $empresaEditadaId === null &&
                                    $laboratorioEditadoId === null &&
                                    $novaInscricaoEmpresaId === null)
                                <div class="mt-3 text-end">
                                    <button class="btn btn-sm btn-success" id="step-5"
                                        wire:click.prevent="novoLaboratorio({{ $empresa_inscrita->id }})">
                                        <i class="ri-add-line"></i> Adicionar Novo Laboratório
                                    </button>
                                </div>
                            @endif
                            <!-- Formulário de novo laboratório em empresa já inscrita -->
                            @if ($novaInscricaoEmpresaId === $empresa_inscrita->id)
                                <div class="mt-3 border-top pt-3">
                                    <form wire:submit.prevent="InscreveLab" id="confirma-inscricao-interlab"
                                        class="mt-4">
                                        <div class="card border overflow-hidden card-border shadow-none">
                                            <div class="card-header">
                                                <h6 class="card-title mb-0">Informe os dados do Laboratório para envio
                                                    de
                                                    amostras:</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-12 col-xl-6">
                                                        <x-forms.input-field wire:model="laboratorio.nome"
                                                            name="laboratorio.nome" label="Laboratório"
                                                            class="mb-2" required />
                                                        @error('laboratorio.nome')
                                                            <div class="text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                    <div class="col-12 col-xl-6">
                                                        <x-forms.input-field
                                                            wire:model="laboratorio.responsavel_tecnico"
                                                            name="laboratorio.responsavel_tecnico"
                                                            label="Responsável Técnico" class="mb-2" required />
                                                        @error('laboratorio.responsavel_tecnico')
                                                            <div class="text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <div class="row mb-2">
                                                    <div class="col-12 col-sm-6">
                                                        <x-forms.input-field wire:model="laboratorio.telefone"
                                                            name="laboratorio.telefone" label="Telefone"
                                                            class="telefone" maxlength="15"
                                                            x-mask:dynamic="$input.replace(/\D/g, '').length === 11 
                                                                ? '(99) 99999-9999' 
                                                                : '(99) 9999-9999'"
                                                            wire:ignore />
                                                        @error('laboratorio.telefone')
                                                            <div class="text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                    <div class="col-12 col-sm-6">
                                                        <x-forms.input-field wire:model="laboratorio.email"
                                                            name="laboratorio.email" type="email" label="E-mail"
                                                            required />
                                                        @error('laboratorio.email')
                                                            <div class="text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <div class="row my-3 gy-2">
                                                    <div class="col-5 col-sm-4">
                                                        <x-forms.input-field wire:model="laboratorio.endereco.cep"
                                                            name="laboratorio.endereco.cep" label="CEP"
                                                            class="cep" wire:blur="buscaCep('laboratorio')"
                                                            maxlength="9" x-mask="99999-999" required />
                                                        @error('laboratorio.endereco.cep')
                                                            <div class="text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>

                                                    <div class="col-12 col-sm-8">
                                                        <x-forms.input-field wire:model="laboratorio.endereco.endereco"
                                                            name="laboratorio.endereco.endereco"
                                                            label="Endereço com número"
                                                            placeholder="Ex. Av. Brasil, 1234" required />
                                                        @error('laboratorio.endereco.endereco')
                                                            <div class="text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>

                                                    <div class="col-12 col-sm-6">
                                                        <x-forms.input-field
                                                            wire:model="laboratorio.endereco.complemento"
                                                            name="laboratorio.endereco.complemento"
                                                            label="Complemento" placeholder="Ex. Sala 101" />
                                                        @error('laboratorio.endereco.complemento')
                                                            <div class="text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>

                                                    <div class="col-12 col-sm-6">
                                                        <x-forms.input-field wire:model="laboratorio.endereco.bairro"
                                                            name="laboratorio.endereco.bairro" label="Bairro"
                                                            required />
                                                        @error('laboratorio.endereco.bairro')
                                                            <div class="text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>

                                                    <div class="col-12 col-sm-6">
                                                        <x-forms.input-field wire:model="laboratorio.endereco.cidade"
                                                            name="laboratorio.endereco.cidade" label="Cidade"
                                                            required />
                                                        @error('laboratorio.endereco.cidade')
                                                            <div class="text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>

                                                    <div class="col-md-2">
                                                        <x-forms.input-field wire:model="laboratorio.endereco.uf"
                                                            name="laboratorio.endereco.uf" label="UF"
                                                            maxlength="2" style="text-transform: uppercase;"
                                                            :required="true" />
                                                        @error('laboratorio.endereco.uf')
                                                            <div class="text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <x-forms.input-textarea wire:model="informacoes_inscricao"
                                                    name="informacoes_inscricao" label="Informações da inscrição"
                                                    sublabel="Informe aqui quais rodadas, blocos ou parâmetros esse laboratório irá participar."
                                                    required>
                                                    {{ old('informacoes_inscricao') ?? null }}
                                                </x-forms.input-textarea>
                                                @error('informacoes_inscricao')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div>
                                            <div class="row mt-3 d-flex justify-content-end gap-2">
                                                <div class="col-md-auto">
                                                    <button type="submit" class="btn btn-success">
                                                        Salvar Novo Laboratório
                                                    </button>
                                                </div>
                                                <div class="col-md-auto">
                                                    <button type="button" class="btn btn-warning"
                                                        wire:click="$set('novaInscricaoEmpresaId', null)">
                                                        Cancelar
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
    <!-- Formulário de busca CNPJ -->
    @if (
        !$showSalvarEmpresa &&
            !$showInscreveLab &&
            $empresaEditadaId === null &&
            $laboratorioEditadoId === null &&
            $novaInscricaoEmpresaId === null)
        <div class="card px-3 py-3 border">
            <div class="row">
                <div class="col-md-6 border-end pe-3" id="step-6">
                    <h5>{{ $empresas_inscritas && $empresas_inscritas->isNotEmpty()
                        ? 'Informe outro CNPJ caso queira cadastrar outra empresa para cobrança'
                        : 'Informe o CNPJ para continuar' }}
                    </h5>
                    <p>Para prosseguir com a inscrição, é necessário informar um CNPJ para envio de nota Fiscal e
                        Cobrança</p>
                    <div class="input-group" id="step-1">
                        <input type="text" id="cnpj" wire:model="BuscaCnpj" class="form-control"
                            placeholder="CNPJ">
                        <button type="button" wire:click="ProcuraCnpj" class="btn btn-primary">Buscar</button>
                    </div>
                    @error('BuscaCnpj')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="col-md-6 d-flex justify-content-end align-items-end" >
                    <button class="btn btn-warning" wire:click="encerrarInscricoes" id="step-7" > ENCERRAR
                        INSCRIÇÕES</button>
                </div>
            </div>
        </div>
    @endif
    <!-- Formulário de edição/cadastro de NOVA empresa -->
    @if ($showSalvarEmpresa)
        @if (!$empresas_inscritas || $empresas_inscritas->isEmpty())
            @if ($interlab->instrucoes_inscricao)
                <blockquote class="blockquote custom-blockquote blockquote-outline blockquote-primary rounded mb-5">
                    <i class="ri-information-fill text-primary fs-5"></i> Importante:
                    <p class="mb-2 text-black fs-6 fs-sm-5">{!! nl2br($interlab->instrucoes_inscricao) !!}</p>
                </blockquote>
            @endif
        @endif

        <form wire:submit.prevent="salvarEmpresa" class="mt-4">
            <div class="card border overflow-hidden card-border-dark shadow-none" id="step-2">
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
                                x-mask:dynamic="$input.replace(/\D/g, '').length === 11 ? '(99) 99999-9999' : '(99) 9999-9999'" />
                            @error('empresa.telefone')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <x-forms.input-field wire:model="empresa.endereco_cobranca.email" name="cobranca_email"
                                label="E-mail de Cobrança" type="email" :required="true" />
                            @error('empresa.endereco_cobranca.email')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <x-forms.input-field wire:model="empresa.endereco_cobranca.cep" name="cobranca_cep"
                                label="CEP" wire:blur="buscaCep('cobranca')" maxlength="9" x-mask="99999-999"
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
                            <x-forms.input-field wire:model="empresa.endereco_cobranca.bairro" name="cobranca_bairro"
                                label="Bairro" :required="true" />
                            @error('empresa.endereco_cobranca.bairro')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <x-forms.input-field wire:model="empresa.endereco_cobranca.cidade" name="cobranca_cidade"
                                label="Cidade" :required="true" />
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
                        <button type="submit" class="btn btn-success">Continuar</button>
                        <button type="button" class="btn btn-warning"
                            wire:click="$set('showSalvarEmpresa', false)">Cancelar</button>
                    </div>
                </div>
            </div>
        </form>
    @endif
    <!-- Formulário de edição/cadastro de laboratório -->
    @if ($showInscreveLab)
        @if (!$empresas_inscritas || $empresas_inscritas->isEmpty())
            @if ($interlab->instrucoes_inscricao)
                <blockquote class="blockquote custom-blockquote blockquote-outline blockquote-primary rounded mb-5">
                    <i class="ri-information-fill text-primary fs-5"></i> Importante:
                    <p class="mb-2 text-black fs-6 fs-sm-5">{!! nl2br($interlab->instrucoes_inscricao) !!}</p>
                </blockquote>
            @endif
        @endif
        <form wire:submit.prevent="InscreveLab" id="confirma-inscricao-interlab" class="mt-4">
            <div class="card mb-3">
                <div class="card-header bg-light" style="min-height: 60px;">
                    <div class="d-flex justify-content-between align-items-center h-100">
                        <div>
                            <strong>{{ $empresa['nome_razao'] }}</strong>
                            <small class="text-muted ms-2">CNPJ: {{ $empresa['cpf_cnpj'] }}</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border overflow-hidden card-border-dark shadow-none" id="step-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">Informe os dados do Laboratório para envio de amostras:</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12 col-xl-6">
                            <x-forms.input-field wire:model="laboratorio.nome" name="laboratorio.nome"
                                label="Laboratório" class="mb-2" required />
                            @error('laboratorio.nome')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12 col-xl-6">
                            <x-forms.input-field wire:model="laboratorio.responsavel_tecnico"
                                name="laboratorio.responsavel_tecnico" label="Responsável Técnico" class="mb-2"
                                required />
                            @error('laboratorio.responsavel_tecnico')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-12 col-sm-6">
                            <x-forms.input-field wire:model="laboratorio.telefone" name="laboratorio.telefone"
                                label="Telefone" class="telefone" maxlength="15"
                                x-mask:dynamic="$input.replace(/\D/g, '').length === 11 ? '(99) 99999-9999' : '(99) 9999-9999'" />
                            @error('laboratorio.telefone')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12 col-sm-6">
                            <x-forms.input-field wire:model="laboratorio.email" name="laboratorio.email"
                                type="email" label="E-mail" required />
                            @error('laboratorio.email')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="row my-3 gy-2">
                        <div class="col-5 col-sm-4">
                            <x-forms.input-field wire:model="laboratorio.endereco.cep" name="laboratorio.endereco.cep"
                                label="CEP" class="cep" wire:blur="buscaCep('laboratorio')" maxlength="9"
                                x-mask="99999-999" required />
                            @error('laboratorio.endereco.cep')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12 col-sm-8">
                            <x-forms.input-field wire:model="laboratorio.endereco.endereco"
                                name="laboratorio.endereco.endereco" label="Endereço com número"
                                placeholder="Ex. Av. Brasil, 1234" required />
                            @error('laboratorio.endereco.endereco')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12 col-sm-6">
                            <x-forms.input-field wire:model="laboratorio.endereco.complemento"
                                name="laboratorio.endereco.complemento" label="Complemento"
                                placeholder="Ex. Sala 101" />
                            @error('laboratorio.endereco.complemento')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12 col-sm-6">
                            <x-forms.input-field wire:model="laboratorio.endereco.bairro"
                                name="laboratorio.endereco.bairro" label="Bairro" required />
                            @error('laboratorio.endereco.bairro')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12 col-sm-6">
                            <x-forms.input-field wire:model="laboratorio.endereco.cidade"
                                name="laboratorio.endereco.cidade" label="Cidade" required />
                            @error('laboratorio.endereco.cidade')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-2">
                            <x-forms.input-field wire:model="laboratorio.endereco.uf" name="laboratorio.endereco.uf"
                                label="UF" maxlength="2" style="text-transform: uppercase;" required />
                            @error('laboratorio.endereco.uf')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <x-forms.input-textarea wire:model="informacoes_inscricao" name="informacoes_inscricao"
                        label="Informações da inscrição"
                        sublabel="Informe aqui quais rodadas, blocos ou parâmetros esse laboratório irá participar."
                        required>
                        {{ old('informacoes_inscricao') ?? null }}
                    </x-forms.input-textarea>
                    @error('informacoes_inscricao')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div>
                    <div class="row m-3 d-flex justify-content-end gap-2">
                        <div class="col-md-auto">
                            <button type="submit" class="btn btn-success">Salvar Laboratório</button>
                        </div>
                        <div class="col-md-auto">
                            <button class="btn btn-warning" type="button"
                                wire:click="$set('showInscreveLab', false)">CANCELAR</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    @endif
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (localStorage.getItem('tourDone') === 'true') return;

        const createDriver = window.driver.js.driver;

        function createDriver1() {
            return createDriver({
                steps: [{
                    element: '#step-1',
                    popover: {
                        title: 'CNPJ da Empresa',
                        description: 'Informe o CNPJ corretamente.',
                        side: 'top',
                        align: 'start',
                    }
                }],
                showButtons: [close],
            });
        }

        function createDriver2() {
            return createDriver({
                steps: [{
                    element: '#step-2',
                    popover: {
                        title: 'Salvar empresa',
                        description: 'Preencha os dados para inscrição no laboratório.',
                        side: 'top',
                        align: 'start',
                    }
                }],
                showButtons: [close],
            });
        }

        function createDriver3() {
            return createDriver({
                steps: [{
                    element: '#step-3',
                    popover: {
                        title: 'Salvar laboratório',
                        description: 'Preencha os dados para inscrição no laboratório.',
                        side: 'top',
                        align: 'start',
                    }
                }],
                showButtons: [close],
            });
        }

        function createDriver4() {
            const driver = createDriver({
                steps: [{
                        element: '#step-4',
                        popover: {
                            title: 'Conferir inscrição',
                            description: 'Aqui você pode conferir as inscrições feitas e editar os dados da empresa e dos laboratórios.',
                            side: 'top',
                            align: 'start',
                        }
                    },
                    {
                        element: '#step-5',
                        popover: {
                            title: 'Adicionar novo laboratório',
                            description: 'Aqui você pode adicionar um novo laboratório à inscrição.',
                            side: 'top',
                            align: 'start',
                        }
                    },
                    {
                        element: '#step-6',
                        popover: {
                            title: 'Incluir nova empresa',
                            description: 'Aqui você pode cadastrar uma nova empresa para a inscrição.',
                            side: 'top',
                            align: 'start',
                        }
                    },
                    {
                        element: '#step-7',
                        popover: {
                            title: 'Encerrar inscrições',
                            description: 'Clique aqui quando desejar encerrar as inscrições.',
                            side: 'top',
                            align: 'start',
                        }
                    }
                ],
                showButtons: ['next'],
                onPopoverRender: (popover, {
                    currentStep
                }) => {


                        const disableButton = document.createElement("button");
                        disableButton.innerText = "Não mostrar novamente";
                        popover.footerButtons.appendChild(disableButton);
                        disableButton.addEventListener("click", () => {
                            localStorage.setItem('tourDone', 'true');
                            driver.destroy(); // encerra o tour
                        });
                    
                }
            });

            return driver;
        }




        // Escutando os eventos Livewire emitidos
        window.addEventListener('start-tour-1', () => {

            const driver = createDriver1();
            setTimeout(() => driver.drive(), 500);

        });

        window.addEventListener('start-tour-2', () => {

            const driver = createDriver2();
            setTimeout(() => driver.drive(), 100);

        });

        window.addEventListener('start-tour-3', () => {

            const driver = createDriver3();
            setTimeout(() => driver.drive(), 100);

        });
        window.addEventListener('start-tour-4', () => {
            const driver = createDriver4();
            setTimeout(() => driver.drive(), 100);

        });



        Livewire.hook('message.processed', () => {
            // Aqui você pode limpar os locais se quiser resetar tours, mas evite recriar drivers diretamente
        });
    });
</script>
