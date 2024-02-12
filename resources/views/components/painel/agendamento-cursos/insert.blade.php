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
                        Principal
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#participantes" role="tab" aria-selected="false">
                        Participantes
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#empresas_participantes" role="tab"
                        aria-selected="false">
                        Empresas Participantes
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#NF" role="tab" aria-selected="false">
                        Notas Fiscais
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#despesas" role="tab" aria-selected="false">
                        Despesas
                    </a>
                </li>


            </ul>

            <!-- Tab panes -->
            <div class="tab-content">
                <div class="tab-pane active" id="principal" role="tabpanel"> <!-- Principal -->
                    <div class="row gy-3">


                        <div class="col-sm-4">
                            <x-forms.input-select name="status" label="Status">

                                <option value="CONFIRMADO">CONFIRMADO</option>
                                <option value="CANCELADO">CANCELADO</option>
                                <option value="INATIVO">INATIVO</option>


                            </x-forms.input-select>
                            @error('status')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-4">
                            <x-forms.input-select name="destaque" label="Destaque">

                                <option value="SIM">SIM</option>
                                <option value="NAO">NÃO</option>


                            </x-forms.input-select>
                            @error('destaque')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-4">
                            <x-forms.input-select name="tipo_agendamento" label="Tipo de Agendamento">

                                <option value="EVENTO">EVENTO</option>
                                <option value="OUTRO">OUTRO</option>


                            </x-forms.input-select>
                            @error('tipo_agendamento')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-12">
                            <x-forms.input-field :value="old('nome_curso') ?? ($agendamento_curso->nome_curso ?? null)" name="nome_curso" label="Nome do Curso"
                                placeholder="" />
                            @error('nome_curso')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-12">
                            <x-forms.input-field :value="old('empresa') ?? ($agendamento_curso->empresa ?? null)" name="empresa" label="Emrpesa" placeholder="" />
                            @error('empresa')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-12">
                            <x-forms.input-textarea name="local_endereco" label="Local/Endereço">
                                {{ old('local_endereco') ?? ($agendamento_curso->local_endereco ?? null) }}
                            </x-forms.input-textarea>
                            @error('local_endereco')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-4">
                            <x-forms.input-field :value="old('sala') ?? ($agendamento_curso->sala ?? null)" name="sala" label="Sala" placeholder="" />
                            @error('sala')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-4">
                            <x-forms.input-field :value="old('num_reserva') ?? ($agendamento_curso->num_reserva ?? null)" name="num_reserva" label="Número da Reserva"
                                placeholder="" />
                            @error('num_reserva')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-4">
                            <x-forms.input-field :value="old('data_confirmacao') ?? ($agendamento_curso->data_confirmacao ?? null)" type="date" name="data_confirmacao"
                                label="Data Confirmação" />
                            @error('data_confirmacao')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-4">
                            <x-forms.input-field :value="old('data_ini') ?? ($agendamento_curso->data_ini ?? null)" type="date" name="data_ini" label="Data Inicio" />
                            @error('data_ini')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-4">
                            <x-forms.input-field :value="old('data_fim') ?? ($agendamento_curso->data_fim ?? null)" type="date" name="data_fim" label="Data Fim" />
                            @error('data_fim')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-4">
                            <x-forms.input-select name="tipo_data" label="Tipo Data">

                                <option value="EVENTO">EVENTO</option>
                                <option value="OUTRO">OUTRO</option>


                            </x-forms.input-select>
                            @error('tipo_data')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-6">
                            <x-forms.input-field :value="old('horario') ?? ($agendamento_curso->horario ?? null)" name="horario" label="Horário" placeholder="" />
                            @error('horario')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-6">
                            <x-forms.input-select name="inscricao" label="Inscrições">

                                <option value="SIM">SIM</option>
                                <option value="NAO">NÃO</option>


                            </x-forms.input-select>
                            @error('inscricao')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-6">
                            <x-forms.input-select name="site" label="Site">

                                <option value="SIM">SIM</option>
                                <option value="NAO">NÃO</option>


                            </x-forms.input-select>
                            @error('site')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-6">
                            <x-forms.input-select name="carga_horaria" label="Carga Horária">

                                <option value="16">16</option>
                                <option value="32">32</option>


                            </x-forms.input-select>
                            @error('carga_horaria')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-12">
                            <x-forms.input-select name="instrutor" label="Instrutor">

                                <option value="RAFAEL LUCCA">RAFAEL LUCCA</option>
                                <option value="DOUGLAS O CONEWL">DOUGLAS O CONEWL</option>


                            </x-forms.input-select>
                            @error('instrutor')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-4">
                            <x-forms.input-field :value="old('investimento') ?? ($agendamento_curso->investimento ?? null)" name="investimento" label="Investimento"
                                placeholder="" />
                            @error('investimento')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-4">
                            <x-forms.input-field :value="old('investimento_ass') ?? ($agendamento_curso->investimento_ass ?? null)" name="investimento_ass"
                                label="Investimento Associado" placeholder="" />
                            @error('investimento_ass')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-4">
                            <x-forms.input-field :value="old('data_limit_pag') ?? ($agendamento_curso->data_limit_pag ?? null)" type="date" name="data_limit_pag"
                                label="Data Limite Pagamento" />
                            @error('data_limit_pag')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-12">
                            <x-forms.input-textarea name="obs" label="Observações">
                                {{ old('obs') ?? ($agendamento_curso->obs ?? null) }}
                            </x-forms.input-textarea>
                            @error('obs')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>




                    </div>
                </div>

                <div class="tab-pane" id="participantes" role="tabpanel"> <!-- participantes -->

                    <div class="row gy-3">

                        {{-- conteudo --}}


                        <div class="col-sm-12">
                            <x-forms.input-field :value="old('nome') ?? ($agendamento_curso->nome ?? null)" name="nome" label="Nome" placeholder="" />
                            @error('nome')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-8">
                            <x-forms.input-field :value="old('cargo') ?? ($agendamento_curso->cargo ?? null)" name="cargo" label="Cargo" placeholder="" />
                            @error('cargo')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-4">
                            <x-forms.input-field :value="old('fone') ?? ($agendamento_curso->fone ?? null)" name="fone" label="Fone" placeholder="" />
                            @error('fone')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-8">
                            <x-forms.input-field :value="old('email') ?? ($agendamento_curso->email ?? null)" name="email" label="Email" placeholder="" />
                            @error('email')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-4">
                            <x-forms.input-field :value="old('valor') ?? ($agendamento_curso->valor ?? null)" name="valor" label="Valor" placeholder="" />
                            @error('valor')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-4">
                            <x-forms.input-select name="confirmou" label="confirmou">

                                <option value="SIM">SIM</option>
                                <option value="NÃO">NÃO</option>


                            </x-forms.input-select>
                            @error('confirmou')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-4">
                            <x-forms.input-field :value="old('data_confirm') ?? ($agendamento_curso->data_confirm ?? null)" type="date" name="data_confirm"
                                label="Usuário/Data Confirmação" />
                            @error('data_confirm')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-4">
                            <x-forms.input-field :value="old('data_certificado') ?? ($agendamento_curso->data_certificado ?? null)" type="date" name="data_certificado"
                                label="Certificado Enviado Em" />
                            @error('data_certificado')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-4">
                            <x-forms.input-select name="pesquisa" label="Respondeu Pesquisa">

                                <option value="SIM">SIM</option>
                                <option value="NÃO">NÃO</option>


                            </x-forms.input-select>
                            @error('pesquisa')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>
                        {{-- end cont --}}
                        {{-- lista --}}
                        {{-- PLACEHOLDER --> trocar --}}
                        <div class="row">
                            <div class="col">
                                <x-painel.agendamento-cursos.list-cursos />
                            </div>
                        </div>
                        {{-- PLACEHOLDER --> trocar --}}

                        {{-- lista --}}



                    </div>
                </div>

                <div class="tab-pane" id="empresas_participantes" role="tabpanel"> <!-- empresas participantes -->

                    <div class="row gy-3">

                        {{-- conteudo --}}


                        <div class="col-sm-12">
                            <x-forms.input-field :value="old('razao_social') ?? ($agendamento_curso->razao_social ?? null)" name="razao_social" label="Razao Social"
                                placeholder="" />
                            @error('razao_social')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-12">
                            <x-forms.input-field :value="old('endereco') ?? ($agendamento_curso->endereco ?? null)" name="endereco" label="Endereco"
                                placeholder="" />
                            @error('endereco')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-4">
                            <x-forms.input-field :value="old('cep') ?? ($agendamento_curso->cep ?? null)" name="cep" label="CEP" placeholder="" />
                            @error('cep')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-4">
                            <x-forms.input-field :value="old('Bairro') ?? ($agendamento_curso->Bairro ?? null)" name="Bairro" label="Bairro" placeholder="" />
                            @error('Bairro')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-4">
                            <x-forms.input-field :value="old('Cidade') ?? ($agendamento_curso->Cidade ?? null)" name="Cidade" label="Cidade" placeholder="" />
                            @error('Cidade')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-4">
                            <x-forms.input-field :value="old('UF') ?? ($agendamento_curso->UF ?? null)" name="UF" label="UF" placeholder="" />
                            @error('UF')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-4">
                            <x-forms.input-field :value="old('IE') ?? ($agendamento_curso->IE ?? null)" name="IE" label="Inscrição Estadual"
                                placeholder="" />
                            @error('IE')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-4">
                            <x-forms.input-field :value="old('IM') ?? ($agendamento_curso->IM ?? null)" name="IM" label="Inscrição Municipal"
                                placeholder="" />
                            @error('IM')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-12">
                            <x-forms.input-field :value="old('Email') ?? ($agendamento_curso->Email ?? null)" name="Email" label="Email" placeholder="" />
                            @error('Email')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-6">
                            <x-forms.input-field :value="old('ficou_sabendo') ?? ($agendamento_curso->ficou_sabendo ?? null)" name="ficou_sabendo"
                                label="Como Ficou Sabendo do Curso?" placeholder="" />
                            @error('ficou_sabendo')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-6">
                            <x-forms.input-field :value="old('resp_info') ?? ($agendamento_curso->resp_info ?? null)" name="resp_info"
                                label="Responsável pelas Informações" placeholder="" />
                            @error('resp_info')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-6">
                            <x-forms.input-select name="associado" label="Associado">

                                <option value="SIM">SIM</option>
                                <option value="NÃO">NÃO</option>


                            </x-forms.input-select>
                            @error('associado')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-6">
                            <x-forms.input-select name="emite_NF" label="Emite NF">

                                <option value="SIM">SIM</option>
                                <option value="NÃO">NÃO</option>


                            </x-forms.input-select>
                            @error('emite_NF')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- lista --}}
                        {{-- PLACEHOLDER --> trocar --}}
                        <div class="row">
                            <div class="col">
                                <x-painel.agendamento-cursos.list-cursos />
                            </div>
                        </div>
                        {{-- PLACEHOLDER --> trocar --}}

                        {{-- lista --}}





                        {{-- conteudo --}}


                    </div>
                </div>

                <div class="tab-pane" id="NF" role="tabpanel"> <!-- notas fiscais -->

                    <div class="row gy-3">

                        {{-- conteudo --}}



                        <div class="col-sm-6">
                            <x-forms.input-field :value="old('resp_info') ?? ($agendamento_curso->resp_info ?? null)" name="resp_info"
                                label="Responsável pelas Informações" placeholder="" />
                            @error('resp_info')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-3">
                            <x-forms.input-field :value="old('inserido_em') ?? ($agendamento_curso->inserido_em ?? null)" type="date" name="inserido_em"
                                label="Inserido em" />
                            @error('inserido_em')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-3">
                            <x-forms.input-field :value="old('alterado_em') ?? ($agendamento_curso->alterado_em ?? null)" type="date" name="alterado_em"
                                label="Alterado em" />
                            @error('alterado_em')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-12">
                            <x-forms.input-field :value="old('razao_social') ?? ($agendamento_curso->razao_social ?? null)" name="razao_social" label="Razão Social"
                                placeholder="" />
                            @error('razao_social')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-12">
                            <x-forms.input-field :value="old('email') ?? ($agendamento_curso->email ?? null)" name="email" label="Email" placeholder="" />
                            @error('email')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-4">
                            <x-forms.input-field :value="old('cnpj_cpf') ?? ($agendamento_curso->cnpj_cpf ?? null)" name="cnpj_cpf" label="cnpj_cpf"
                                placeholder="" />
                            @error('CNPJ/CPF')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-4">
                            <x-forms.input-field :value="old('ie') ?? ($agendamento_curso->ie ?? null)" name="ie" label="IE" placeholder="" />
                            @error('ie')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-4">
                            <x-forms.input-field :value="old('im') ?? ($agendamento_curso->im ?? null)" name="im" label="Insc. Municipal"
                                placeholder="" />
                            @error('im')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-12">
                            <x-forms.input-field :value="old('endereco') ?? ($agendamento_curso->endereco ?? null)" name="endereco" label="Endereço"
                                placeholder="" />
                            @error('endereco')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-4">
                            <x-forms.input-field :value="old('Bairro') ?? ($agendamento_curso->Bairro ?? null)" name="Bairro" label="Bairro" placeholder="" />
                            @error('Bairro')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-4">
                            <x-forms.input-field :value="old('Cidade') ?? ($agendamento_curso->Cidade ?? null)" name="Cidade" label="Cidade" placeholder="" />
                            @error('Cidade')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-1">
                            <x-forms.input-field :value="old('UF') ?? ($agendamento_curso->UF ?? null)" name="UF" label="UF" placeholder="" />
                            @error('UF')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-3">
                            <x-forms.input-field :value="old('CEP') ?? ($agendamento_curso->CEP ?? null)" name="CEP" label="CEP" placeholder="" />
                            @error('CEP')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-4">
                            <x-forms.input-field :value="old('fone') ?? ($agendamento_curso->fone ?? null)" name="fone" label="fone" placeholder="" />
                            @error('fone')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-4">
                            <x-forms.input-select name="enviado_financ" label="Enviado Financeiro">

                                <option value="SIM">SIM</option>
                                <option value="NAO">NÃO</option>


                            </x-forms.input-select>
                            @error('enviado_financ')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-4">
                            <x-forms.input-field :value="old('data_doc') ?? ($agendamento_curso->data_doc ?? null)" type="date" name="data_doc"
                                label="Data Documento" />
                            @error('data_doc')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-4">
                            <x-forms.input-field :value="old('valor_NF') ?? ($agendamento_curso->valor_NF ?? null)" name="valor_NF" label="Valor da Nota"
                                placeholder="" />
                            @error('valor_NF')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-4">
                            <x-forms.input-field :value="old('num_NF') ?? ($agendamento_curso->num_NF ?? null)" name="num_NF" label="Num NF" placeholder="" />
                            @error('num_NF')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-4">
                            <x-forms.input-field :value="old('data_pagamento') ?? ($agendamento_curso->data_pagamento ?? null)" type="date" name="data_pagamento"
                                label="Data de Pagamento" />
                            @error('data_pagamento')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-4">
                            <x-forms.input-select name="modalidade" label="Modalidade">

                                <option value="Modalidade 1">Modalidade 1</option>
                                <option value="Modalidade 2">Modalidade 2</option>


                            </x-forms.input-select>
                            @error('modalidade')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-12">
                            <x-forms.input-textarea name="obs" label="Observações">
                                {{ old('obs') ?? ($agendamento_curso->obs ?? null) }}
                            </x-forms.input-textarea>
                            @error('obs')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- lista --}}
                        {{-- PLACEHOLDER --> trocar --}}
                        <div class="row">
                            <div class="col">
                                <x-painel.agendamento-cursos.list-cursos />
                            </div>
                        </div>
                        {{-- PLACEHOLDER --> trocar --}}

                        {{-- lista --}}


                        {{-- conteudo --}}

                    </div>
                </div>



                <div class="tab-pane" id="despesas" role="tabpanel"> <!-- despesas -->

                    <div class="row gy-3">

                        <div class="col-sm-12">
                            <x-forms.input-select name="curso" label="despesas">

                                <option value="Curso 1">Curso 1</option>
                                <option value="Curso 2">Curso 2</option>
                                <option value="Curso 3">Curso 3</option>

                            </x-forms.input-select>
                            @error('curso')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>


                    </div>
                </div>

                <div class="tab-pane" id="anexos" role="tabpanel"> <!-- anexos -->

                    <div class="row gy-3">

                        <div class="col-sm-12">
                            <x-forms.input-select name="curso" label="anexos">

                                <option value="Curso 1">Curso 1</option>
                                <option value="Curso 2">Curso 2</option>
                                <option value="Curso 3">Curso 3</option>

                            </x-forms.input-select>
                            @error('curso')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>


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
