<div class="col-9">
    <!-- Cabeçalho do interlaboratorial -->
    <div>
        <h5 class="card-subtitle mt-3 mb-2 text-primary-emphasis">Dados do interlaboratorial:</h5>
        <p class="pb-3">
            <strong>Interlaboratorial:</strong> {{ $interlab->interlab->nome }} <br>
            <strong>Agenda:</strong> de {{ \Carbon\Carbon::parse($interlab->data_inicio)->format('d/m/Y') }} a
            {{ \Carbon\Carbon::parse($interlab->data_fim)->format('d/m/Y') }} <br>
        </p>
    </div>
    <!-- Listagem de empresas com inscrições existentes -->
    @if ($pessoa && $pessoa->isNotEmpty())
        @if ($interlab->instrucoes_inscricao)
            <blockquote class="blockquote custom-blockquote blockquote-outline blockquote-primary rounded mb-5">
                <i class="ri-information-fill text-primary fs-5"></i> Importante:
                <p class="mb-2 text-black fs-6 fs-sm-5">{!! nl2br($interlab->instrucoes_inscricao) !!}</p>
            </blockquote>
        @endif
        <div class="mb-5 border p-3 rounded">
            <h5 class="mb-3 text-primary">Laboratórios Inscritos:</h5>
            @foreach ($pessoa as $empresa)
                <div class="card mb-3" wire:key="empresa-{{ $empresa->id }}">
                    @if ($empresaEditadaId !== $empresa->id)
                        <div class="card-header bg-light" style="min-height: 60px;">
                            <div class="d-flex justify-content-between align-items-center h-100">
                                <div>
                                    <strong>{{ $empresa->nome_razao }}</strong>
                                    <small class="text-muted ms-2">CNPJ: {{ $empresa->cpf_cnpj }}</small>
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
                                            wire:click.prevent="{{ $empresaEditadaId === $empresa->id
                                                ? '$set(\'empresaEditadaId\', null)'
                                                : 'editEmpresa(' . $empresa->id . ')' }}">
                                            <i class="ri-edit-line"></i>
                                            {{ $empresaEditadaId === $empresa->id ? 'Cancelar' : 'Editar' }}
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                    {{-- editando empresa ja inscrita --}}
                    @if ($empresaEditadaId === $empresa->id)
                        <div class="card-body bg-light">
                            <form wire:submit.prevent="salvarEmpresa">
                                <div class="row g-4">

                                    <div class="col-md-6">
                                        <label for="nome_razao" class="form-label">Razão Social</label>
                                        <input type="text" id="nome_razao" wire:model="nome_razao"
                                            class="form-control" placeholder="Digite a razão social">
                                        @error('nome_razao')
                                            <span class="text-danger small">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="cpf_cnpj" class="form-label">CNPJ</label>
                                        <input type="text" id="cpf_cnpj" wire:model="cpf_cnpj" class="form-control"
                                            readonly>
                                        @error('cpf_cnpj')
                                            <span class="text-danger small">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="telefone" class="form-label">Telefone</label>
                                        <input type="text" id="telefone" wire:model="telefone" class="form-control"
                                            placeholder="Digite o telefone">
                                        @error('telefone')
                                            <span class="text-danger small">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="cobranca_email" class="form-label">E-mail de Cobrança</label>
                                        <input type="email" id="cobranca_email" wire:model="cobranca_email"
                                            class="form-control" placeholder="Digite o e-mail de cobrança">
                                        @error('cobranca_email')
                                            <span class="text-danger small">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="col-md-4">
                                        <label for="cobranca_cep" class="form-label">CEP</label>
                                        <input type="text" id="cobranca_cep" wire:model="cobranca_cep"
                                            class="form-control" placeholder="Digite o CEP"
                                            wire:blur="buscaCep('cobranca')">
                                        @error('cobranca_cep')
                                            <span class="text-danger small">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="col-md-8">
                                        <label for="cobranca_endereco" class="form-label">Endereço</label>
                                        <input type="text" id="cobranca_endereco" wire:model="cobranca_endereco"
                                            class="form-control" placeholder="Ex. Av. Brasil, 1234">
                                        @error('cobranca_endereco')
                                            <span class="text-danger small">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="cobranca_complemento" class="form-label">Complemento</label>
                                        <input type="text" id="cobranca_complemento"
                                            wire:model="cobranca_complemento" class="form-control"
                                            placeholder="Ex. Sala 101">
                                        @error('cobranca_complemento')
                                            <span class="text-danger small">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="cobranca_bairro" class="form-label">Bairro</label>
                                        <input type="text" id="cobranca_bairro" wire:model="cobranca_bairro"
                                            class="form-control" placeholder="Digite o bairro">
                                        @error('cobranca_bairro')
                                            <span class="text-danger small">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="cobranca_cidade" class="form-label">Cidade</label>
                                        <input type="text" id="cobranca_cidade" wire:model="cobranca_cidade"
                                            class="form-control" placeholder="Digite a cidade">
                                        @error('cobranca_cidade')
                                            <span class="text-danger small">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="col-md-2">
                                        <label for="cobranca_uf" class="form-label">UF</label>
                                        <input type="text" id="cobranca_uf" wire:model="cobranca_uf"
                                            class="form-control" maxlength="2" placeholder="UF"
                                            style="text-transform: uppercase;">
                                        @error('cobranca_uf')
                                            <span class="text-danger small">{{ $message }}</span>
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
                            @foreach ($inscritos->where('empresa_id', $empresa->id) as $inscrito)
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
                                                            <x-forms.input-field wire:model="laboratorio"
                                                                name="laboratorio" label="Laboratório" required />
                                                            @error('laboratorio')
                                                                <div class="text-warning">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                        <div class="col-12 col-xl-6">
                                                            <x-forms.input-field wire:model="responsavel_tecnico"
                                                                name="responsavel_tecnico" label="Responsável Técnico"
                                                                required />
                                                            @error('responsavel_tecnico')
                                                                <div class="text-warning">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>

                                                    <div class="row mb-2">
                                                        <div class="col-12 col-sm-6">
                                                            <x-forms.input-field wire:model="lab_telefone"
                                                                name="lab_telefone" label="Telefone"
                                                                class="telefone" />
                                                            @error('lab_telefone')
                                                                <div class="text-warning">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                        <div class="col-12 col-sm-6">
                                                            <x-forms.input-field wire:model="lab_email"
                                                                name="lab_email" type="email" label="E-mail" />
                                                            @error('lab_email')
                                                                <div class="text-warning">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>

                                                    <div class="row my-3 gy-2">
                                                        <div class="col-5 col-sm-4">
                                                            <x-forms.input-field wire:model="cep" name="cep"
                                                                label="CEP" class="cep"
                                                                wire:blur="buscaCep('laboratorio')" />
                                                            @error('cep')
                                                                <div class="text-warning">{{ $message }}
                                                                </div>
                                                            @enderror
                                                        </div>
                                                        <div class="col-12 col-sm-8">
                                                            <x-forms.input-field wire:model="endereco" name="endereco"
                                                                label="Endereço" />
                                                            @error('endereco')
                                                                <div class="text-warning">{{ $message }}
                                                                </div>
                                                            @enderror
                                                        </div>
                                                        <div class="col-12 col-sm-6">
                                                            <x-forms.input-field wire:model="complemento"
                                                                name="complemento" label="Complemento" />
                                                            @error('complemento')
                                                                <div class="text-warning">{{ $message }}
                                                                </div>
                                                            @enderror
                                                        </div>
                                                        <div class="col-12 col-sm-6">
                                                            <x-forms.input-field wire:model="bairro" name="bairro"
                                                                label="Bairro" />
                                                            @error('bairro')
                                                                <div class="text-warning">{{ $message }}
                                                                </div>
                                                            @enderror
                                                        </div>
                                                        <div class="col-12 col-sm-6">
                                                            <x-forms.input-field wire:model="cidade" name="cidade"
                                                                label="Cidade" />
                                                            @error('cidade')
                                                                <div class="text-warning">{{ $message }}
                                                                </div>
                                                            @enderror
                                                        </div>
                                                        <div class="col-4 col-sm-2">
                                                            <label for="uf" class="form-label mb-0">UF</label>
                                                            <input type="text" class="form-control"
                                                                wire:model="uf" id="uf" maxlength="2"
                                                                style="text-transform: uppercase;">
                                                            @error('uf')
                                                                <div class="text-warning">{{ $message }}
                                                                </div>
                                                            @enderror
                                                        </div>
                                                    </div>

                                                    <x-forms.input-textarea wire:model="informacoes_inscricao"
                                                        name="informacoes_inscricao"
                                                        label="Informações da inscrição:" />
                                                </div>
                                            </div>

                                            <div class="row m-3 mt-4 d-flex justify-content-end gap-2">
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
                                    <button class="btn btn-sm btn-success"
                                        wire:click.prevent="novoLaboratorio({{ $empresa->id }})">
                                        <i class="ri-add-line"></i> Adicionar Novo Laboratório
                                    </button>
                                </div>
                            @endif
                            <!-- Formulário de novo laboratório em empresa já inscrita -->
                            @if ($novaInscricaoEmpresaId === $empresa->id)
                                <div class="mt-3 border-top pt-3">
                                    <form wire:submit.prevent="InscreveLab" id="confirma-inscricao-interlab"
                                        class="mt-4">
                                        <div class="card border overflow-hidden card-border-dark shadow-none">
                                            <div class="card-header">
                                                <h6 class="card-title mb-0">Informe os dados do Laboratório para envio
                                                    de
                                                    amostras:</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-12 col-xl-6">
                                                        <x-forms.input-field wire:model="laboratorio"
                                                            name="laboratorio" label="Laboratório" class="mb-2"
                                                            required />
                                                        @error('laboratorio')
                                                            <div class="text-warning">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                    <div class="col-12 col-xl-6">
                                                        <x-forms.input-field wire:model="responsavel_tecnico"
                                                            name="responsavel_tecnico" label="Responsável Técnico"
                                                            class="mb-2" required />
                                                        @error('responsavel_tecnico')
                                                            <div class="text-warning">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <div class="row mb-2">
                                                    <div class="col-12 col-sm-6">
                                                        <x-forms.input-field wire:model="lab_telefone" name="telefone"
                                                            label="Telefone" class="telefone" />
                                                        @error('lab_telefone')
                                                            <div class="text-warning">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                    <div class="col-12 col-sm-6">
                                                        <x-forms.input-field wire:model="lab_email" name="email"
                                                            type="email" label="E-mail" />
                                                        @error('lab_email')
                                                            <div class="text-warning">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <div class="row my-3 gy-2">
                                                    <div class="col-5 col-sm-4">
                                                        <x-forms.input-field wire:model="cep" name="cep"
                                                            label="CEP" class="cep"
                                                            wire:blur="buscaCep('laboratorio')" />
                                                        @error('cep')
                                                            <div class="text-warning">{{ $message }}</div>
                                                        @enderror
                                                    </div>

                                                    <div class="col-12 col-sm-8">
                                                        <x-forms.input-field wire:model="endereco" name="endereco"
                                                            label="Endereço com número"
                                                            placeholder="Ex. Av. Brasil, 1234" />
                                                        @error('endereco')
                                                            <div class="text-warning">{{ $message }}</div>
                                                        @enderror
                                                    </div>

                                                    <div class="col-12 col-sm-6">
                                                        <x-forms.input-field wire:model="complemento"
                                                            name="complemento" label="Complemento"
                                                            placeholder="Ex. Sala 101" />
                                                        @error('complemento')
                                                            <div class="text-warning">{{ $message }}</div>
                                                        @enderror
                                                    </div>

                                                    <div class="col-12 col-sm-6">
                                                        <x-forms.input-field wire:model="bairro" name="bairro"
                                                            label="Bairro" />
                                                        @error('bairro')
                                                            <div class="text-warning">{{ $message }}</div>
                                                        @enderror
                                                    </div>

                                                    <div class="col-12 col-sm-6">
                                                        <x-forms.input-field wire:model="cidade" name="cidade"
                                                            label="Cidade" />
                                                        @error('cidade')
                                                            <div class="text-warning">{{ $message }}</div>
                                                        @enderror
                                                    </div>

                                                    <div class="col-4 col-sm-2">
                                                        <label for="uf" class="form-label mb-0">UF<small
                                                                class="text-danger-emphasis opacity-75"> *
                                                            </small></label>
                                                        <input type="text" class="form-control" wire:model="uf"
                                                            id="uf" maxlength="2" pattern="[A-Z]{2}"
                                                            title="Duas letras maiúsculo"
                                                            style="text-transform: uppercase;">
                                                        @error('uf')
                                                            <div class="text-warning">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <x-forms.input-textarea wire:model="informacoes_inscricao"
                                                    name="informacoes_inscricao" label="Informações da inscrição:"
                                                    sublabel="Informe aqui quais rodadas, blocos ou parâmetros esse laboratório irá participar.">
                                                    {{ old('informacoes_inscricao') ?? null }}
                                                </x-forms.input-textarea>
                                                @error('informacoes_inscricao')
                                                    <div class="text-warning">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div>
                                            <div class="row mt-3 mt-4 d-flex justify-content-end gap-2">
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
        <div class="row justify-content-center mb-5">
            <div class="col-md-6">
                <label for="cnpj" class="fw-bold">
                    {{ $pessoa && $pessoa->isNotEmpty() ? 'Informe CNPJ caso queira cadastrar outro endereço de cobrança' : 'Informe o CNPJ para continuar' }}

                </label>
                <div class="input-group input-group-lg">
                    <input type="text" id="cnpj" wire:model="BuscaCnpj" class="form-control"
                        placeholder="CNPJ">
                    <button type="button" wire:click="ProcuraCnpj" class="btn btn-primary">Buscar</button>
                </div>
                @error('BuscaCnpj')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
        </div>
    @endif
    <!-- Formulário de edição/cadastro de NOVA empresa -->
    @if ($showSalvarEmpresa)
        @if (!$pessoa)
            @if ($interlab->instrucoes_inscricao)
                <blockquote class="blockquote custom-blockquote blockquote-outline blockquote-primary rounded mb-5">
                    <i class="ri-information-fill text-primary fs-5"></i> Importante:
                    <p class="mb-2 text-black fs-6 fs-sm-5">{!! nl2br($interlab->instrucoes_inscricao) !!}</p>
                </blockquote>
            @endif
        @endif
        <form wire:submit.prevent="salvarEmpresa" class="mt-4">
            <div class="card border overflow-hidden card-border-dark shadow-none">

                <div class="card-header">
                    <h6 class="card-title mb-0">Complete os dados abaixo para emissão e envio de NF</h6>
                </div>

                <div class="card-body">
                    <div class="row g-3">

                        <div class="col-md-6">
                            <label for="nome_razao" class="form-label">Razão Social</label>
                            <input type="text" id="nome_razao" wire:model="nome_razao" class="form-control">
                            @error('nome_razao')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="cpf_cnpj" class="form-label">CNPJ</label>
                            <input type="text" id="cpf_cnpj" wire:model="cpf_cnpj" class="form-control"
                                readonly>
                            @error('cpf_cnpj')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="telefone" class="form-label">Telefone</label>
                            <input type="text" id="telefone" wire:model="telefone" class="form-control">
                            @error('telefone')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        {{-- endereco cobranca --}}
                        <!-- E-mail de Cobrança -->
                        <div class="col-md-6">
                            <label for="cobranca_email" class="form-label">E-mail de Cobrança</label>
                            <input type="email" id="cobranca_email" wire:model="cobranca_email"
                                class="form-control" placeholder="Digite o e-mail de cobrança">
                            @error('cobranca_email')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="row my-3 gy-2">

                            <div class="col-5 col-sm-4">
                                <x-forms.input-field wire:model="cobranca_cep" name="cobranca_cep" label="CEP"
                                    class="cep" wire:blur="buscaCep('cobranca')" />
                                @error('cep')
                                    <div class="text-warning">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-sm-8">
                                <x-forms.input-field wire:model="cobranca_endereco" name="cobranca_endereco"
                                    label="Endereço com número" placeholder="Ex. Av. Brasil, 1234" />
                                @error('endereco')
                                    <div class="text-warning">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-sm-6">
                                <x-forms.input-field wire:model="cobranca_complemento" name="cobranca_complemento"
                                    label="Complemento" placeholder="Ex. Sala 101" />
                                @error('complemento')
                                    <div class="text-warning">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-sm-6">
                                <x-forms.input-field wire:model="cobranca_bairro" name="cobranca_bairro"
                                    label="Bairro" />
                                @error('bairro')
                                    <div class="text-warning">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-sm-6">
                                <x-forms.input-field wire:model="cobranca_cidade" name="cobranca_cidade"
                                    label="Cidade" />
                                @error('cidade')
                                    <div class="text-warning">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-4 col-sm-2">
                                <label for="uf" class="form-label mb-0">UF<small
                                        class="text-danger-emphasis opacity-75"> * </small></label>
                                <input type="text" class="form-control" wire:model="cobranca_uf" maxlength="2"
                                    pattern="[A-Z]{2}" title="Duas letras maiúsculo"
                                    style="text-transform: uppercase;">
                                @error('uf')
                                    <div class="text-warning">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    @if (session()->has('message'))
                        <div class="alert alert-success mt-3">
                            {{ session('message') }}
                        </div>
                    @endif
                </div>

                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-auto">
                            <button type="submit" class="btn btn-success">
                                Continuar
                            </button>
                        </div>
                        <div class="col-md-auto">
                            <button type="button" class="btn btn-warning"
                                wire:click="$set('showSalvarEmpresa', false)">
                                Cancelar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    @endif
    <!-- Formulário de NOVO laboratório -->
    @if ($showInscreveLab)
        @if (!$pessoa)
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
                            <strong>{{ $empresa->nome_razao }}</strong>
                            <small class="text-muted ms-2">CNPJ: {{ $empresa->cpf_cnpj }}</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border overflow-hidden card-border-dark shadow-none">
                <div class="card-header">
                    <h6 class="card-title mb-0">Informe os dados do Laboratório para envio de amostras:</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12 col-xl-6">
                            <x-forms.input-field wire:model="laboratorio" name="laboratorio" label="Laboratório"
                                class="mb-2" required />
                            @error('laboratorio')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12 col-xl-6">
                            <x-forms.input-field wire:model="responsavel_tecnico" name="responsavel_tecnico"
                                label="Responsável Técnico" class="mb-2" required />
                            @error('responsavel_tecnico')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-12 col-sm-6">
                            <x-forms.input-field wire:model="lab_telefone" name="telefone" label="Telefone"
                                class="telefone" />
                            @error('lab_telefone')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12 col-sm-6">
                            <x-forms.input-field wire:model="lab_email" name="email" type="email"
                                label="E-mail" />
                            @error('lab_email')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row my-3 gy-2">
                        <div class="col-5 col-sm-4">
                            <x-forms.input-field wire:model="cep" name="cep" label="CEP" class="cep"
                                wire:blur="buscaCep('laboratorio')" />
                            @error('cep')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-sm-8">
                            <x-forms.input-field wire:model="endereco" name="endereco" label="Endereço com número"
                                placeholder="Ex. Av. Brasil, 1234" />
                            @error('endereco')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-sm-6">
                            <x-forms.input-field wire:model="complemento" name="complemento" label="Complemento"
                                placeholder="Ex. Sala 101" />
                            @error('complemento')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-sm-6">
                            <x-forms.input-field wire:model="bairro" name="bairro" label="Bairro" />
                            @error('bairro')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-sm-6">
                            <x-forms.input-field wire:model="cidade" name="cidade" label="Cidade" />
                            @error('cidade')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-4 col-sm-2">
                            <label for="uf" class="form-label mb-0">UF<small
                                    class="text-danger-emphasis opacity-75"> * </small></label>
                            <input type="text" class="form-control" wire:model="uf" id="uf"
                                maxlength="2" pattern="[A-Z]{2}" title="Duas letras maiúsculo"
                                style="text-transform: uppercase;">
                            @error('uf')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <x-forms.input-textarea wire:model="informacoes_inscricao" name="informacoes_inscricao"
                        label="Informações da inscrição:"
                        sublabel="Informe aqui quais rodadas, blocos ou parâmetros esse laboratório irá participar.">
                        {{ old('informacoes_inscricao') ?? null }}
                    </x-forms.input-textarea>
                    @error('informacoes_inscricao')
                        <div class="text-warning">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div>
                <div class="row">
                    <div class="col-md-auto">
                        <button class="btn btn-info mt-2" type="submit">
                            SALVAR
                        </button>
                    </div>
                    <div class="col-md-auto">
                        <button class="btn btn-warning mt-2" type="button"
                            wire:click="$set('showInscreveLab', false)">
                            CANCELAR
                        </button>
                    </div>
                </div>
            </div>
        </form>
    @endif
</div>
