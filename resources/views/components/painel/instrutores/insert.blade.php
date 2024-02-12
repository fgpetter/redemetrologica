@if (session('funcionario-error'))
    <div class="alert alert-danger"> {{ session('error') }} </div>
@endif

<div class="card">
    <div class="card-body">

        <form method="POST"
            action="{{ isset($funcionario->uid) ? route('funcionario-update', $funcionario->uid) : route('funcionario-create') }}"
            enctype="multipart/form-data">
            @csrf

            <!-- Nav tabs -->
            <ul class="nav nav-tabs nav-justified mb-3" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#principal" role="tab"
                        aria-selected="true">
                        Dados Principais
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#documentos" role="tab" aria-selected="false">
                        Cursos Habilitados
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#endereco" role="tab" aria-selected="false">
                        Cursos Realizados
                    </a>
                </li>

            </ul>

            <!-- Tab panes -->
            <div class="tab-content">
                <div class="tab-pane active" id="principal" role="tabpanel"> <!-- Dados principais -->

                    <div class="row gy-3">
                        <div class="col-sm-4">
                            <x-forms.input-field :value="old('data_cadastro') ?? ($instrutor->data_cadastro ?? null)" type="date" name="data_cadastro"
                                label="Data de Cadastro" />
                            @error('nascimento')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-4">
                            <x-forms.input-field :value="old('data_alteracao') ?? ($instrutor->data_calteracao ?? null)" type="date" name="data_calteracao"
                                label="Data Alteração/Alterado Por" />
                            @error('data_alteracao')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>


                        <div class="col-sm-4">
                            <x-forms.input-select name="situacao" label="Situação">

                                <option value="ATIVO">ATIVO</option>
                                <option value="INATIVO">INATIVO</option>


                            </x-forms.input-select>
                            @error('situacao')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-6">
                            <x-forms.input-select name="tipo_classificacao" label="Tipo Classificação">

                                <option value="FÍSICA">FÍSICA</option>
                                <option value="OUTROS">OUTROS</option>
                                <option value="PJ">PJ</option>

                            </x-forms.input-select>
                            @error('tipo_classificacao')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-6">
                            <x-forms.input-field :value="old('data_nascimento') ?? ($instrutor->data_nascimento ?? null)" type="date" name="data_nascimento"
                                label="Data Nascimento" />
                            @error('data_nascimento')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-6">
                            <x-forms.input-field :value="old('cnpj_cpf') ?? ($instrutor->cnpj_cpf ?? null)" name="cnpj" label="CNPJ/CPF"
                                placeholder="CNPJ/CPF" />
                            @error('cnpj_cpf')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-6">
                            <x-forms.input-field :value="old('rg_IE') ?? ($instrutor->rg_ie ?? null)" name="rg_ie" label="RG/IE" placeholder="RG/IE" />
                            @error('rg_ie')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-12">
                            <x-forms.input-field :value="old('nome') ?? ($instrutor->nome ?? null)" name="nome" label="Nome" placeholder="" />
                            @error('nome')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-12">
                            <x-forms.input-field :value="old('curriculo') ?? ($instrutor->curriculo ?? null)" name="curriculo" label="Curriculo" placeholder="" />
                            @error('curriculo')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-4">
                            <x-forms.input-field :value="old('CEP') ?? ($instrutor->CEP ?? null)" name="CEP" label="CEP" placeholder="" />
                            @error('CEP')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-4">
                            <x-forms.input-field :value="old('MUNICIPIO') ?? ($instrutor->MUNICIPIO ?? null)" name="MUNICIPIO" label="Município" placeholder="" />
                            @error('MUNICIPIO')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-4">
                            <x-forms.input-select name="UF" label="UF">

                                <option value="RS">RS</option>
                                <option value="OUTROS">OUTROS</option>
                                <option value="SP">SP</option>

                            </x-forms.input-select>
                            @error('UF')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-12">
                            <x-forms.input-field :value="old('Endereco') ?? ($instrutor->Endereco ?? null)" name="Endereco" label="Endereço" placeholder="" />
                            @error('Endereco')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-6">
                            <x-forms.input-field :value="old('numero_complemento') ?? ($instrutor->numero_complemento ?? null)" name="numero_complemento"
                                label="Número/Complemento" placeholder="" />
                            @error('numero_complemento')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-6">
                            <x-forms.input-field :value="old('bairro') ?? ($instrutor->bairro ?? null)" name="bairro" label="Bairro" placeholder="" />
                            @error('bairro')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-6">
                            <x-forms.input-field :value="old('fone1') ?? ($instrutor->fone1 ?? null)" name="fone1" label="Fone 1" placeholder="" />
                            @error('fone1')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-6">
                            <x-forms.input-field :value="old('fone2') ?? ($instrutor->fone2 ?? null)" name="fone2" label="Fone 2" placeholder="" />
                            @error('fone2')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-6">
                            <x-forms.input-field :value="old('contato') ?? ($instrutor->contato ?? null)" name="contato" label="Contato" placeholder="" />
                            @error('contato')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-6">
                            <x-forms.input-field :value="old('celular') ?? ($instrutor->celular ?? null)" name="celular" label="Celular" placeholder="" />
                            @error('celular')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-6">
                            <x-forms.input-field :value="old('email') ?? ($instrutor->email ?? null)" name="email" label="Email" placeholder="" />
                            @error('email')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-6">
                            <x-forms.input-field :value="old('site') ?? ($instrutor->site ?? null)" name="site" label="Site" placeholder="" />
                            @error('site')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-6">
                            <x-forms.input-field :value="old('banco') ?? ($instrutor->banco ?? null)" name="banco" label="Banco" placeholder="" />
                            @error('banco')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-6">
                            <x-forms.input-field :value="old('agencia') ?? ($instrutor->agencia ?? null)" name="agencia" label="Agência" placeholder="" />
                            @error('agencia')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-6">
                            <x-forms.input-field :value="old('conta') ?? ($instrutor->conta ?? null)" name="conta" label="Conta" placeholder="" />
                            @error('conta')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-12">
                            <x-forms.input-textarea name="observacoes" label="Observações">
                                {{ old('observacoes') ?? ($instrutor->observacoes ?? null) }}
                            </x-forms.input-textarea>
                            @error('observacoes')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="tab-pane" id="documentos" role="tabpanel"> <!-- Cursos habilitados -->

                    <div class="row gy-3">

                        <div class="col-sm-12">
                            <x-forms.input-select name="curso" label="Curso">

                                <option value="Curso 1">Curso 1</option>
                                <option value="Curso 2">Curso 2</option>
                                <option value="Curso 3">Curso 3</option>

                            </x-forms.input-select>
                            @error('curso')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-6">
                            <x-forms.input-select name="habilitado" label="Habilitado">

                                <option value="SIM ">SIM</option>
                                <option value="NÃO ">NÃO </option>


                            </x-forms.input-select>
                            @error('habilitado')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-6"></div>
                        <div class="col-sm-6">
                            <x-forms.input-select name="conhecimento"
                                label="Conhecimento (Graduação na área de atuação do curso)">

                                <option value="SIM ">SIM</option>
                                <option value="NÃO ">NÃO </option>


                            </x-forms.input-select>
                            @error('conhecimento')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-6">
                            <x-forms.input-select name="experiencia"
                                label="Experiência (Mínimo de 02 anos na área de atuação do curso)">

                                <option value="SIM ">SIM</option>
                                <option value="NÃO ">NÃO </option>


                            </x-forms.input-select>
                            @error('experiencia')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-12">
                            <x-forms.input-textarea name="analise_observacoes" label="Análise/Observações">
                                {{ old('analise_observacoes') ?? ($instrutor->analise_observacoes ?? null) }}
                            </x-forms.input-textarea>
                            @error('analise_observacoes')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- PLACEHOLDER --> trocar para lista de cursos --}}
                        <div class="row">
                            <div class="col">
                                <x-painel.instrutores.list />
                            </div>
                        </div>
                        {{-- PLACEHOLDER --> trocar para lista de cursos --}}

                    </div>
                </div>

                <div class="tab-pane" id="endereco" role="tabpanel"> <!-- Cursos Realizado -->
                    <div class="row gy-3">
                        {{-- PLACEHOLDER --> trocar para lista de cursos --}}
                        <div class="row">
                            <div class="col">
                                <x-painel.instrutores.list-cursos />
                            </div>
                        </div>
                        {{-- PLACEHOLDER --> trocar para lista de cursos --}}

                    </div>
                </div>



            </div>

            <!-- Btn -->
            <div class="row mt-3">
                <div class="col-sm-12">
                    <button type="submit" class="btn btn-primary px-4"> Cadastrar
                    </button>
                </div>
            </div>
        </form>


    </div>

</div>
